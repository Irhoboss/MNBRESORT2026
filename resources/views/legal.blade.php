<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ ['privacy' => 'Privacy Policy', 'terms' => 'Terms of Service', 'refund' => 'Refund Policy', 'cookies' => 'Cookie Policy'][$page] }} · MNB Resort</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body class="legal-page">
<main class="legal-content">
    <a href="{{ url('/') }}">← Back to MNB Resort</a>
    @if ($page === 'privacy')
        <h1>Privacy Policy</h1><p>Last updated: November 1, 2026</p>
        <p>MNB Resort, Papaya, Nasugbu, Batangas, uses information needed to manage guest accounts, bookings, payments, customer support, and resort operations.</p>
        <h2>Information we use</h2><p>This may include your name, email, phone number, optional address and date of birth, booking dates and guest count, messages, review, and payment proof you choose to submit. Payment card details are not requested by this booking system.</p>
        <h2>How we use it</h2><p>We use this information to provide and administer your requested services, communicate about bookings and account security, process payment records, and meet legal obligations. Access is limited to resort staff and administrators who need it for their duties.</p>
        <h2>Service providers and retention</h2><p>Email delivery is handled by the configured SMTP provider. The site also loads Bootstrap and QR tools from third-party content delivery networks. Those providers may receive technical request data when your browser loads their resources. Booking and financial records may be retained for operational and legal recordkeeping.</p>
        <h2>Your choices</h2><p>You may update profile details in your account or request access, correction, or deletion by contacting the resort through the contact details shown in the resort settings. Deletion may be limited where records must be kept for bookings, disputes, or legal obligations. Do not provide information about children unless needed to make a booking; a parent or guardian should make bookings for minors.</p>
        <h2>Contact</h2><p>MNB Resort, Papaya, Nasugbu, Batangas. Use the current contact email or phone number shown in the resort information page.</p>
    @elseif ($page === 'terms')
        <h1>Terms of Service</h1><p>Last updated: November 1, 2026</p><p>By using this site, you agree to provide accurate information and use your account only for lawful bookings and communications. Keep your password private and contact the resort if you suspect unauthorized access.</p><p>Submitting a reservation is a booking request. It is not confirmed until resort staff confirms it. Rates, accommodation details, availability, and payment status are shown in the booking flow; contact the resort to clarify any detail before paying. The resort may correct material errors and will contact affected guests.</p><p>Do not use the service to interfere with its operation, submit unlawful content, or access another person's account. The resort may suspend accounts for misuse, subject to applicable law. These terms do not limit rights that cannot legally be waived.</p>
    @elseif ($page === 'refund')
        <h1>Refund and Cancellation Policy</h1><p>Last updated: November 1, 2026</p><p>Unpaid reservations may be cancelled online while the booking is eligible for cancellation. For paid bookings, cancellations, changes, and refund eligibility are handled by the resort based on the booking details and applicable consumer law. Contact the resort using the contact details shown in resort information and include your booking number.</p><p>Refund requests are reviewed by resort staff. An approved refund is recorded in the system; the time for funds to reach you depends on the original payment provider or method. No cancellation fee or refund deadline is promised here unless it is stated in the terms provided for your specific booking.</p>
    @else
        <h1>Cookie Policy</h1><p>Last updated: November 1, 2026</p><p>The application uses a server session to keep you signed in and protect account actions. The session cookie is essential to the service. The site does not currently set advertising or analytics cookies. Your choice to dismiss this notice is stored in your browser's local storage; it is not sent to the resort.</p><p>Third-party Bootstrap and QR resources are loaded from content delivery networks and may receive technical request data. You can block third-party resources in your browser, though QR scanning or styling may be affected.</p>
    @endif
</main>
</body>
</html>
