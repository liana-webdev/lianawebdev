# Web Girl Studio PHP edition

This folder is the classic-hosting version of the Web Girl Studio landing page.
It uses PHP 8+, semantic HTML, standalone CSS and vanilla JavaScript.

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

## Portfolio routes

- `/work/` - filterable portfolio index with a route-aware desktop case-study viewer
- `/culture/` - culture and creators market page
- `/projects/mira-silt/`
- `/projects/ninth-form/`
- `/projects/second-weather/`
- `/projects/sasha-mirev/`
- `/projects/quiet-signal/`
- `/projects/mira-silt/site/` - complete interactive fictional artist website
- `/projects/ninth-form/site/` - complete interactive fictional fashion store

Portfolio copy and metadata live in `content/projects.php`. Project media slots
use ratio-stable art-direction placeholders until final source imagery and
interface captures are supplied. WGS Lab actions remain intentionally disabled.

Mira Silt and Ninth Form campaign/editorial assets are integrated under their
respective `img/portfolio/` directories. Both now have classic-PHP interactive
concept websites. Their controls remain honest demonstrations: no music service,
ticketing, payment, mailing-list or wholesale submission is connected.

Run the local smoke test after starting the PHP development server on port 8099:

```powershell
$env:WGS_NODE_MODULES = 'path-to-bundled-node_modules'
node scripts/portfolio-smoke.cjs
```

## Analytics

The production pages use one direct GA4 Google tag from `components/analytics.php`.
Analytics is automatically disabled on non-production hostnames. Custom events are
implemented in `assets/analytics-events.js` and contain no enquiry form values.

Run the analytics regression test against the same local PHP server with
`WGS_TEST_MODE=1` set on the server process:

```powershell
$env:WGS_NODE_MODULES = 'path-to-bundled-node_modules'
node scripts/analytics-smoke.cjs
```
