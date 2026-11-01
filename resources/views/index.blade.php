<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MNB Resort</title>
    <script>try { document.documentElement.dataset.theme = localStorage.mnbTheme || 'light' } catch (e) { }</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div id="auth" class="center auth-shell hidden">
        <div class="authbox row g-0 container-fluid shadow-lg">
            <section class="auth-visual col-lg-7">
                <div class="auth-brand"><span class="auth-brand-mark">M</span> MNB <span>Resort</span></div>
                <div class="auth-story">
                    <span class="auth-eyebrow">PAPAYA · NASUGBU · BATANGAS</span>
                    <h1>Your tropical escape<br>starts here.</h1>
                    <p>Slow down, breathe in the sea air, and make room for a better kind of getaway.</p>
                    <small>Made for your next memorable stay</small>
                </div>
            </section>
            <section class="auth-content col-lg-5">
            <div class="auth-main">
                <div class="auth-heading"><span class="auth-kicker">WELCOME TO MNB RESORT</span><h2 id="authTitle">Welcome back</h2><p class="mut" id="authSubtitle">Log in to plan your next escape.</p></div>
                <div class="tabs"><a id="tl" class="on" onclick="tab(0)">Log in</a><a id="tr" onclick="tab(1)">Register</a>
                </div>
                <div id="authError" class="auth-error hidden" role="alert" aria-live="assertive"></div>
                <div id="fr" class="hidden"><input id="rn" autocomplete="name" placeholder="Full name" oninput="clearAuthError()"><input id="rph" autocomplete="tel" placeholder="Phone" oninput="clearAuthError()">
                </div>
                <input id="em" type="email" autocomplete="email" placeholder="Email" oninput="clearAuthError()">
                <div class="auth-password-control">
                    <input id="pw" type="password" autocomplete="current-password" placeholder="Password" oninput="clearAuthError()"
                        onkeydown="event.key=='Enter'&&authGo()">
                    <button id="pwToggle" class="auth-password-toggle" type="button" aria-label="Show password" aria-controls="pw" aria-pressed="false" onclick="togglePasswordVisibility()">
                        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <label class="registration-consent"><input id="guardianConsent" type="checkbox" onchange="clearAuthError()"><span>I am 18 or older, or a parent or legal guardian registering for a minor. I agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms</a> and acknowledge the <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span></label>
                <input id="authOtp" class="hidden" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit email verification code" onkeydown="event.key=='Enter'&&authGo()">
                <button type="button" class="btn" onclick="authGo()" id="ab">Log in</button>
                <button type="button" class="btn ghost" onclick="forgotPassword()" id="forgotBtn">Forgot password?</button>
            </div>

            <div id="otpPanel" class="otp-panel hidden" aria-live="polite">
                <div class="otp-brand">MNB <span>Resort</span></div>
                <h2>Check your email</h2>
                <p>We sent a 6-digit verification code to <strong id="otpEmailText">your@email.com</strong></p>
                <div class="otp-digits" aria-label="One-time password input">
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 1" />
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 2" />
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 3" />
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 4" />
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 5" />
                    <input class="otp-box" maxlength="1" inputmode="numeric" aria-label="Digit 6" />
                </div>
                <div class="otp-timer">Code expires in <span id="otpExpiresText">09:47</span></div>
                <div class="otp-actions">
                    <button type="button" class="btn otp-submit" onclick="authGo()">VERIFY ACCOUNT</button>
                    <button type="button" class="btn otp-resend-button" id="otpResendButton" disabled onclick="resendRegisterOtp()">RESEND CODE</button>
                </div>
                <a href="#" class="otp-back" onclick="event.preventDefault(); returnToAuthUi();">← Back to Create Account</a>
            </div>
            </section>
        </div>
    </div>
    <div id="app" class="wrap hidden">
        <header class="n top">
            <div class="logo">MNB<span>Resort</span></div>
            <div class="grow"></div><span id="who" class="chip"></span><button class="rb" id="nb" onclick="go('notifications')" title="Notifications"></button>
            <button class="rb" id="tb" onclick="theme()" title="Dark mode"></button><button class="rb" id="mb"
                onclick="go('msg')" title="Messages"></button><button class="rb" id="pb" onclick="go('profile')"
                title="Profile"></button><button class="rb" id="lb" onclick="logout()" title="Log out"></button>
        </header>
        <div class="shell">
            <aside id="side" class="side n"></aside>
            <main id="main" class="g"></main>
        </div>
    </div>
    <footer class="site-legal">
        <div class="site-legal-inner">
            <div class="site-legal-intro">
                <div class="site-legal-brand"><span class="site-legal-mark" aria-hidden="true">M</span><span>MNB <span class="site-legal-brand-light">Resort</span></span></div>
                <p class="site-legal-caption">A little clarity for a carefree stay.</p>
            </div>
            <nav class="site-legal-links" aria-label="Policies and privacy settings">
                <div class="site-legal-group">
                    <span class="site-legal-heading">GUEST POLICIES</span>
                    <a href="{{ route('privacy') }}">Privacy policy</a>
                    <a href="{{ route('terms') }}">Terms of service</a>
                </div>
                <div class="site-legal-group">
                    <span class="site-legal-heading">YOUR BOOKING</span>
                    <a href="{{ route('refund-policy') }}">Refund policy</a>
                </div>
                <div class="site-legal-group">
                    <span class="site-legal-heading">PRIVACY CHOICES</span>
                    <a href="{{ route('cookie-policy') }}">Cookie policy</a>
                    <button type="button" id="cookie-settings">Cookie settings</button>
                </div>
            </nav>
        </div>
        <div class="site-legal-bottom"><span>© {{ date('Y') }} MNB Resort. All rights reserved.</span><span>Made for your next memorable stay.</span></div>
    </footer>
    <section id="cookie-banner" class="cookie-banner" role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-description" hidden>
        <div class="cookie-mark" aria-hidden="true">✦</div>
        <p class="cookie-eyebrow">YOUR PRIVACY MATTERS</p>
        <h2 id="cookie-title">A little note about cookies</h2>
        <p id="cookie-description">We use essential session cookies to keep your account secure and the booking system working. We don’t use advertising or analytics cookies.</p>
        <a class="cookie-policy-link" href="{{ route('cookie-policy') }}">Read our cookie policy <span aria-hidden="true">→</span></a>
        <div class="cookie-actions"><button type="button" id="cookie-essential">Continue with essential cookies</button></div>
    </section>
    <script async src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script async src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script src="app.js"></script>
</body>

</html>
