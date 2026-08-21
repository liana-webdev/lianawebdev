<?php
declare(strict_types=1);
session_start();
header('Cache-Control: no-cache, must-revalidate');

if (empty($_SESSION['wgs_csrf'])) {
    $_SESSION['wgs_csrf'] = bin2hex(random_bytes(24));
}

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
    ['01', 'Website systems', 'New builds and strategic redesigns that make the offer obvious and the next step natural.', ['Business diagnosis', 'Conversion architecture', 'Custom UI + code', 'Mobile, speed + launch']],
    ['02', 'Brand + interface identity', 'A visual language that makes the business recognisable, credible and consistent everywhere.', ['Positioning direction', 'Logo refinement', 'Colour + typography', 'Digital design system']],
    ['03', 'SEO foundations', 'The technical and on-page groundwork that helps the right people understand and find you.', ['Search structure', 'Metadata + headings', 'Local SEO alignment', 'Indexing foundations']],
    ['04', 'Digital growth layers', 'Connected touchpoints and AI-assisted systems that keep the website from becoming an island.', ['Social profile alignment', 'Landing pages', 'Analytics setup', 'AI workflow strategy']],
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
    ['Do you only make websites?', 'Websites are the core offer. Branding, SEO foundations, social profile alignment and AI-assisted workflows can be added when they solve a real part of the client journey.'],
    ['What if I already have branding?', 'Usable brand assets are preserved. I only recommend refinement when the existing system weakens clarity, trust or consistency on the web.'],
    ['Can you improve a site without rebuilding it?', 'Sometimes. The $400 Website Diagnosis identifies whether the problem is visual, structural, technical or in the offer itself. The recommendation may be optimisation, a landing page or a rebuild.'],
    ['How long does a project take?', 'A focused website generally takes 2–4 weeks once content and approvals move on schedule. Larger identity and authority systems are scoped individually.'],
    ['Will I be able to edit the site?', 'That depends on the build. Tilda is suitable when easy client editing and speed matter most. Custom code is used when the experience needs more control, behaviour or technical flexibility.'],
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
        <a href="#services">Services</a>
        <a href="#method">Method</a>
        <a href="/work/">Work</a>
        <a href="/culture/">Culture</a>
        <a href="#about">About</a>
    </nav>
    <a class="header-cta" href="#contact">Request diagnosis <span>↗</span></a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-label="Toggle navigation">
        <span></span><span></span>
    </button>
</header>

<main>
    <section class="hero section-dark pearl-sand-host" id="top" data-pearl-sand>
        <div class="hero-grid pearl-sand-content" id="main-content">
            <div class="hero-copy">
                <p class="eyebrow reveal">Founder-led strategy, design + development</p>
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

    <section class="problem-section section-light">
        <div class="section-shell">
            <div class="problem-heading reveal">
                <p class="section-index">01 / The problem</p>
                <h2>A beautiful website that says nothing is still <em>expensive silence.</em></h2>
            </div>
            <div class="problem-layout">
                <p class="problem-lead reveal">Most weak websites do not fail dramatically. They quietly lose a little trust, clarity and momentum at every screen.</p>
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
                <p class="section-index">02 / Fast diagnosis</p>
                <h2>Where are you now?</h2>
                <p>Choose the situation closest to yours. The answer changes the solution.</p>
            </div>
            <div class="diagnosis-grid">
                <div class="diagnosis-options" role="tablist" aria-label="Business website situation">
                    <button class="diagnosis-option" type="button" role="tab" aria-selected="false" data-diagnostic="none"><span>01</span>No website<i>↗</i></button>
                    <button class="diagnosis-option is-active" type="button" role="tab" aria-selected="true" data-diagnostic="weak"><span>02</span>Weak / outdated website<i>↗</i></button>
                    <button class="diagnosis-option" type="button" role="tab" aria-selected="false" data-diagnostic="quiet"><span>03</span>Good site, no enquiries<i>↗</i></button>
                </div>
                <div class="diagnosis-result reveal" role="tabpanel" aria-live="polite">
                    <span class="result-label">WGS diagnosis</span>
                    <h3 data-diagnostic-title>Find the leak before rebuilding.</h3>
                    <p data-diagnostic-body>An audit separates surface-level visual issues from deeper problems in messaging, trust, mobile experience and conversion flow.</p>
                    <div><span>Recommended route</span><strong data-diagnostic-route>$400 Website Diagnosis → targeted rebuild</strong></div>
                    <a href="#contact">Discuss this route <span>↗</span></a>
                </div>
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

    <section class="work-section section-offwhite" id="work">
        <div class="section-shell">
            <div class="section-heading reveal">
                <div><p class="section-index">05 / Selected work</p><h2>Selected work</h2></div>
                <p>Two complete concept projects, built to show how creative direction and practical website thinking work together.</p>
            </div>
            <div class="home-selected-grid">
                <?php foreach (['mira-silt', 'ninth-form'] as $selectedSlug): $selected = $projects[$selectedSlug]; ?>
                    <article class="home-featured-project reveal" style="<?= project_palette_style($selected) ?>">
                        <a class="home-featured-project__media" href="<?= e(wgs_project_url($selectedSlug)) ?>" aria-label="View <?= e($selected['name']) ?> case study"><?php project_art($selected, 'home-cover', true); ?></a>
                            <div class="home-featured-project__copy">
                                <p class="project-kicker"><?= e($selected['status']) ?> / <?= e($selected['industry']) ?></p>
                                <h3><a href="<?= e(wgs_project_url($selectedSlug)) ?>"><?= e($selected['name']) ?></a></h3>
                                <?php if ($selectedSlug === 'mira-silt'): ?>
                                    <p>A complete digital home for an independent musician.</p><span>An interactive album and artist website that brings music, live dates, physical editions and press information into one place.</span>
                                <?php else: ?>
                                    <p>A fashion store that keeps the point of view—and answers buying questions.</p><span>An independent fashion store that keeps the campaign feeling strong while making products, sizing, materials and wholesale information easy to understand.</span>
                                <?php endif; ?>
                                <ul class="project-tags"><?php foreach (array_slice($selected['capabilities'], 0, 4) as $capability): ?><li><?= e($capability) ?></li><?php endforeach; ?></ul>
                                <div class="home-featured-project__actions"><a href="<?= e(wgs_project_url($selectedSlug)) ?>">Read the case study ↗</a><a href="<?= e($selected['labRoute']) ?>"><?= e($selected['labLabel']) ?> →</a></div>
                            </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="case-study reveal" href="https://fortepianoacademy.au" target="_blank" rel="noreferrer" aria-label="Visit Fortepiano Academy website">
                <div class="case-meta"><span>Education / Founder-built brand</span><span>2026 ↗</span></div>
                <div class="case-visual">
                    <div class="case-browser">
                        <div class="browser-bar"><i></i><i></i><i></i><span>fortepianoacademy.au</span></div>
                        <div class="academy-screen">
                            <p>FORTEPIANO</p>
                            <div class="academy-title">Serious education.<br><em>Personal attention.</em></div>
                            <div class="academy-keys"><?php for ($i = 0; $i < 11; $i++): ?><i></i><?php endfor; ?></div>
                        </div>
                    </div>
                </div>
                <div class="case-description">
                    <h3>Fortepiano Academy</h3>
                    <p>A premium education brand shaped into a clear enrolment system—balancing artistic credibility, rigorous structure and a warm path for parents and students.</p>
                    <ul><li>Strategy</li><li>Identity</li><li>Copy</li><li>Custom build</li><li>SEO foundation</li></ul>
                </div>
            </a>
            <!-- Selected-work slot 04 is reserved for Marlow & Tide Dental. Copy and assets were not supplied, so no public card is fabricated. -->
            <div class="home-work-footer reveal"><p>Five independent cultural concepts, one founder-built education system, and more work as it becomes ready to prove something specific.</p><a class="button button-dark" href="/work/">Explore all work ↗</a></div>
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
                        <a class="contact-link" href="mailto:liana@webgirl.studio">
                            <span>Email</span><strong>liana@webgirl.studio</strong><i>↗</i>
                        </a>
                        <a class="contact-link" href="https://www.instagram.com/webgirlstudio/" target="_blank" rel="noopener noreferrer">
                            <span>Instagram</span><strong>@webgirlstudio</strong><i>↗</i>
                        </a>
                    </div>
                    <p>Sydney, Australia<br>Working Australia-wide</p>
                    <div class="contact-note"><i class="status-dot"></i>New project enquiries welcome</div>
                </div>
                <form class="enquiry-form reveal" action="contact.php" method="post">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['wgs_csrf']) ?>">
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
                    <button class="button button-red form-submit" type="submit">Send project enquiry <span>↗</span></button>
                    <p class="form-status" aria-live="polite"><?= e($formMessage) ?></p>
                    <?php if ($leadConfirmed): ?><span data-wgs-lead-success="<?= e($leadReceipt) ?>" hidden></span><?php endif; ?>
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
