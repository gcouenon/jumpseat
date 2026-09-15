# jumpseat

`jumpseat` is a PHP/JavaScript web app for pilots to share flights.

## Stack
- PHP 8+ (no external dependency)
- SQLite (auto-created in `data/jumpseat.sqlite`)
- Vanilla HTML/CSS/JavaScript

## Features implemented
- Registration (email required, photo optional, phone optional, opt-in notifications enabled by default)
- Login/logout and password reset flow
- Profile update and deregistration
- Admin user management (list/block/unblock/delete/reset password)
- Flight CRUD with all required fields
- Shared weekly calendar with previous/next week navigation
- Multi-day flight event display
- Hover modal with flight details
- Clickable event (if seats > 0) to notify flight owner with free-text message
- Disabled/grayed event (if seats = 0)
- New-flight email notifications to opt-in users
- EN/FR localization structure for easy language expansion
- Adaptive modern "flight/sky" style

## Run on WAMP
1. Place project under `c:\wamp64\www\git\jumpseat`.
2. Ensure PHP has SQLite extension enabled (`pdo_sqlite`).
3. Browse to `http://localhost/git/jumpseat/`.

## Default admin
- Email: `admin@jumpseat.local`
- Password: `Admin123!`

## Email
Emails are always logged into `data/mail.log`.  
The app also attempts PHP `mail()` when configured.

## Suggested frameworks (future evolution)
- Laravel: fast delivery and rich auth ecosystem
- Symfony: enterprise-level architecture and i18n
- Slim + Twig: lightweight but structured
