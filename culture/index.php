<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';
require dirname(__DIR__) . '/components/analytics.php';

$problems = [
    ['Rented attention', 'An audience can grow on social and streaming platforms without creating a reliable place to understand the work or take the next step.'],
    ['Identity flattened by templates', 'The work has a point of view, but the site reduces it to the same grid, store, reel or link page as everyone else.'],
    ['Professional routes hidden', 'Fans may find the music while press cannot find credits; customers may love the campaign while buyers cannot find wholesale.'],
];
$offers = ['Artist and creator websites', 'Release and campaign sites', 'Digital archives and portfolios', 'Fashion and editorial commerce', 'EPK, press and partnership systems', 'Visual identity and art direction for web', 'Technical SEO and analytics foundations'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creative Industries Websites | Web Girl Studio</title>
    <meta name="description" content="Websites and digital systems for artists, labels, studios, publications, fashion, film and cultural businesses.">
    <meta name="theme-color" content="#080808">
    <link rel="icon" href="<?= portfolio_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="canonical" href="https://webgirl.studio/culture/">
    <?php wgs_analytics_head(); ?>
    <link rel="stylesheet" href="<?= portfolio_asset('assets/styles.css') ?>">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/portfolio.css') ?>">
    <script defer src="<?= portfolio_asset('assets/app.js') ?>"></script>
    <script defer src="<?= portfolio_asset('assets/portfolio.js') ?>"></script>
</head>
<body class="portfolio-body" id="top">
<?php portfolio_header(); ?>
<main id="main-content" class="portfolio-main">
    <section class="culture-hero section-dark">
        <div class="section-shell culture-hero__grid">
            <p class="section-index reveal">Web Girl Studio / Specialist sector</p>
            <h1 class="reveal">Creative Industries</h1>
            <div class="culture-hero__aside reveal">
                <p>Websites and digital systems for artists, labels, studios, publications, fashion, film and cultural businesses.</p>
                <div class="hero-actions"><a class="button button-red" href="/#contact">Start a creative project</a></div>
            </div>
        </div>
    </section>

    <section class="culture-proposition section-red">
        <div class="section-shell culture-proposition__grid">
            <p class="section-index">Who this is for</p>
            <h2>The work has a point of view. <em>The website still needs a clear job.</em></h2>
            <p>For creative businesses that need atmosphere without hiding the route to listen, buy, book, commission, cover or collaborate.</p>
        </div>
    </section>

    <section class="culture-problems section-dark">
        <div class="section-shell split-heading"><div><p class="section-index">Common problems</p><h2>Attention arrives.<br><em>Then the path disappears.</em></h2></div><p>A strong body of work can still be hard to understand, navigate or act on.</p></div>
        <div class="section-shell culture-problem-list">
            <?php foreach ($problems as $index => [$title, $body]): ?><article><span>0<?= $index + 1 ?></span><h3><?= e($title) ?></h3><p><?= e($body) ?></p></article><?php endforeach; ?>
        </div>
    </section>

    <section class="culture-offers section-light">
        <div class="section-shell culture-offers__grid">
            <div><p class="section-index">What WGS builds</p><h2>Distinctive interfaces.<br><em>Useful systems.</em></h2><p>Strategy, art direction and development stay connected from the first page decision to launch.</p></div>
            <ol><?php foreach ($offers as $offer): ?><li><?= e($offer) ?></li><?php endforeach; ?></ol>
        </div>
    </section>

    <section class="culture-work section-offwhite" aria-labelledby="culture-work-title">
        <div class="section-shell">
            <div class="culture-section-heading"><p class="section-index">Relevant proof</p><h2 id="culture-work-title">See the systems<br><em>working in the browser.</em></h2></div>
            <div class="project-grid"><?php project_card($projects['mira-silt']); ?><?php project_card($projects['ninth-form']); ?></div>
            <div class="culture-single-cta"><a class="button button-dark" href="/#contact">Start a creative project →</a></div>
        </div>
    </section>
</main>
<?php portfolio_footer(); ?>
</body>
</html>
