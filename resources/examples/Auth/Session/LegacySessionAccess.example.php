<?php

declare(strict_types=1);

namespace App\Handler\Auth;

final class LegacyLoginController
{
    public function __invoke(Request $request, SessionInterface $session): Response
    {
        $user = $this->users->findByEmail($request->input('email'));

        if ($user === null) {
            return $this->error('Invalid credentials.');
        }

        if (!$user->passwordMatches($request->input('password'))) {
            return $this->error('Invalid credentials.');
        }

        $session->set('current_user', $user->getId());
        $session->set('current_user_name', $user->getDisplayName());
        $session->set('auth_stage', 'authenticated');

        return $this->redirect('/account');
    }
}
