<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Core\Http\Client\FakeHttpClient;
use Semitexa\Core\Http\Client\HttpRequest;
use Semitexa\Core\Http\Client\HttpResponse;
use Semitexa\Core\Http\Client\HttpTransportKind;
use Semitexa\Core\Http\Exception\HttpTransportException;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;

/**
 * The demo's Google sign-in over the shared HTTP client (ep-outbound-http-client).
 * It had no test, and its fallback without curl never looked at the status.
 */
final class GoogleOAuthClientTest extends TestCase
{
    #[Test]
    public function the_code_is_exchanged_with_the_client_credentials_as_a_form(): void
    {
        $http = (new FakeHttpClient())->queue(HttpResponse::make(200, '{"access_token":"at-1","id_token":"x"}'));

        self::assertSame('at-1', self::client($http)->exchangeCodeForToken('code-9')['access_token']);

        $http->assertSent(static function (HttpRequest $r): bool {
            parse_str($r->body, $form);

            return $r->method === 'POST'
                && $r->url === 'https://oauth2.googleapis.com/token'
                && $r->header('Content-Type') === 'application/x-www-form-urlencoded'
                && $form === ['client_id' => 'cid', 'client_secret' => 'secret', 'code' => 'code-9', 'grant_type' => 'authorization_code', 'redirect_uri' => 'https://site.test/cb'];
        });
        self::assertSame(1, self::onlyOptions($http)->retry->maxAttempts, 'a single-use code is not resent');
    }

    #[Test]
    public function the_user_is_read_with_the_bearer_token(): void
    {
        $http = (new FakeHttpClient())->queue(HttpResponse::make(200, '{"email":"a@site.test"}'));

        self::assertSame('a@site.test', self::client($http)->fetchUserInfo('at-1')['email']);
        $http->assertSent(static fn (HttpRequest $r): bool => $r->method === 'GET' && $r->header('Authorization') === 'Bearer at-1');
    }

    #[Test]
    public function an_error_status_a_failed_call_and_bad_json_are_refused(): void
    {
        $cases = [
            'status' => [HttpResponse::make(400, '{"error":"invalid_grant"}'), 'HTTP 400'],
            // The transport's own words (Google's address, the curl error) went
            // into the page's query string: the page gets a plain sentence.
            'transport' => [new HttpTransportException(HttpTransportKind::Timeout, 'POST', 'https://oauth2.googleapis.com/token', 'timed out'), 'could not be reached'],
            'json' => [HttpResponse::make(200, '<html>'), 'invalid JSON'],
        ];
        foreach ($cases as $case => [$answer, $message]) {
            try {
                self::client((new FakeHttpClient())->queue($answer))->exchangeCodeForToken('c');
                self::fail("{$case} was accepted");
            } catch (\RuntimeException $e) {
                self::assertStringContainsString($message, $e->getMessage(), $case);
            }
        }
    }

    private static function client(FakeHttpClient $http): GoogleOAuthClient
    {
        $client = new GoogleOAuthClient();
        foreach (['http' => $http, 'clientId' => 'cid', 'clientSecret' => 'secret', 'redirectUri' => 'https://site.test/cb'] as $property => $value) {
            (new \ReflectionProperty($client, $property))->setValue($client, $value);
        }

        return $client;
    }

    private static function onlyOptions(FakeHttpClient $http): \Semitexa\Core\Http\Client\HttpCallOptions
    {
        return $http->recordedOptions()[0];
    }

    /** A personal account registered with a company address passed on its e-mail domain. */
    #[Test]
    public function only_the_hosted_domain_admits_an_account(): void
    {
        $client = self::client(new FakeHttpClient());
        (new \ReflectionProperty($client, 'allowedDomain'))->setValue($client, 'example.com');

        self::assertTrue($client->isAllowedDomain('example.com', 'ann@example.com'));
        self::assertFalse($client->isAllowedDomain(null, 'ann@example.com'), 'no hosted domain: not the organisation');
        self::assertFalse($client->isAllowedDomain('other.org', 'ann@example.com'));
        self::assertSame('/demo/rendering/deferred', $client->sanitizeReturnTo('/\\evil.com'));
        self::assertSame('/demo/rendering/deferred', $client->sanitizeReturnTo("/\t/evil.com"));
        self::assertSame('/demo/x?y=1', $client->sanitizeReturnTo('/demo/x?y=1'));
    }
}
