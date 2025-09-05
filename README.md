# Krasa Studio OK

PHP-based multilingual booking website for Krása štúdio "OK" (Slovak / Russian / Ukrainian).
Designed for simple shared-hosting deployment. Features booking form, localized UI, Telegram notifications and Google reCAPTCHA protection.

## Features
- Multilingual front-end (sk / ru / ua) via `translations.php`
- Booking form with fields: Service, Date, Time, Name, Phone, Message
- Date input limited from today to +30 days, client-side time-slot loading
- Server-side booking API and validations
- Telegram notifications localized to user's language
- Google reCAPTCHA verification
- Simple slot-blocking mechanism (file-based or DB)
- Minimal admin/DB setup via `setup.php`
- `.htaccess` tuned for security on shared hosting

## Tech stack
- PHP (7.4+ recommended)
- MySQL / MariaDB
- Vanilla JavaScript (client logic in `main.j
