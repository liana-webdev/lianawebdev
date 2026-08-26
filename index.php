<?php
declare(strict_types=1);
session_start();
header('Cache-Control: no-cache, must-revalidate');

if (empty($_SESSION['wgs_csrf'])) {
    $_SESSION['wgs_csrf'] = bin2hex(random_bytes(24));
}
$_SESSION['wgs_form_started'] = time();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset_url(string $path): string
{
    $cleanPath = ltrim($path, '/');
    $absolutePath = __DIR__ . '/' . $cleanPath;
    $version = is_file($absolutePath) ? substr((string) hash_file('sha256', $absolutePath), 0, 12) : '1';
    return e($cleanPath . '?v=' . $version);
}

function brand_mark(bool $light = false): void
{
    $class = $light ? 'brand-mark brand-mark-light' : 'brand-mark';
    echo '<span class="' . $class . '" aria-label="Web Girl Studio">';
    echo '<span class="brand-web">WEB GIRL</span>';
    echo '<span class="brand-star" aria-hidden="true">✦</span>';
    echo '<span class="brand-studio">studio</span></span>';
}

require __DIR__ . '/content/projects.php';
require __DIR__ . '/components/portfolio.php';
require __DIR__ . '/components/analytics.php';

$services = [
    ['01', 'New websites + rebuilds', 'Custom websites that make the offer clear and give people an obvious route to book or enquire.', ['Page planning + copy structure', 'Responsive design', 'Custom development', 'Launch setup']],
    ['02', 'Brand + interface identity', 'A recognisable visual system built to work across the website, not just in a logo file.', ['Positioning direction', 'Logo refinement', 'Colour + typography', 'Reusable design system']],
    ['03', 'SEO foundations', 'The practical groundwork that helps Google and the right people understand the business.', ['Search-friendly page structure', 'Metadata + headings', 'Local search setup', 'Indexing foundations']],
    ['04', 'Booking + measurement', 'The connections that turn a finished website into a working part of the business.', ['Booking or enquiry flow', 'Analytics setup', 'Domain connection', 'Professional email setup']],
];

$process = [
    ['01', 'Understand', 'Understand the business and audience.'],
    ['02', 'Plan', 'Plan the pages and user journey.'],
    ['03', 'Create', 'Create the visual direction.'],
    ['04', 'Build', 'Design and build the website.'],
    ['05', 'Test', 'Test, launch and refine.'],
];

$packages = [
    [
        'Foundation',
        '$2.5–4k',
        'Stop losing trust',
        'A focused 1–3 page website for a clear offer, professional presence and direct enquiry path.',
        ['Website audit + basic strategy', 'Light brand direction', 'Mobile optimisation', 'SEO essentials', 'Enquiry setup'],
        false,
    ],
    [
        'Growth System',
        '$5–8k',
        'Turn traffic into enquiries',
        'A complete website system for a service business ready to sharpen its position and grow.',
        ['Deep business diagnosis', '4–7 page website', 'Offer + messaging clarity', 'Conversion architecture', 'Analytics + funnel setup'],
        true,
    ],
    [
        'Authority System',
        '$9–12k+',
        'Lead the category',
        'A premium identity and digital system built to make an established business feel unmistakable.',
        ['Advanced brand strategy', 'Full visual identity', 'Multi-page website', 'Expanded SEO structure', 'Post-launch optimisation'],
        false,
    ],
];

$faqs = [
    ['Do you only make websites?', 'Websites are the core offer. Branding, SEO and connected business tools are added only when the project needs them.'],
    ['What if I already have branding?', 'I preserve usable brand assets and recommend refinement only when the existing system weakens the website.'],
    ['Can you improve a site without rebuilding it?', 'Sometimes. The $400 Website Diagnosis shows whether targeted fixes or a full rebuild make more sense.'],
    ['How long does a project take?', 'Most WGS builds take around 2 to 4 weeks. Larger or unusually complex projects may take longer.'],
    ['Will I be able to edit the site?', 'Yes, when editing is part of the agreed build. The right setup depends on how often you need to change content.'],
];

$formStatus = $_GET['status'] ?? '';
$leadReceipt = is_string($_GET['lead'] ?? null) ? $_GET['lead'] : '';
$sessionLeadReceipt = is_string($_SESSION['wgs_lead_receipt'] ?? null) ? $_SESSION['wgs_lead_receipt'] : '';
$leadConfirmed = in_array($formStatus, ['sent', 'saved'], true) &&
    $leadReceipt !== '' &&
    $sessionLeadReceipt !== '' &&
    hash_equals($sessionLeadReceipt, $leadReceipt);
if ($leadConfirmed) {
    unset($_SESSION['wgs_lead_receipt']);
}
$leadContext = $leadConfirmed && is_array($_SESSION['wgs_last_lead_context'] ?? null) ? $_SESSION['wgs_last_lead_context'] : [];
if ($leadConfirmed) unset($_SESSION['wgs_last_lead_context']);
$formMessage = match ($formStatus) {
    'sent' => 'Thank you. Your project enquiry has been received.',
    'saved' => 'Thank you. Your enquiry has been securely received for follow-up.',
    'invalid' => 'Please check the required fields and try again.',
    'error' => 'The form could not be sent. Please call 0482 176 777.',
    default => '',
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Girl Studio | Sydney Web Design &amp; Digital Growth</title>
    <meta name="description" content="Conversion-led websites, brand systems, SEO foundations and AI-assisted digital growth for service businesses and creative founders in Sydney and across Australia.">
    <meta name="theme-color" content="#080808">
    <link rel="icon" href="<?= asset_url('assets/favicon.svg') ?>" type="image/svg+xml">
    <meta property="og:title" content="Web Girl Studio | Websites with a pulse">
    <meta property="og:description" content="Sharp, memorable website systems built to move people from attention to enquiry.">
    <meta property="og:type" content="website">
    <?php wgs_analytics_head(); ?>
    <?php if ((string) getenv('WGS_TURNSTILE_SITE_KEY') !== ''): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script><?php endif; ?>
    <link rel="preload" as="image" href="<?= asset_url('img/wgs-liana-founder-red-signal-closeup-looking-right-sideview.jpg') ?>" fetchpriority="high">
    <link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/clarity-engine.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/portfolio.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/pearl-signal/pearl-sand.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/pearl-signal/pearl-glow.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/pearl-signal/pearl-integration.css') ?>">
    <script defer src="<?= asset_url('assets/app.js') ?>"></script>
    <script defer src="<?= asset_url('assets/portfolio.js') ?>"></script>
    <script type="module" src="<?= asset_url('assets/pearl-signal/pearl-signal-init.js') ?>"></script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ProfessionalService",
      "name": "Web Girl Studio",
      "telephone": "+61 482 176 777",
      "email": "hello@webgirl.studio",
      "description": "Sydney web design and digital growth studio creating conversion-led websites, brand systems and SEO foundations.",
      "areaServed": ["Sydney", "Australia"],
      "founder": {"@type": "Person", "name": "Liana Pavlicheva"},
      "knowsAbout": ["Web design", "Web development", "Brand identity", "SEO", "Conversion strategy"]
    }
    </script>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<div class="progress-line" aria-hidden="true"></div>

<header class="site-header">
    <a href="#top" class="header-brand"><?php brand_mark(true); ?></a>
    <nav class="site-nav" aria-label="Primary navigation">
        <a href="/work/">Work</a>
        <a href="#services">Services</a>
        <a href="#method">Method</a>
        <a href="#about">About</a>
    </nav>
    <a class="header-cta" href="#contact">Start a project <span>↗</span></a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-label="Toggle navigation">
        <span></span><span></span>
    </button>
</header>

<main>
    <section class="hero section-dark pearl-sand-host" id="top" data-pearl-sand>
        <div class="hero-grid pearl-sand-content" id="main-content">
            <div class="hero-copy">
                <p class="eyebrow reveal">Websites with a pulse / Founder-led strategy, design + development</p>
                <h1 class="hero-title hero-title--plain" aria-label="Websites that look like you—and make sense to the right people.">
                    <span class="hero-line reveal">Websites that</span>
                    <span class="hero-line reveal delay-1">look like <em class="pearl-signal-glow">you—</em></span>
                    <span class="hero-line reveal delay-2">and make sense</span>
                    <span class="hero-line reveal delay-3">to the right people.</span>
                </h1>
                <p class="hero-intro reveal delay-3">I design and build websites for artists, cultural brands and growing businesses. The work can be expressive, practical or both. What matters is that people understand who you are, trust the experience and know what to do next.</p>
                <div class="hero-actions reveal delay-4">
                    <a class="button button-red button-diffraction" href="#work">See selected work</a>
                    <a class="button button-outline" href="#contact">Tell me about your project</a>
                </div>
            </div>
            <?php require __DIR__ . '/components/hero-clarity-engine.php'; ?>
        </div>
    </section>

    <section class="home-proof-section section-offwhite" id="work" aria-labelledby="home-work-title">
        <div class="section-shell">
            <div class="section-heading reveal">
                <div><p class="section-index">01 / Selected work</p><h2 id="home-work-title">Proof before<br><em>the pitch.</em></h2></div>
                <p>Founder-built client work, shown through the live website and the thinking behind it.</p>
            </div>
            <div class="project-grid home-proof-grid">
                <?php project_card($projects['fortepiano-academy']); ?>
            </div>
            <div class="home-work-footer reveal"><p>One real business, one working website, and a clear account of the decisions behind it.</p><a class="button button-dark" href="/work/">View the case study →</a></div>
        </div>
    </section>

    <section class="problem-section section-light">
        <div class="section-shell">
            <div class="problem-heading reveal">
                <p class="section-index">02 / The problem</p>
                <h2>A beautiful website that says nothing is still <em>expensive silence.</em></h2>
            </div>
            <div class="problem-layout">
                <p class="problem-lead reveal">Someone hears about the business, searches for it and lands on the site. Within seconds they decide whether it feels credible, relevant and worth contacting. WGS improves that whole path.</p>
                <div class="leak-list">
                    <?php
                    $leaks = [
                        ['Unclear offer', 'People cannot quickly tell what you do or why it matters.'],
                        ['Weak structure', 'Important information exists, but not in the order people need it.'],
                        ['Low trust', 'The business is stronger than its digital presence makes it look.'],
                        ['No conversion flow', 'Attention arrives, wanders and leaves without a decisive next step.'],
                    ];
                    foreach ($leaks as $index => [$title, $body]):
                    ?>
                        <article class="leak-item reveal">
                            <span>0<?= $index + 1 ?></span><h3><?= e($title) ?></h3><p><?= e($body) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="diagnosis-section section-red" id="diagnosis">
        <div class="section-shell">
            <div class="diagnosis-header reveal">
                <p class="section-index">03 / Website Diagnosis</p>
                <h2>See what’s actually holding the site back.</h2>
                <p>A focused review for the buyer who knows something is wrong but does not yet know what to fix.</p>
            </div>
            <div class="diagnosis-grid">
                <div class="diagnosis-options" role="tablist" aria-label="Business website situation">
                    <button class="diagnosis-option" type="button" role="tab" aria-selected="false" data-diagnostic="none"><span>01</span>No website<i>↗</i></button>
                    <button class="diagnosis-option is-active" type="button" role="tab" aria-selected="true" data-diagnostic="weak"><span>02</span>Weak / outdated website<i>↗</i></button>
                    <button class="diagnosis-option" type="button" role="tab" aria-selected="false" data-diagnostic="quiet"><span>03</span>Good site, no enquiries<i>↗</i></button>
                </div>
                <div class="diagnosis-result reveal" role="tabpanel" aria-live="polite">
                    <span class="result-label">Your diagnosis deliverable</span>
                    <h3 data-diagnostic-title>Find the leak before rebuilding.</h3>
                    <p data-diagnostic-body>An audit separates surface-level visual issues from deeper problems in messaging, trust, mobile experience and conversion flow.</p>
                    <ul class="diagnosis-deliverables"><li>UX + hierarchy</li><li>Messaging</li><li>Trust</li><li>Conversion path</li><li>Priority fixes</li></ul>
                    <div><span>Fixed price</span><strong data-diagnostic-route>$400 AUD</strong></div>
                    <a href="#contact">Request Diagnosis <span>↗</span></a>
                </div>
            </div>
            <div class="buyer-paths reveal" aria-label="Choose your starting point">
                <article><span>Not sure what is wrong?</span><h3>Start with the Diagnosis.</h3><a href="#contact">Request Diagnosis →</a></article>
                <article><span>Already know you need a rebuild?</span><h3>Go straight to the project brief.</h3><a href="#contact">Start a Project →</a></article>
            </div>
        </div>
    </section>

    <section class="services-section section-dark" id="services">
        <div class="section-shell">
            <div class="section-heading section-heading-inverse reveal">
                <div><p class="section-index">03 / What I build</p><h2>One core system.<br><em>Every layer aligned.</em></h2></div>
                <p>A website performs better when strategy, identity, interface and acquisition are not designed as separate worlds.</p>
            </div>
            <div class="service-list">
                <?php foreach ($services as [$number, $title, $lead, $details]): ?>
                    <article class="service-row reveal">
                        <span class="service-number"><?= e($number) ?></span>
                        <h3><?= e($title) ?></h3>
                        <p><?= e($lead) ?></p>
                        <ul><?php foreach ($details as $detail): ?><li><?= e($detail) ?></li><?php endforeach; ?></ul>
                        <span class="service-arrow" aria-hidden="true">↗</span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="about-section section-dark" id="about">
        <div class="section-shell about-grid">
            <div class="about-portraits reveal">
                <figure class="about-portrait about-portrait-primary">
                    <img src="<?= asset_url('img/wgs-liana-founder-red-signal-closeup-looking-right-sideview.jpg') ?>" alt="Liana, founder and creative director of Web Girl Studio, looking to the right under red light" width="960" height="1280" loading="eager" decoding="async">
                    <figcaption><strong>Liana / Founder / Creative Director</strong><span>Web Girl Studio / Sydney</span></figcaption>
                </figure>
            </div>
            <div class="about-statement">
                <p class="section-index reveal">04 / Founder-led studio</p>
                <h2 class="reveal">Creative <em>instinct.</em><br>Strategic logic.<br>Built by the<br>same person.</h2>
                <div class="about-rule"></div>
                <strong class="about-founder-label reveal">Liana / Founder / Creative Director</strong>
                <p class="about-copy reveal">I move between strategy, art direction, UX, design and development, so the idea does not get diluted as it passes from one discipline to another. The work stays visually distinctive, commercially clear and technically real.</p>
                <ul class="about-disciplines reveal" aria-label="Liana's disciplines">
                    <li>Strategy</li><li>Art direction</li><li>UX</li><li>Design</li><li>Development</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="deliverables-section section-offwhite" id="deliverables">
        <div class="section-shell">
            <div class="section-heading reveal"><div><p class="section-index">05 / What you get</p><h2>A finished website.<br><em>Ready to work.</em></h2></div><p>The exact scope changes, but every item below is a real deliverable—not an abstract agency promise.</p></div>
            <div class="deliverables-grid reveal">
                <?php foreach (['Custom website', 'Responsive design', 'Domain setup', 'Professional business email', 'Service pages', 'Booking or enquiry flow', 'Analytics', 'SEO foundations', 'Launch setup'] as $index => $deliverable): ?>
                    <div><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= e($deliverable) ?></strong></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="method-section section-dark" id="method">
        <div class="section-shell method-grid">
            <div class="method-sticky">
                <p class="section-index reveal">06 / The method</p>
                <h2 class="reveal">Creative, but never confusing.</h2>
                <p class="reveal">The visual idea matters, but it is only one part of the job. I also plan the pages, organise the content, design the mobile experience and build the final website. You work with one person from the first idea to launch.</p>
                <a class="button button-outline reveal" href="#contact">Start with diagnosis <span>↗</span></a>
            </div>
            <div class="process-list">
                <?php foreach ($process as [$number, $title, $body]): ?>
                    <article class="process-step reveal"><span><?= e($number) ?></span><div><h3><?= e($title) ?></h3><p><?= e($body) ?></p></div></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="packages-section section-light" id="packages">
        <div class="section-shell">
            <div class="section-heading reveal">
                <div><p class="section-index">07 / Investment</p><h2>Choose the level.<br><em>Keep the logic.</em></h2></div>
                <p>Every scope begins with the same principle: fix the most important business problem before adding more deliverables.</p>
            </div>
            <div class="package-grid">
                <?php foreach ($packages as [$name, $price, $label, $description, $items, $featured]): ?>
                    <article class="package-card reveal<?= $featured ? ' is-featured' : '' ?>"<?= $featured ? ' data-iridescent-card' : '' ?>>
                        <?php if ($featured): ?><span class="popular-label">Most complete</span><?php endif; ?>
                        <div class="package-top"><p><?= e($label) ?></p><h3><?= e($name) ?></h3><strong>AUD <?= e($price) ?></strong><span>Project range</span></div>
                        <p class="package-description"><?= e($description) ?></p>
                        <ul><?php foreach ($items as $item): ?><li><span>✦</span><?= e($item) ?></li><?php endforeach; ?></ul>
                        <a href="#contact">Enquire about <?= e($name) ?> <span>↗</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="audit-strip reveal">
                <div><span>Not sure what needs fixing?</span><h3>Website Diagnosis</h3></div>
                <p>A focused review of message, trust, structure, mobile experience and conversion flow.</p>
                <strong>AUD $400</strong>
                <a href="#contact">Request audit <span>↗</span></a>
            </div>
        </div>
    </section>

    <section class="ai-pricing-note section-offwhite" id="ai-and-pricing" aria-labelledby="ai-pricing-title">
        <div class="section-shell ai-pricing-note__grid">
            <div class="ai-pricing-note__heading reveal">
                <p class="section-index">Studio note / AI + value</p>
                <h2 id="ai-pricing-title">Why WGS does not sell <em>$300 websites.</em></h2>
                <p>WGS uses AI because it is useful. That does not make the project worth less.</p>
            </div>
            <article class="ai-pricing-note__article reveal">
                <p>A professional website is not priced according to how many hours someone spends manually pushing pixels. Its value comes from the quality of the thinking, the decisions being made, the level of execution and the business problem being solved.</p>
                <details>
                    <summary>Read how WGS uses AI <span aria-hidden="true">+</span></summary>
                    <div class="ai-pricing-note__content">
                        <p>AI can accelerate research, generate alternatives, assist with production, test directions, help organise information, support development and reduce repetitive work.</p>
                        <p>A proper WGS project still involves:</p>
                        <ul>
                            <li>understanding the business</li>
                            <li>researching the market and audience</li>
                            <li>defining the offer and message</li>
                            <li>planning the information architecture</li>
                            <li>designing the user journey</li>
                            <li>developing creative directions</li>
                            <li>establishing typography, colour, imagery and visual language</li>
                            <li>designing responsive interfaces</li>
                            <li>considering motion and interaction</li>
                            <li>building and integrating the site</li>
                            <li>setting up forms, booking, analytics, SEO and other required systems</li>
                            <li>testing usability, responsiveness and performance</li>
                            <li>refining the final result</li>
                        </ul>
                        <p>AI can make parts of this process faster. It does not remove the need for judgment.</p>
                        <p>It does not decide what is appropriate for a specific business, what should be removed, what should be emphasised, how a brand should feel, what a user needs to understand first, or whether a creative idea is actually worth implementing. Those decisions remain the work.</p>
                        <p>WGS uses AI in the same way a modern studio uses design software, development frameworks, 3D tools, motion libraries, analytics platforms and automation. The tool changes the workflow. It does not replace the creative direction.</p>
                        <p>In practice, this means WGS can explore more ideas, test more possibilities and move from strategy to execution faster than a traditional process that relies on manual production at every stage.</p>
                        <p>That efficiency benefits the client. It allows more time to be spent on the parts of the project that actually require thought: direction, judgment, refinement and execution.</p>
                        <p>A $300 website is usually priced around speed, templates and minimal involvement. WGS is priced as a professional creative and strategic project.</p>
                        <p><strong>The difference is not whether AI was used. The difference is how much thinking, specificity and responsibility goes into the result.</strong></p>
                    </div>
                </details>
            </article>
        </div>
    </section>

    <section class="manifesto-section section-red">
        <div class="manifesto-orbit" aria-hidden="true"></div>
        <div class="section-shell">
            <p class="section-index reveal">The standard</p>
            <blockquote class="reveal">“I’d rather build the right thing than make the wrong thing <em>prettier.</em>”</blockquote>
            <div class="manifesto-foot reveal"><span>Clear enough to understand.</span><span>Distinct enough to remember.</span><span>Structured enough to perform.</span></div>
        </div>
    </section>

    <section class="faq-section section-offwhite">
        <div class="section-shell faq-grid">
            <div><p class="section-index reveal">08 / Questions</p><h2 class="reveal">Before we<br><em>begin.</em></h2></div>
            <div class="faq-list">
                <?php foreach ($faqs as $index => [$question, $answer]): ?>
                    <article class="faq-item reveal<?= $index === 0 ? ' is-open' : '' ?>">
                        <h3><button type="button" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"><span>0<?= $index + 1 ?></span><?= e($question) ?><i><?= $index === 0 ? '−' : '+' ?></i></button></h3>
                        <div class="faq-answer"><p><?= e($answer) ?></p></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="contact-section section-dark" id="contact">
        <div class="contact-star star-one" aria-hidden="true">✦</div>
        <div class="contact-star star-two" aria-hidden="true">✦</div>
        <div class="section-shell">
            <div class="contact-heading reveal">
                <p class="section-index">09 / Start</p>
                <h2>Let’s build the thing<br>people <em>remember.</em></h2>
                <p>Tell me where the business is now, what feels wrong and what the website should make possible.</p>
            </div>
            <div class="contact-layout">
                <div class="contact-direct reveal">
                    <span>Direct contact</span>
                    <div class="contact-links">
                        <a class="contact-link contact-link-phone" href="tel:+61482176777">
                            <span>Phone</span><strong>0482 176 777</strong><i>↗</i>
                        </a>
                        <a class="contact-link" href="mailto:hello@webgirl.studio">
                            <span>Email</span><strong>hello@webgirl.studio</strong><i>↗</i>
                        </a>
                        <a class="contact-link" href="https://www.instagram.com/webgirlstudio/" target="_blank" rel="noopener noreferrer">
                            <span>Instagram</span><strong>@webgirlstudio</strong><i>↗</i>
                        </a>
                    </div>
                    <p>Sydney, Australia<br>Working Australia-wide</p>
                    <div class="contact-note"><i class="status-dot"></i>New project enquiries welcome</div>
                </div>
                <form class="enquiry-form reveal" action="contact.php" method="post" aria-describedby="enquiry-status">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['wgs_csrf']) ?>">
                    <input type="hidden" name="form_started" value="<?= e((string) $_SESSION['wgs_form_started']) ?>">
                    <?php foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'landing_path', 'initial_referrer'] as $field): ?><input type="hidden" name="<?= e($field) ?>" data-attribution-field="<?= e($field) ?>" value=""><?php endforeach; ?>
                    <label class="honeypot" aria-hidden="true" tabindex="-1">
                        Leave this field empty
                        <input name="company_website" type="text" tabindex="-1" autocomplete="off">
                    </label>
                    <div class="form-row">
                        <label><span>Your name *</span><input name="name" type="text" autocomplete="name" maxlength="100" required placeholder="Name"></label>
                        <label><span>Email *</span><input name="email" type="email" autocomplete="email" maxlength="160" required placeholder="you@business.com"></label>
                    </div>
                    <label><span>Business / current website</span><input name="business" type="text" maxlength="220" placeholder="Business name or URL"></label>
                    <div class="form-row">
                        <label>
                            <span>What do you need? *</span>
                            <select name="project_type" required>
                                <option value="" disabled selected>Select a route</option>
                                <option>Website Diagnosis</option><option>New website</option><option>Website redesign</option>
                                <option>Brand + website</option><option>Landing page</option><option>Something more unusual</option>
                            </select>
                        </label>
                        <label>
                            <span>Investment range *</span>
                            <select name="budget" required>
                                <option value="" disabled selected>Select a range</option>
                                <option>$400 audit</option><option>$2.5–4k</option><option>$5–8k</option>
                                <option>$9–12k+</option><option>Not sure yet</option>
                            </select>
                        </label>
                    </div>
                    <label><span>What is not working now? *</span><textarea name="message" rows="5" minlength="20" maxlength="4000" required placeholder="The current problem, the outcome you want, and anything I should know..."></textarea></label>
                    <div class="cf-turnstile" data-sitekey="<?= e((string) (getenv('WGS_TURNSTILE_SITE_KEY') ?: '')) ?>" data-theme="dark"></div>
                    <button class="button button-red form-submit" type="submit">Send project enquiry <span>↗</span></button>
                    <p class="form-status" id="enquiry-status" role="status" aria-live="polite"><?= e($formMessage) ?></p>
                    <?php if ($leadConfirmed): ?><span data-wgs-lead-success="<?= e($leadReceipt) ?>" data-lead-context="<?= e((string) json_encode($leadContext)) ?>" hidden></span><?php endif; ?>
                    <p class="form-privacy">Your information is used only to respond to this project enquiry.</p>
                </form>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-top"><?php brand_mark(); ?><p>Creative direction, digital systems and websites that move.</p><a href="#top">Back to top ↑</a></div>
    <div class="footer-bottom"><span>© <?= date('Y') ?> Web Girl Studio</span><span>Sydney / Australia-wide</span><span>Designed + built with human direction</span></div>
</footer>
</body>
</html>
