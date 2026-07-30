# Web Girl Studio PHP edition

This folder is the classic-hosting version of the Web Girl Studio landing page.
It uses PHP 8+, semantic HTML, standalone CSS, vanilla JavaScript and WebGL.

## Deploy

1. Upload the contents of this folder to the public directory of a PHP 8+ host.
2. Set the server environment variable `WGS_RECIPIENT_EMAIL` to the private
   inbox that should receive enquiries. If it is omitted, the supplied private
   contact inbox is used.
3. Make `storage/` writable by PHP. It is a fallback only when the host's
   `mail()` transport is unavailable.
4. Keep `storage/.htaccess` in place on Apache. For Nginx, deny web access to
   the `/storage` path in the server configuration.
5. Submit a test enquiry after deployment and verify email delivery.

The form includes server-side validation, a CSRF token, a honeypot, basic
session rate limiting, mail delivery and a locked JSONL fallback.
