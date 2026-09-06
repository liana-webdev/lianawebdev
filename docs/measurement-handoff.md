# Measurement and delivery handoff (26 August 2026)

## Architecture and findings

The site is PHP 8, static CSS and vanilla JavaScript for classic hosting. Form
delivery previously used PHP `mail()` with a sender derived from the request
host. That did not prove an authenticated or aligned sender, while the honeypot
was the only bot-specific control. GA4 (`G-LPBCC632PW`) is loaded directly with
gtag; there is no consent manager, booking integration, CRM API, analytics Admin
API or provider SDK in this repository. A click on a recognised scheduler URL
was previously called `book_call`, even though it was not a confirmed booking.

`dbye_fzbvy` does not occur in repository source, including PHP, JavaScript and
configuration. There is no tag-manager container here. On repository evidence it
is therefore an externally supplied campaign/referral value or configuration
outside source; retain it until GA4 Acquisition reports expose its source,
medium, campaign and landing page.

## Deployment constraint notice

Repository code cannot prove inbox delivery, configure Hostinger, modify DNS or
change GA4 Admin. Before deploying, Liana or the hosting administrator must:

1. Create/authorise `website@webgirl.studio` in Hostinger (or set
   `WGS_FORM_FROM_EMAIL` to another authenticated WGS-domain sender) and ensure
   PHP `mail()` accepts that envelope sender. Set both Turnstile environment
   keys for a Managed-mode widget restricted to `webgirl.studio`.
2. Inspect the live DNS records in Hostinger and the exact Hostinger mail setup
   screen. Publish the SPF record Hostinger currently specifies, enable the DKIM
   record/value Hostinger generates, and add a DMARC policy appropriate to the
   domain after alignment monitoring. No record values are listed here because
   they cannot be derived safely from source.
3. Submit one clearly named `WGS QA test — do not action` enquiry after deploy,
   confirm it arrives in `hello@webgirl.studio` (not Junk), and inspect the
   message headers for SPF, DKIM and DMARC pass/alignment. This repository must
   not send that external message from a local test.
4. In GA4, open **Admin → Data display → Events**, find `generate_lead`, and
   toggle **Mark as key event**. Add office/developer IP filters under **Admin →
   Data collection and modification → Data filters** only after defining those
   addresses as internal traffic on the web data stream; test the filter before
   activating it.
5. In GA4 Acquisition, add `dbye_fzbvy` as a search/filter and inspect Session
   source/medium, campaign, landing page + query string, hostname and country.
   Do not exclude it without evidence.

There is no confirmed-booking callback or webhook to instrument. Scheduler link
clicks are now `booking_link_click`, not conversions. When a booking provider is
chosen, its signed server webhook or confirmed callback must create a unique
booking receipt before emitting a documented booked-call event.

## Proof-first status (checked 6 September 2026)

Current `index.php` already places `#work` immediately after the hero. The older
screenshots above are not current evidence of section order. Retain the existing
placement and art direction; deployment is still a separate verification step.

The research implementation names the existing AUD $400 entry offer Decision &
Conversion Diagnosis and defines one priority journey, evidence-led findings,
ranked improvements and a scope recommendation. The fee is credited toward a
WGS website project if the client proceeds. HTML option labels are updated while
their existing `Website Diagnosis` and `$400 audit` values are preserved for
server validation and analytics compatibility.

Fortepiano is explicitly disclosed as Liana's own founder-built business. Its
case demonstrates delivered work, not an unverified conversion uplift.

Hostinger Mail read access to `hello@webgirl.studio` was verified on 6 September.
That access does not prove authenticated PHP delivery or supply DNS/GA4 Admin
access. The deployment, Turnstile, inbox-delivery and GA4 checks above remain
open until evidence is recorded. Do not send external QA email from local tests.
