<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';

if (!isset($projectSlug, $projects[$projectSlug])) {
    http_response_code(404);
    exit('Project not found');
}
$project = $projects[$projectSlug];
$nextProject = $projects[$project['next']];
$canonical = 'https://webgirl.studio' . wgs_project_url($project['slug']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($project['seoTitle']) ?></title>
    <meta name="description" content="<?= e($project['seoDescription']) ?>">
    <meta name="theme-color" content="<?= e($project['palette'][0]) ?>">
    <link rel="icon" href="<?= portfolio_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= e($project['seoTitle']) ?>">
    <meta property="og:description" content="<?= e($project['seoDescription']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/styles.css') ?>">
    <link rel="stylesheet" href="<?= portfolio_asset('assets/portfolio.css') ?>">
    <script defer src="<?= portfolio_asset('assets/app.js') ?>"></script>
    <script defer src="<?= portfolio_asset('assets/portfolio.js') ?>"></script>
</head>
<body class="portfolio-body project-body" id="top" style="<?= project_palette_style($project) ?>">
<?php portfolio_header('work'); ?>
<main id="main-content" class="portfolio-main project-page" data-project-page data-project-slug="<?= e($project['slug']) ?>">
    <article>
        <header class="project-hero">
            <div class="section-shell project-hero__grid">
                <p class="project-kicker reveal"><?= e($project['status']) ?> / <?= e($project['industry']) ?></p>
                <h1 class="reveal"><?= e($project['transformation']) ?></h1>
                <p class="project-hero__preview reveal"><?= e($project['preview']) ?></p>
                <dl class="project-snapshot reveal">
                    <div><dt>Project</dt><dd><?= e($project['name']) ?></dd></div>
                    <div><dt>Territory</dt><dd><?= e($project['location']) ?></dd></div>
                    <div><dt>Year</dt><dd><?= e($project['year']) ?></dd></div>
                    <div><dt>Scope</dt><dd><?= e($project['scope']) ?></dd></div>
                </dl>
                <?php project_disclosure(); ?>
            </div>
            <div class="section-shell project-hero__media"><?php project_art($project, 'case-hero'); ?></div>
        </header>

        <section class="case-chapter case-chapter--reality section-offwhite">
            <div class="section-shell case-copy-grid">
                <div><p class="section-index">01 / The business reality</p><h2>The context before<br><em>the interface.</em></h2></div>
                <div class="case-prose"><?php foreach ($project['reality'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?></div>
            </div>
        </section>

        <section class="case-diagnosis section-light">
            <div class="section-shell case-diagnosis__grid">
                <div class="case-request"><p class="section-index">02 / Visible request</p><blockquote>“<?= e($project['request']) ?>”</blockquote></div>
                <div class="case-actual"><p class="section-index">03 / Actual diagnosis</p><h2><?= e($project['diagnosis']) ?></h2></div>
            </div>
        </section>

        <section class="case-journeys section-dark">
            <div class="section-shell">
                <div class="case-section-heading"><p class="section-index">04 / Audience journeys</p><h2>One world.<br><em>Different reasons to enter.</em></h2></div>
                <div class="journey-list">
                    <?php foreach ($project['journeys'] as [$audience, $steps]): ?>
                        <article class="journey"><h3><?= e($audience) ?></h3><ol><?php foreach ($steps as $step): ?><li><?= e($step) ?></li><?php endforeach; ?></ol></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="case-insight section-red">
            <div class="section-shell case-insight__grid"><p class="section-index">05 / Central insight</p><h2><?= e($project['insight']) ?></h2></div>
        </section>

        <section class="case-system section-offwhite">
            <div class="section-shell case-system__grid">
                <div><p class="section-index">06 / Strategic response</p><h2>The system behind<br><em>the atmosphere.</em></h2><p><?= e($project['proof']) ?></p></div>
                <ol class="architecture-list"><?php foreach ($project['architecture'] as $index => $item): ?><li><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($item) ?></li><?php endforeach; ?></ol>
            </div>
        </section>

        <section class="case-visual-world section-dark">
            <div class="section-shell case-visual-world__copy"><p class="section-index">07 / Visual world</p><h2>A visual language with a job.</h2><p><?= e($project['visual']) ?></p></div>
            <div class="section-shell project-proof-grid">
                <?php project_art($project, 'desktop-system'); ?>
                <?php project_art($project, 'mobile-system'); ?>
                <?php project_art($project, 'interface-detail'); ?>
            </div>
        </section>

        <section class="case-decisions section-light">
            <div class="section-shell"><div class="case-section-heading"><p class="section-index">08 / Key experience decisions</p><h2>Design choices,<br><em>with reasons.</em></h2></div>
                <div class="decision-list">
                    <?php foreach ($project['decisions'] as $index => [$decision, $why, $validate]): ?><article><span>0<?= $index + 1 ?></span><div><h3><?= e($decision) ?></h3><dl><dt>Why</dt><dd><?= e($why) ?></dd><dt>How to validate</dt><dd><?= e($validate) ?></dd></dl></div></article><?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="case-technical section-offwhite">
            <div class="section-shell case-technical__grid"><div><p class="section-index">09 / Responsive + technical layer</p><h2>The world still works<br><em>at 320 pixels.</em></h2></div><ul><?php foreach ($project['technical'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul></div>
        </section>

        <section class="case-validation section-dark">
            <div class="section-shell case-validation__grid"><div><p class="section-index">10 / Honest evidence</p><h2>Validation plan,<br><em>not invented results.</em></h2><p>These checks are planned for the interactive build. They are not presented as completed outcomes.</p></div><ol><?php foreach ($project['validation'] as $index => $item): ?><li><span>0<?= $index + 1 ?></span><?= e($item) ?></li><?php endforeach; ?></ol></div>
        </section>

        <section class="case-proves section-red">
            <div class="section-shell case-proves__grid"><p class="section-index">11 / What this proves</p><h2><?= e($project['proves']) ?></h2></div>
        </section>

        <section class="case-lab section-light">
            <div class="section-shell case-lab__grid"><div><p class="section-index">WGS Lab</p><h2>Interactive build<br><em>coming later.</em></h2></div><p><?= e($project['lab']) ?><br><br><span class="lab-disabled" aria-disabled="true">Interactive build coming later</span></p></div>
        </section>

        <section class="case-conversion section-dark">
            <div class="section-shell case-conversion__grid"><p class="section-index">Recognise this problem in your own work?</p><div><h2><?= e($project['closeHeading']) ?></h2><p><?= e($project['closeBody']) ?></p><div class="hero-actions"><a class="button button-red" href="/#contact">Start a project</a><a class="button button-outline" href="/work/">Explore more work</a></div></div></div>
        </section>

        <aside class="next-project section-offwhite">
            <div class="section-shell"><p class="section-index">Next project / <?= e($nextProject['industry']) ?></p><a href="<?= e(wgs_project_url($nextProject['slug'])) ?>"><span><?= e($nextProject['name']) ?></span><strong><?= e($nextProject['transformation']) ?></strong><b aria-hidden="true">→</b></a></div>
        </aside>
    </article>
</main>
<?php portfolio_footer(); ?>
</body>
</html>
