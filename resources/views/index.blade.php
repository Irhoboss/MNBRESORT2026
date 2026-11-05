<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MNB Resort</title>
    <meta name="description" content="Find your next coastal getaway at MNB Resort in Papaya, Nasugbu, Batangas. Explore beachfront rooms, family stays, and cottages.">
    <script>try { document.documentElement.dataset.theme = localStorage.mnbTheme || 'light' } catch (e) { }</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div id="landing" class="landing-page">
        <section id="home" class="landing-hero">
            <header class="landing-nav">
                <a class="landing-brand" href="#home" aria-label="MNB Resort home">
                    <span class="landing-brand-mark">M</span>
                    <span><b>MNB</b> <span>RESORT</span></span>
                </a>
                <nav id="landing-nav-links" class="landing-nav-links" aria-label="Main navigation">
                    <a href="#home" class="is-current" aria-current="location">Home</a>
                    <a href="#stay">Stays</a>
                    <a href="#experiences">Explore</a>
                    <a href="#experience">About us</a>
                    <a href="#questions">FAQs</a>
                </nav>
                <button class="landing-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="landing-nav-links" aria-expanded="false" onclick="toggleLandingMenu(this)"><span></span><span></span></button>
                <div class="landing-nav-actions">
                    <button class="landing-login" type="button" onclick="openAuth(0)">Log in</button>
                    <button class="landing-nav-cta" type="button" onclick="openAuth(1)">Book your stay <span aria-hidden="true">↗</span></button>
                </div>
            </header>

            <div class="landing-hero-layout">
                <div class="landing-hero-content">
                    <span class="landing-eyebrow"><span></span> YOUR COASTAL ESCAPE · NASUGBU, BATANGAS</span>
                    <h1>NEVER STOP<br>EXPLORING<br><em>THE SHORE.</em></h1>
                    <p>Find your pace by the sea. From slow mornings to weekends with your favorite people, your next escape starts here.</p>
                    <button class="landing-primary" type="button" onclick="openAuth(1)">Explore your stay <span aria-hidden="true">→</span></button>
                    <div class="landing-location"><span aria-hidden="true">⌖</span> PAPAYA · NASUGBU · BATANGAS</div>
                </div>
                <div class="landing-hero-visual">
                    <button class="landing-hero-photo" type="button" aria-label="View the MNB Resort coastline" onclick="openLandingPhoto('https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=2000&q=90','A quiet day by the coast')"><span class="landing-hero-photo-label">PAPAYA, NASUGBU <b>↗</b></span></button>
                    <div class="landing-hero-float landing-hero-float-one"><span>⌁</span><div><b>Closer to the coast</b><small>Make room to slow down</small></div></div>
                    <div class="landing-hero-float landing-hero-float-two"><b>Find your pace.</b><span>Stay by the sea</span></div>
                </div>
            </div>
        </section>

        <section class="landing-pick-section landing-reveal" aria-label="Explore resort stays">
            <div class="landing-pick-heading"><div><span class="landing-section-kicker">FIND YOUR KIND OF STAY</span><h2>A place for <em>your people.</em></h2></div><div class="landing-carousel-controls"><span>SWIPE TO EXPLORE</span><div><button type="button" aria-label="Previous stays" onclick="scrollLandingCards(-1)">←</button><button type="button" aria-label="Next stays" onclick="scrollLandingCards(1)">→</button></div></div></div>
            <div id="landing-destinations" class="landing-destinations">
                <button class="landing-destination-card landing-destination-beach" type="button" onclick="openAuth(1)"><span class="landing-card-number">01 / BY THE WATER</span><span class="landing-card-name">BEACHFRONT<br>ROOMS</span><span class="landing-card-arrow" aria-hidden="true">↗</span></button>
                <button class="landing-destination-card landing-destination-suite" type="button" onclick="openAuth(1)"><span class="landing-card-number">02 / TOGETHER</span><span class="landing-card-name">FAMILY<br>SUITES</span><span class="landing-card-arrow" aria-hidden="true">↗</span></button>
                <button class="landing-destination-card landing-destination-garden" type="button" onclick="openAuth(1)"><span class="landing-card-number">03 / SLOW DOWN</span><span class="landing-card-name">GARDEN<br>COTTAGES</span><span class="landing-card-arrow" aria-hidden="true">↗</span></button>
                <button class="landing-destination-card landing-destination-picnic" type="button" onclick="openAuth(1)"><span class="landing-card-number">04 / OPEN AIR</span><span class="landing-card-name">PICNIC<br>COTTAGES</span><span class="landing-card-arrow" aria-hidden="true">↗</span></button>
            </div>
        </section>

        <div class="landing-trust-strip" aria-label="Resort highlights"><span>BEACH ACCESS</span><i></i><span>ROOMS & COTTAGES</span><i></i><span>SWIMMING</span><i></i><span>EVENT SPACE</span></div>

        <section id="stay" class="landing-stay-section">
            <div class="landing-section-heading">
                <div>
                    <span class="landing-section-kicker">STAY A LITTLE CLOSER TO THE GOOD LIFE</span>
                    <h2>Find your kind of <em>escape.</em></h2>
                </div>
                <p>Whether it is a quiet getaway or time together with everyone, there is a place for your next stay.</p>
            </div>
            <div class="landing-stay-grid">
                <article class="landing-stay-card">
                    <div class="landing-stay-art landing-stay-art-room"><span>01 / BY THE SHORE</span><button class="landing-photo-open" type="button" aria-label="View beachfront room photo" onclick="openLandingPhoto('https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1800&q=88','Beachfront rooms')">View photo <b aria-hidden="true">↗</b></button></div>
                    <div class="landing-stay-copy"><span>FOR SLOW MORNINGS</span><h3>Beachfront rooms</h3><p>Wake up near the water and leave a little more room for rest.</p></div>
                </article>
                <article class="landing-stay-card">
                    <div class="landing-stay-art landing-stay-art-suite"><span>02 / TOGETHER</span><button class="landing-photo-open" type="button" aria-label="View family suite photo" onclick="openLandingPhoto('https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1800&q=88','Family suite')">View photo <b aria-hidden="true">↗</b></button></div>
                    <div class="landing-stay-copy"><span>FOR YOUR FAVORITE PEOPLE</span><h3>Family suite</h3><p>Bring your people together for an easygoing change of scenery.</p></div>
                </article>
                <article class="landing-stay-card">
                    <div class="landing-stay-art landing-stay-art-cottage"><span>03 / OPEN AIR</span><button class="landing-photo-open" type="button" aria-label="View garden cottage photo" onclick="openLandingPhoto('https://images.unsplash.com/photo-1449158743715-0a90dc1c1b6e?auto=format&fit=crop&w=1800&q=88','Garden and picnic cottages')">View photo <b aria-hidden="true">↗</b></button></div>
                    <div class="landing-stay-copy"><span>FOR A CHANGE OF PACE</span><h3>Garden & picnic cottages</h3><p>Make space for fresh air, good company, and unhurried moments.</p></div>
                </article>
            </div>
        </section>

        <section id="experiences" class="landing-feature-section landing-reveal">
            <div class="landing-feature-heading"><span class="landing-section-kicker">A LITTLE SOMETHING FOR EVERY KIND OF DAY</span><h2>Make the most of<br><em>your time away.</em></h2></div>
            <div class="landing-feature-grid">
                <article class="landing-feature-card"><span>01</span><b>Room to unwind</b><p>Choose from guest rooms, family suites, and cottages.</p><i aria-hidden="true">⌂</i></article>
                <article class="landing-feature-card"><span>02</span><b>Time by the water</b><p>Enjoy beach access and swimming during your stay.</p><i aria-hidden="true">⌁</i></article>
                <article class="landing-feature-card"><span>03</span><b>Space to gather</b><p>Plan an easygoing visit with your favorite people.</p><i aria-hidden="true">✳</i></article>
                <article class="landing-feature-card"><span>04</span><b>Help when you need it</b><p>Keep your booking details and messages together.</p><i aria-hidden="true">↗</i></article>
            </div>
        </section>

        <section class="landing-story-band landing-reveal">
            <div><span class="landing-section-kicker">YOUR COASTAL RESET</span><h2>Slow mornings.<br><em>Longer moments.</em></h2><p>Trade the rush for fresh air, time together, and a little more room to breathe in Papaya, Nasugbu.</p><button class="landing-primary" type="button" onclick="openAuth(1)">Plan your getaway <span aria-hidden="true">→</span></button></div>
            <div class="landing-story-photo" role="img" aria-label="A tropical resort garden"></div>
        </section>

        <section id="experience" class="landing-experience">
            <div class="landing-experience-copy">
                <span class="landing-section-kicker">LESS PLANNING, MORE GETAWAY</span>
                <h2>Your next stay,<br><em>made simple.</em></h2>
                <p>Explore the available rooms, choose your dates, and keep your reservations together in one place.</p>
                <button class="landing-primary" type="button" onclick="openAuth(1)">Start planning <span aria-hidden="true">→</span></button>
            </div>
            <div class="landing-experience-points">
                <div><span>01</span><p><b>Explore your options</b><small>See resort stays and room details.</small></p><span aria-hidden="true">↗</span></div>
                <div><span>02</span><p><b>Choose your dates</b><small>Find a stay that fits your plans.</small></p><span aria-hidden="true">↗</span></div>
                <div><span>03</span><p><b>Keep it all together</b><small>Manage bookings from your account.</small></p><span aria-hidden="true">↗</span></div>
            </div>
        </section>

        <section class="landing-final-cta">
            <span class="landing-section-kicker">PAPAYA · NASUGBU · BATANGAS</span>
            <h2>A little time away<br>can go a long way.</h2>
            <button class="landing-primary" type="button" onclick="openAuth(1)">Find your stay <span aria-hidden="true">→</span></button>
        </section>

        <section class="landing-faq-section landing-reveal" id="questions">
            <div class="landing-faq-heading"><span class="landing-section-kicker">GOOD TO KNOW</span><h2>Questions before<br><em>you get away?</em></h2><p>Here are a few helpful details for planning your visit.</p></div>
            <div class="landing-faq-list">
                <details><summary>What kinds of stays can I book?<span>+</span></summary><p>Explore guest rooms, family suites, garden cottages, and picnic cottages. Availability appears after you choose dates.</p></details>
                <details><summary>How do I know if my booking is confirmed?<span>+</span></summary><p>Check My Bookings and Notifications in your account. Staff confirmation also sends a notification.</p></details>
                <details><summary>Where can I find my booking QR code?<span>+</span></summary><p>Open Receipt & QR from My Bookings after your reservation is confirmed.</p></details>
                <details><summary>Can I contact the resort about my stay?<span>+</span></summary><p>Log in and use Messages to coordinate details with resort staff.</p></details>
            </div>
        </section>
    </div>

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
            <button class="auth-back" type="button" onclick="closeAuth()">← Back to the resort</button>
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
