<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';
require dirname(__DIR__) . '/components/analytics.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selected Work | Web Girl Studio</title>
    <meta name="description" content="Fortepiano Academy: founder-built client work by Web Girl Studio, from strategy and identity through design and development.">
    <meta name="theme-color" content="#080808">
    <link rel="icon" href="<?= portfolio_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="canonical" href="https://webgirl.studio/work/">
    <?php wgs_analytics_head(); ?>
    <link rel="stylesheet" href="<?= portfolio_asset('assets/styles.css') ?>">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/portfolio.css') ?>">
    <script defer src="<?= portfolio_asset('assets/app.js') ?>"></script>
    <script defer src="<?= portfolio_asset('assets/portfolio.js') ?>"></script>
</head>
<body class="portfolio-body" id="top">
<?php portfolio_header('work'); ?>
<main id="main-content" class="portfolio-main">
    <section class="portfolio-hero section-dark">
        <div class="section-shell portfolio-hero__grid">
            <p class="section-index reveal">Work / Proof in the browser</p>
            <h1 class="reveal">Selected Work</h1>
            <p class="portfolio-hero__intro reveal">A live founder-built project showing how strategy, identity, interface and development work together.</p>
        </div>
    </section>

    <section class="work-index section-offwhite" aria-labelledby="work-index-title">
        <div class="section-shell">
            <div class="work-index__heading">
                <div><p class="section-index">Client work</p><h2 id="work-index-title">Real businesses.<br><em>Working websites.</em></h2></div>
                <p>Commissioned and founder-built work for operating businesses.</p>
            </div>
            <div class="project-grid project-grid--client"><?php project_card($projects['fortepiano-academy'], true); ?></div>
        </div>
    </section>

    <section class="portfolio-close section-red">
        <div class="section-shell portfolio-close__grid">
            <p class="section-index">Have a world, a system, or both?</p>
            <h2>Bring the difficult part.</h2>
            <div><p>WGS works from the real business and audience problem outward - through strategy, art direction, UX, design and development.</p><a class="button button-dark" href="/#contact">Start a project ↗</a></div>
        </div>
    </section>
</main>
<div id="case-study-viewer" class="case-viewer" hidden></div>
<?php portfolio_footer(); ?>
</body>
</html>
