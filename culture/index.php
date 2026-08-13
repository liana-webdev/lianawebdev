<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';

$problems = [
    ['Rented attention', 'An audience can grow on social and streaming platforms without creating a durable home, a direct relationship or a coherent body of work.'],
    ['Identity flattened by templates', 'The work has a point of view, but the site reduces it to the same grid, store, reel or link page as everyone else.'],
    ['Professional routes hidden', 'Fans may find the music while press cannot find credits; customers may love the campaign while buyers cannot find wholesale; curators may see an artwork while the archive remains unusable.'],
    ['Campaigns that disappear', 'A release, collection, screening, exhibition or issue gets a moment of attention but leaves no structured archive for the next opportunity.'],
    ['Complexity without structure', 'Credits, metadata, rights, products, editions, authors, formats, access levels and enquiries need systems - not more decorative pages.'],
];
$offers = ['Artist and creator websites', 'Release and campaign microsites', 'Digital archives and portfolios', 'Fashion and editorial commerce', 'EPK, press and partnership systems', 'Visual identity and art direction for web', 'Content architecture, technical SEO and analytics'];
$method = ['Diagnose', 'Structure', 'Direct', 'Design', 'Build', 'Test'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Websites for Culture and Creators | Web Girl Studio</title>
    <meta name="description" content="Authored digital worlds and practical systems for musicians, artists, fashion labels, filmmakers, publications and cultural organisations.">
    <meta name="theme-color" content="#080808">
    <link rel="icon" href="<?= portfolio_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="canonical" href="https://webgirl.studio/culture/">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/styles.css') ?>">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/portfolio.css') ?>">
    <script defer src="<?= portfolio_asset('assets/app.js') ?>"></script>
    <script defer src="<?= portfolio_asset('assets/portfolio.js') ?>"></script>
</head>
<body class="portfolio-body" id="top">
<?php portfolio_header('culture'); ?>
<main id="main-content" class="portfolio-main">
    <section class="culture-hero section-dark">
        <div class="section-shell culture-hero__grid">
            <p class="section-index reveal">Culture / creators / authored systems</p>
            <h1 class="reveal">Your work already has a world. <em>Your website should let people enter it.</em></h1>
            <div class="culture-hero__aside reveal">
                <p>Web Girl Studio combines strategy, art direction, UX, design and development to build authored digital experiences that still make the practical path clear: listen, watch, buy, book, commission, cover, collaborate or return.</p>
                <div class="hero-actions"><a class="button button-red" href="#cultural-work">Explore cultural work</a><a class="button button-outline" href="/#contact">Start a project</a></div>
            </div>
        </div>
    </section>

    <section class="culture-proposition section-red">
        <div class="section-shell culture-proposition__grid">
            <p class="section-index">The proposition</p>
            <h2>Some clients need a world. Some need a system. <em>The strongest need both.</em></h2>
            <p>Atmosphere creates attention. Structure makes that attention useful, ownable and durable.</p>
        </div>
    </section>

    <section class="culture-work section-offwhite" id="cultural-work" aria-labelledby="culture-work-title">
        <div class="section-shell">
            <div class="culture-section-heading"><p class="section-index">Five cultural challenges</p><h2 id="culture-work-title">Different worlds.<br><em>Specific systems.</em></h2></div>
            <div class="project-grid project-grid--culture"><?php foreach ($projects as $project) project_card($project); ?></div>
        </div>
    </section>

    <section class="culture-problems section-dark">
        <div class="section-shell split-heading"><div><p class="section-index">Shared pressure points</p><h2>What culture loses<br><em>when the system is missing.</em></h2></div><p>The aesthetic changes. The underlying losses are remarkably consistent.</p></div>
        <div class="section-shell culture-problem-list">
            <?php foreach ($problems as $index => [$title, $body]): ?><article><span>0<?= $index + 1 ?></span><h3><?= e($title) ?></h3><p><?= e($body) ?></p></article><?php endforeach; ?>
        </div>
    </section>

    <section class="culture-offers section-light">
        <div class="section-shell culture-offers__grid">
            <div><p class="section-index">Relevant offers</p><h2>Built around the work.<br><em>Ready for the real world.</em></h2></div>
            <ol><?php foreach ($offers as $offer): ?><li><?= e($offer) ?></li><?php endforeach; ?></ol>
        </div>
    </section>

    <section class="culture-method section-red">
        <div class="section-shell"><p class="section-index">The WGS method</p><h2>One authorial line,<br><em>from diagnosis to launch.</em></h2><ol><?php foreach ($method as $index => $step): ?><li><span>0<?= $index + 1 ?></span><?= e($step) ?></li><?php endforeach; ?></ol></div>
    </section>

    <section class="culture-founder section-dark">
        <div class="section-shell culture-founder__grid">
            <div class="culture-founder__portrait"><img src="<?= portfolio_asset('img/wgs-liana-strategy-founder-portrait-hands-behind-back-looking-at-camera.jpg') ?>" alt="Liana, founder and creative director of Web Girl Studio" width="853" height="1280" loading="lazy"></div>
            <div><p class="section-index">Founder authorship</p><h2>One mind holds the thread.</h2><p>I’m Liana - strategist, creative director, designer and core developer. I move between the world and the system so the central idea does not get diluted between disciplines.</p><a class="button button-red" href="/#contact">Start a cultural project ↗</a></div>
        </div>
    </section>
</main>
<?php portfolio_footer(); ?>
</body>
</html>
