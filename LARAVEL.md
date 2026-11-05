# Laravel entry point setup

The original UI, SQL schema, uploaded files, and API implementation are retained. The Laravel 12 routing boundary serves the existing page and forwards its API contract through `/api`.

## Run locally with XAMPP

1. Keep MySQL running and preserve the existing `mnb_resort` database.
2. Install PHP 8.2+, Composer, and Apache `mod_rewrite`.
3. From this folder, run `composer install` and copy `.env.example` to `.env`.
4. Run `php artisan key:generate`.
5. Point Apache at this folder (the root rewrite forwards `/api` to Laravel's `public/index.php`) or point it at `public/`.
6. Open the existing `index.html` through Apache.

## Configure registration email

Registration and password verification codes are sent through the SMTP settings in `.env`. Set `MNB_SMTP_USER`, `MNB_SMTP_PASS`, and `MNB_MAIL_FROM` to a valid sending account and sender address. For Gmail, use a Google App Password (not the account password); the default SMTP host, port, and TLS settings are already provided in `.env.example`. Run `php artisan config:clear` and restart Apache after changing environment settings. If delivery still fails, check `storage/logs/laravel.log` for the SMTP error.

The current PDO database settings remain in `config.php`; the SQL files are unchanged. The API actions themselves still use the existing procedural implementation as a compatibility layer, so this is the Laravel MVC entry point migration, not a full rewrite of each data operation into Eloquent models and individual controllers.
PHP 8.2.12 (cli) (built: Oct 24 2023 21:15:15) (ZTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.2.12, Copyright (c) Zend Technologies
PS C:\Users\calin>