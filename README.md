# Web Girl Studio PHP edition

This folder is the classic-hosting version of the Web Girl Studio landing page.
It uses PHP 8+, semantic HTML, standalone CSS and vanilla JavaScript.

## Deploy

1. Upload the contents of this folder to the public directory of a PHP 8+ host.
2. Contact-form enquiries are delivered to `hello@webgirl.studio`.
3. Make `storage/` writable by PHP for server-side rate-limit state.
4. Keep `storage/.htaccess` in place on Apache. For Nginx, deny web access to
   the `/storage` path in the server configuration.
5. Submit a test enquiry after deployment and verify email delivery.

The form includes server-side schema validation, CSRF and completion-time checks,
a honeypot, Cloudflare Turnstile, file-backed rate limiting and aligned-domain
mail delivery. Set `WGS_TURNSTILE_SITE_KEY` and `WGS_TURNSTILE_SECRET_KEY` in the
production environment. Set `WGS_FORM_FROM_EMAIL` to the authenticated
`@webgirl.studio` sender configured in Hostinger (the default is
`website@webgirl.studio`). The form fails closed if Turnstile is not configured.

The notification uses `Liana | Web Girl Studio` as its display name, delivers
only to `hello@webgirl.studio`, and places the validated visitor address in
`Reply-To`. Attribution is retained in session storage only and included in the
notification. Never place mailbox credentials in this repository.

## Portfolio routes

- `/work/` - proof-led index featuring the live Fortepiano Academy project
- `/culture/` - Creative Industries niche landing page (route retained for compatibility)
- `/projects/fortepiano-academy/` - live founder-built client case study
- `/projects/mira-silt/` - retained but intentionally unpublished
- `/projects/ninth-form/` - retained but intentionally unpublished
- `/projects/second-weather/`
- `/projects/sasha-mirev/`
- `/projects/quiet-signal/`
- `/projects/mira-silt/site/` - retained but intentionally unpublished
- `/projects/ninth-form/site/` - retained but intentionally unpublished

Portfolio copy, metadata, placeholder testimonials and the non-public Face Not
Fake case-study pipeline live in `content/projects.php`. Only Fortepiano Academy
is listed publicly. Concept routes remain intact for compatibility but return a
not-found response and are deliberately absent from the public portfolio.

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
