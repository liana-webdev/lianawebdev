<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selected Work | Web Girl Studio</title>
    <meta name="description" content="Selected client, founder-built and independent Web Girl Studio projects across cultural worlds and conversion systems.">
    <meta name="theme-color" content="#080808">
    <link rel="icon" href="<?= portfolio_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="canonical" href="https://webgirl.studio/work/">
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
            <p class="section-index reveal">Work / Selected systems</p>
            <h1 class="reveal">Website projects for artists, cultural brands and growing businesses.</h1>
            <p class="portfolio-hero__intro reveal">Each project starts with a real problem: scattered platform links, a store that hides the quality of the clothes, or a body of work that is hard to explore. The case studies show what changed, why it changed and how the website works.</p>
        </div>
    </section>

    <section class="work-index section-offwhite" aria-labelledby="work-index-title">
        <div class="section-shell">
            <div class="work-index__heading">
                <div><p class="section-index">Portfolio index</p><h2 id="work-index-title">Five projects.<br><em>Five different jobs.</em></h2></div>
                <p id="project-count" class="filter-status" aria-live="polite"><?= count($projects) ?> projects shown</p>
            </div>
            <div class="project-filters" role="group" aria-label="Filter projects">
                <?php foreach ($filterLabels as $key => $label): ?>
                    <button type="button" data-project-filter="<?= e($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="project-grid" data-project-grid>
                <?php foreach ($projects as $project) project_card($project, true); ?>
            </div>
            <noscript><p class="noscript-note">Filters require JavaScript. All projects remain available above.</p></noscript>
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
