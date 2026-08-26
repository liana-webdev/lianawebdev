<?php
declare(strict_types=1);
require dirname(__DIR__) . '/content/projects.php';
require dirname(__DIR__) . '/components/portfolio.php';
require dirname(__DIR__) . '/components/analytics.php';

if (!isset($projectSlug, $projects[$projectSlug]) || ($projects[$projectSlug]['public'] ?? false) !== true) {
    http_response_code(404);
    exit('Project not found');
}
$project = $projects[$projectSlug];
$nextProject = isset($project['next'], $projects[$project['next']]) && ($projects[$project['next']]['public'] ?? false) === true
    ? $projects[$project['next']]
    : null;
$canonical = 'https://webgirl.studio' . wgs_project_url($project['slug']);
$isInteractive = ($project['labStatus'] ?? '') === 'live';
$isLiveClient = ($project['labStatus'] ?? '') === 'live-client';
$primaryUrl = $isInteractive ? $project['labRoute'] : ($isLiveClient ? $project['liveRoute'] : '');
$primaryLabel = $isInteractive ? 'Explore interactive site' : ($isLiveClient ? 'Visit live site' : '');
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
    <?php wgs_analytics_head(); ?>
    <meta property="og:type" content="article"><meta property="og:title" content="<?= e($project['seoTitle']) ?>"><meta property="og:description" content="<?= e($project['seoDescription']) ?>"><meta property="og:url" content="<?= e($canonical) ?>">
    <?php if (!empty($project['media']['og'])): $og = $project['media']['og']; ?><meta property="og:image" content="https://webgirl.studio<?= e($og['src']) ?>"><meta property="og:image:alt" content="<?= e($og['alt']) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
    <?php if (!empty($project['media']['case-hero'])): ?><link rel="preload" as="image" href="<?= portfolio_asset($project['media']['case-hero']['src']) ?>" fetchpriority="high"><?php endif; ?>
    <link rel="stylesheet" href="<?= portfolio_asset('assets/styles.css') ?>"><link rel="stylesheet" href="<?= portfolio_asset('assets/portfolio.css') ?>">
    <script defer src="<?= portfolio_asset('assets/app.js') ?>"></script><script defer src="<?= portfolio_asset('assets/portfolio.js') ?>"></script>
</head>
<body class="portfolio-body project-body" id="top" style="<?= project_palette_style($project) ?>">
<?php portfolio_header('work'); ?>
<main id="main-content" class="portfolio-main project-page" data-project-page data-project-slug="<?= e($project['slug']) ?>">
<article>
    <header class="project-hero project-hero--proof-first">
        <div class="section-shell project-hero__grid">
            <p class="project-kicker reveal"><?= e($project['status']) ?> / <?= e($project['industry']) ?></p>
            <h1 class="reveal"><?= e($project['name']) ?></h1>
            <p class="project-hero__preview reveal"><?= e($project['caseTitle'] ?? $project['transformation']) ?></p>
            <div class="project-hero__actions reveal">
                <?php if ($primaryUrl !== ''): ?><a class="button button-red" href="<?= e($primaryUrl) ?>"<?= $isLiveClient ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($primaryLabel) ?> ↗</a><?php endif; ?>
                <a class="button button-outline" href="#project-breakdown">Project breakdown ↓</a>
            </div>
            <dl class="project-snapshot reveal"><div><dt>Category</dt><dd><?= e($project['industry']) ?></dd></div><div><dt>Status</dt><dd><?= e($project['status']) ?></dd></div><div><dt>Year</dt><dd><?= e($project['year']) ?></dd></div><div><dt>Scope</dt><dd><?= e($project['scope']) ?></dd></div></dl>
            <?php if (!$isLiveClient) project_disclosure(); ?>
        </div>
        <a class="section-shell project-hero__media project-hero__media-link" href="<?= e($primaryUrl !== '' ? $primaryUrl : '#project-breakdown') ?>" aria-label="<?= e($primaryLabel !== '' ? $primaryLabel : 'View project breakdown') ?>"><?php project_art($project, 'case-hero'); ?></a>
    </header>

    <section class="case-chapter section-offwhite" id="project-breakdown">
        <div class="section-shell case-copy-grid">
            <div><p class="section-index">01 / The challenge</p><h2>The problem,<br><em>without the essay.</em></h2></div>
            <div class="case-prose"><p><?= e(implode(' ', $project['reality'])) ?></p><h3>The visible request</h3><blockquote>“<?= e($project['request']) ?>”</blockquote><h3>The diagnosis</h3><p><?= e($project['diagnosis']) ?></p></div>
        </div>
    </section>

    <section class="case-system section-light">
        <div class="section-shell case-system__grid">
            <div><p class="section-index">02 / The system</p><h2>Clear routes.<br><em>One coherent experience.</em></h2><p><?= e($project['insight']) ?></p></div>
            <ol class="architecture-list"><?php foreach ($project['architecture'] as $index => $item): ?><li><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($item) ?></li><?php endforeach; ?></ol>
        </div>
        <div class="section-shell journey-list case-system__journeys"><?php foreach ($project['journeys'] as [$audience, $steps]): ?><article class="journey"><h3><?= e($audience) ?></h3><ol><?php foreach ($steps as $step): ?><li><?= e($step) ?></li><?php endforeach; ?></ol></article><?php endforeach; ?></div>
    </section>

    <section class="case-interface-proof section-dark">
        <div class="section-shell case-section-heading"><div><p class="section-index">03 / The experience</p><h2>Actual interface.<br><em>Actual interaction.</em></h2></div><p><?= e($project['visual']) ?></p></div>
        <?php if (!empty($project['media']['screen-desktop-01'])): ?><div class="section-shell interface-proof-grid"><?php project_media_frame($project, 'screen-desktop-01', 'media-frame--wide'); ?><?php if (!empty($project['media']['screen-mobile-01'])) project_media_frame($project, 'screen-mobile-01', 'media-frame--phone'); ?></div><?php else: ?><div class="section-shell"><?php project_art($project, 'case-hero'); ?></div><?php endif; ?>
        <?php if ($primaryUrl !== ''): ?><div class="section-shell case-experience-cta"><a class="button button-red" href="<?= e($primaryUrl) ?>"<?= $isLiveClient ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($primaryLabel) ?> ↗</a></div><?php endif; ?>
        <?php if (!empty($project['media']['screen-desktop-02'])): ?><div class="section-shell interface-proof-grid interface-proof-grid--second"><?php project_media_frame($project, 'screen-desktop-02', 'media-frame--wide'); ?><?php if (!empty($project['media']['screen-mobile-02'])) project_media_frame($project, 'screen-mobile-02', 'media-frame--phone'); ?></div><?php endif; ?>
    </section>

    <section class="case-validation section-offwhite">
        <div class="section-shell case-validation__grid"><div><p class="section-index">04 / Evidence</p><h2>Evidence,<br><em>not invented outcomes.</em></h2><p><?= $isLiveClient ? 'The live build and implementation are shown here. Business results will only be added when real, attributable data is available.' : 'The interactive build demonstrates responsive behavior, functional detail and technical execution. It does not claim fictional business results.' ?></p></div><ol><?php foreach ($project['validation'] as $index => $item): ?><li><span>0<?= $index + 1 ?></span><?= e($item) ?></li><?php endforeach; ?></ol></div>
    </section>

    <section class="case-deep-dives section-light">
        <div class="section-shell"><p class="section-index">Optional detail</p><h2>Go deeper if it matters.</h2>
            <details><summary>Deep dive: UX decisions <span>+</span></summary><div class="decision-list"><?php foreach ($project['decisions'] as $index => [$decision, $why, $validate]): ?><article><span>0<?= $index + 1 ?></span><div><h3><?= e($decision) ?></h3><dl><dt>Why</dt><dd><?= e($why) ?></dd><dt>How to validate</dt><dd><?= e($validate) ?></dd></dl></div></article><?php endforeach; ?></div></details>
            <details><summary>Responsive considerations <span>+</span></summary><ul class="case-detail-list"><?php foreach ($project['technical'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul></details>
            <details><summary>Strategy notes <span>+</span></summary><p><?= e($project['proves']) ?></p></details>
        </div>
    </section>

    <section class="case-conversion section-dark"><div class="section-shell case-conversion__grid"><p class="section-index">Ready for a clearer website?</p><div><h2><?= e($project['closeHeading']) ?></h2><p><?= e($project['closeBody']) ?></p><a class="button button-red" href="/#contact">Start a project →</a></div></div></section>
    <?php if ($nextProject !== null): ?><aside class="next-project section-offwhite"><div class="section-shell"><p class="section-index">Next project / <?= e($nextProject['industry']) ?></p><a href="<?= e(wgs_project_url($nextProject['slug'])) ?>"><span><?= e($nextProject['name']) ?></span><strong><?= e($nextProject['transformation']) ?></strong><b aria-hidden="true">→</b></a></div></aside><?php endif; ?>
</article>
</main>
<?php portfolio_footer(); ?>
</body>
</html>
