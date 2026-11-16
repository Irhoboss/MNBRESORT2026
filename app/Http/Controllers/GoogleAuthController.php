<?php

namespace App\Http\Controllers;

use App\Models\ResortData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class GoogleAuthController
{
    public function redirect(Request $request)
    {
        $config = config('mnb.google');
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return redirect('/')->with('google_error', 'Google sign-in is not configured yet. Add the Google OAuth client ID and secret to the server environment.');
        }

        $this->startPhpSession($request);
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $redirectUri = $config['redirect_uri'] ?: route('google.callback');
        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request)
    {
        $config = config('mnb.google');
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return redirect('/')->with('google_error', 'Google sign-in is not configured yet. Add the Google OAuth client ID and secret to the server environment.');
        }

        $this->startPhpSession($request);
        $state = (string) $request->query('state', '');
        $expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
        unset($_SESSION['google_oauth_state']);
        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            return redirect('/')->with('google_error', 'Google sign-in could not be verified. Please try again.');
        }

        if ($request->query('error') || !$request->query('code')) {
            return redirect('/')->with('google_error', 'Google sign-in was cancelled or could not be completed.');
        }

        $redirectUri = $config['redirect_uri'] ?: route('google.callback');
        try {
            $tokenResponse = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'code' => $request->query('code'),
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);
            if (!$tokenResponse->successful() || !$tokenResponse->json('id_token')) {
                return redirect('/')->with('google_error', 'Google could not complete sign-in. Please try again.');
            }

            $claimsResponse = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $tokenResponse->json('id_token'),
            ]);
        } catch (ConnectionException) {
            return redirect('/')->with('google_error', 'Google sign-in is temporarily unavailable. Please try again.');
        }

        if (!$claimsResponse->successful()) {
            return redirect('/')->with('google_error', 'Google could not verify your account. Please try again.');
        }

        $claims = $claimsResponse->json();
        $issuerValid = in_array($claims['iss'] ?? '', ['https://accounts.google.com', 'accounts.google.com'], true);
        $emailVerified = in_array($claims['email_verified'] ?? false, [true, 'true'], true);
        if (
            !$issuerValid
            || ($claims['aud'] ?? '') !== $config['client_id']
            || (int) ($claims['exp'] ?? 0) <= time()
            || !$emailVerified
            || empty($claims['sub'])
            || !filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL)
        ) {
            return redirect('/')->with('google_error', 'Google returned an account that could not be verified.');
        }

        $googleId = (string) $claims['sub'];
        $email = strtolower((string) $claims['email']);
        $user = ResortData::query('SELECT user_id, full_name, user_role, is_active FROM users WHERE google_sub=?', [$googleId])->fetch();
        if (!$user) {
            $user = ResortData::query('SELECT user_id, full_name, user_role, is_active, google_sub FROM users WHERE email=?', [$email])->fetch();
            if (!$user) {
                $googleName = trim((string) ($claims['name'] ?? ''));
                $nameParts = preg_split('/\s+/', $googleName, 2) ?: [];
                $firstName = mb_substr(trim((string) ($claims['given_name'] ?? $nameParts[0] ?? '')), 0, 100);
                $surname = mb_substr(trim((string) ($claims['family_name'] ?? $nameParts[1] ?? '')), 0, 100);
                if ($firstName === '') {
                    $firstName = mb_substr(strstr($email, '@', true) ?: 'Guest', 0, 100);
                }
                $fullName = mb_substr(trim($firstName.' '.$surname), 0, 100);
                $usernameBase = strtolower((string) (strstr($email, '@', true) ?: 'guest'));
                $usernameBase = trim((string) preg_replace('/[^a-z0-9._-]+/', '.', $usernameBase), '._-');
                $username = mb_substr($usernameBase !== '' ? $usernameBase : 'guest', 0, 22).'.'.bin2hex(random_bytes(4));
                ResortData::query(
                    'INSERT INTO users(username, google_sub, first_name, surname, full_name, email, user_role, password_hash) VALUES(?,?,?,?,?,?,?,?)',
                    [$username, $googleId, $firstName, $surname, $fullName, $email, 'Guest', password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)]
                );
                $userId = ResortData::connection()->lastInsertId();
                $user = ResortData::query('SELECT user_id, full_name, user_role, is_active FROM users WHERE user_id=?', [$userId])->fetch();
            } else {
                if (!empty($user['google_sub']) && !hash_equals((string) $user['google_sub'], $googleId)) {
                    return redirect('/')->with('google_error', 'This MNB Resort account is linked to a different Google account.');
                }
                if (empty($user['google_sub'])) {
                    ResortData::query('UPDATE users SET google_sub=? WHERE user_id=? AND google_sub IS NULL', [$googleId, $user['user_id']]);
                    $linkedGoogleId = ResortData::query('SELECT google_sub FROM users WHERE user_id=?', [$user['user_id']])->fetchColumn();
                    if (!is_string($linkedGoogleId) || !hash_equals($linkedGoogleId, $googleId)) {
                        return redirect('/')->with('google_error', 'This MNB Resort account is linked to a different Google account.');
                    }
                }
            }
        }

        if (empty($user['is_active'])) {
            return redirect('/')->with('google_error', 'This account is inactive. Contact the resort administrator.');
        }

        session_regenerate_id(true);
        $_SESSION['u'] = [
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name'],
            'user_role' => $user['user_role'],
        ];
        session()->flash('google_signin', [
            'full_name' => $user['full_name'],
            'email' => $email,
        ]);

        return redirect('/');
    }

    private function startPhpSession(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => $request->isSecure(),
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
}
