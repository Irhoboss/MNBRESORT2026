# Laravel entry point setup

The original UI, SQL schema, resort images, and API implementation are retained. Laravel 12 serves the application page and existing API routes.

## Upload and deploy on Apache

1. Upload and extract the deployment archive. Point the domain's document root to the extracted `public` directory. If the host only allows the project folder as its document root, the root `.htaccess` redirects requests into `public/` instead.
2. Use PHP 8.2+ with `pdo_mysql` and Apache `mod_rewrite`. Make the `storage/`, `bootstrap/cache/`, and `public/uploads/` directories writable by PHP.
3. Copy `.env.example` to `.env`. Set the production `APP_URL`, database credentials, SMTP credentials, and a unique `APP_KEY`; never upload a local `.env` file.
4. If Composer is available on the host, run `composer install --no-dev --optimize-autoloader`. Otherwise, use the included `vendor/` directory from the archive.
5. On hosts with terminal access, run `php artisan key:generate` only when `APP_KEY` is empty, then `php artisan config:clear` and `php artisan config:cache`. For hosts without a terminal, generate the key locally with `php artisan key:generate --show` and enter it into the server `.env`.
6. Import `database.sql` for a new database. For an existing database, apply the needed SQL updates (`management_update.sql`, `profile_update.sql`, `room_photo_update.sql`, and `account_identity_update.sql`) only if those schema changes have not already been applied.
7. Export/import database content separately when migrating an existing resort. Copy its existing `public/uploads/` files separately if its user records refer to profile photos.

## InfinityFree

InfinityFree fixes the web root at `htdocs` and does not provide shell access. Upload and extract the archive contents into `htdocs`; its root `.htaccess` redirects requests into `public/`. Select PHP 8.2 or later with PDO MySQL enabled, create the database in the InfinityFree control panel, and use that panel's database hostname and credentials in `.env` (the hostname is not necessarily `localhost`). Import SQL using phpMyAdmin after selecting the new database. InfinityFree-generated database names are prefixed, so remove or update the `CREATE DATABASE` and `USE mnb_resort` lines in `database.sql`; update the `USE` line in any migration SQL you apply too. The archive already includes Composer dependencies, so do not run Composer or Artisan on the host. This build's PHP files are below InfinityFree's documented 1 MB per-file upload limit.

The site uses HTTP polling for messages and dashboard refreshes, not WebSockets. InfinityFree's own [Laravel hosting guide](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578) notes free-hosting limits on CPU, file counts, shell access, and background services. Use it for a demo or low-traffic test; choose managed hosting with backups and stronger resource guarantees before storing real guest or payment-related data.

## Configure Google sign-in

Create a Google OAuth client of type **Web application** in Google Cloud Console. Add the site's exact callback URL as an authorized redirect URI. When uploading this project into InfinityFree `htdocs` and using the root `.htaccess`, the callback is normally `https://your-site-host/public/auth/google/callback`; if the domain's document root already points to `public`, omit `/public`. For local XAMPP, use `http://localhost/mnb-resort/public/auth/google/callback`. Set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI` to the same production values in the server `.env`—never use `localhost` there—then clear any cached Laravel config and restart Apache. Existing MNB accounts can link on their first Google sign-in when the verified Google email matches the account email; Google sign-in does not create an account. Apply `account_identity_update.sql` before deployment so the username and Google account-link columns exist.

## Configure registration email

Registration and password verification codes are sent through the SMTP settings in `.env`. Set `MNB_SMTP_USER`, `MNB_SMTP_PASS`, and `MNB_MAIL_FROM` to a valid sending account and sender address. For Gmail, use a Google App Password (not the account password); the default SMTP host, port, and TLS settings are already provided in `.env.example`. Run `php artisan config:clear` and restart Apache after changing environment settings. If delivery still fails, check `storage/logs/laravel.log` for the SMTP error.

The deployment archive intentionally excludes `.env`, Git metadata, local logs/caches/sessions, and profile uploads. Keep the `.env.example` template and resort images. The PDO database settings are read from `.env` through `config/mnb.php`. The API actions still use the existing procedural implementation as a compatibility layer, so this is the Laravel MVC entry point migration, not a full rewrite of each data operation into Eloquent models and individual controllers.

Operational views and reports refresh every four seconds, while messages sync every two seconds and fetch only newly added messages. Polling pauses when the browser tab is hidden. The administrator activity log records guest bookings/reviews/payment proofs and staff or administrator management actions.
PHP 8.2.12 (cli) (built: Oct 24 2023 21:15:15) (ZTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.2.12, Copyright (c) Zend Technologies
PS C:\Users\calin>