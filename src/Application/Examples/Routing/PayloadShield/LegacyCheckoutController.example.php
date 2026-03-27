<?php

declare(strict_types=1);

final class LegacyCheckoutController
{
    public function create(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $coupon = trim((string) $request->input('coupon', ''));
        $agreeToTerms = (bool) $request->input('agree_to_terms', false);

        if ($email === '') {
            return Response::json(['errors' => ['email' => ['Email is required.']]], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::json(['errors' => ['email' => ['Email is invalid.']]], 422);
        }

        if ($agreeToTerms !== true) {
            return Response::json(['errors' => ['agree_to_terms' => ['Terms must be accepted.']]], 422);
        }

        return $this->checkoutService->create(
            email: $email,
            coupon: $coupon !== '' ? strtoupper($coupon) : null,
            agreeToTerms: $agreeToTerms,
        );
    }
}
