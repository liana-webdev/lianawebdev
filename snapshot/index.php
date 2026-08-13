<?php
declare(strict_types=1);

require dirname(__DIR__) . '/components/analytics.php';

$snapshots = require dirname(__DIR__) . '/content/outreach-snapshots.php';
$slug = strtolower(trim((string) ($_GET['business'] ?? '')));
$validSlug = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
$snapshot = $validSlug && isset($snapshots[$slug]) ? $snapshots[$slug] : null;

if (!is_array($snapshot)) {
    http_response_code(404);
}

function snapshot_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function snapshot_asset(string $path): string
{
    $cleanPath = ltrim($path, '/');
    $absolutePath = dirname(__DIR__) . '/' . $cleanPath;
    $version = is_file($absolutePath) ? substr((string) hash_file('sha256', $absolutePath), 0, 12) : '1';
    return '/' . snapshot_e($cleanPath) . '?v=' . snapshot_e($version);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#080808">
    <title><?= $snapshot ? snapshot_e($snapshot['business']) . ' | WGS Conversion Snapshot' : 'Snapshot not found | Web Girl Studio' ?></title>
    <meta name="description" content="A concise, independent conversion snapshot prepared by Web Girl Studio.">
    <link rel="icon" href="<?= snapshot_asset('assets/favicon.svg') ?>" type="image/svg+xml">
    <?php wgs_analytics_head(); ?>
    <link rel="stylesheet" href="<?= snapshot_asset('assets/snapshot.css') ?>">
</head>
<body>
<a class="snapshot-skip" href="#snapshot-main">Skip to snapshot</a>

<header class="snapshot-header">
    <a class="snapshot-brand" href="/" aria-label="Web Girl Studio home">
        <strong>WEB GIRL</strong>
        <span>studio</span>
    </a>
    <p>Independent conversion review</p>
</header>

<?php if (!$snapshot): ?>
    <main class="snapshot-not-found" id="snapshot-main">
        <p class="snapshot-kicker">Private snapshot</p>
        <h1>This snapshot link is incomplete or no longer available.</h1>
        <p>Please use the complete link from the email you received.</p>
        <a class="snapshot-button" href="/">Visit Web Girl Studio</a>
    </main>
<?php else: ?>
    <main id="snapshot-main">
        <section class="snapshot-hero">
            <div class="snapshot-shell snapshot-hero-grid">
                <div>
                    <p class="snapshot-kicker">WGS Conversion Snapshot / <?= snapshot_e($snapshot['location']) ?></p>
                    <h1>Prepared for<br><em><?= snapshot_e($snapshot['business']) ?></em></h1>
                    <p class="snapshot-summary"><?= snapshot_e($snapshot['summary']) ?></p>
                    <div class="snapshot-meta">
                        <span><?= snapshot_e($snapshot['niche']) ?></span>
                        <span>Reviewed <?= snapshot_e($snapshot['reviewed_at']) ?></span>
                    </div>
                </div>
                <aside class="snapshot-page-card" aria-label="Page reviewed">
                    <p>Page reviewed</p>
                    <strong><?= snapshot_e($snapshot['page_label']) ?></strong>
                    <a href="<?= snapshot_e($snapshot['page_url']) ?>" target="_blank" rel="noopener noreferrer">Open the current page <span aria-hidden="true">↗</span></a>
                </aside>
            </div>
        </section>

        <section class="snapshot-strength">
            <div class="snapshot-shell snapshot-two-column">
                <p class="snapshot-section-label">01 / What is already working</p>
                <div>
                    <h2>The offer is stronger than the journey around it.</h2>
                    <p><?= snapshot_e($snapshot['strength']) ?></p>
                </div>
            </div>
        </section>

        <section class="snapshot-issues">
            <div class="snapshot-shell">
                <div class="snapshot-section-heading">
                    <p class="snapshot-section-label">02 / Three conversion leaks</p>
                    <h2>Where a qualified visitor can lose confidence or momentum.</h2>
                </div>
                <div class="snapshot-issue-grid">
                    <?php foreach ($snapshot['issues'] as $index => $issue): ?>
                        <article class="snapshot-issue">
                            <span>0<?= $index + 1 ?></span>
                            <h3><?= snapshot_e($issue['title']) ?></h3>
                            <p><?= snapshot_e($issue['evidence']) ?></p>
                            <div>
                                <strong>Why it matters</strong>
                                <p><?= snapshot_e($issue['impact']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="snapshot-repair">
            <div class="snapshot-shell">
                <div class="snapshot-section-heading snapshot-section-heading-light">
                    <p class="snapshot-section-label">03 / First repair direction</p>
                    <h2>Turn existing strengths into one clear decision path.</h2>
                    <p><?= snapshot_e($snapshot['first_repair']) ?></p>
                </div>
                <div class="snapshot-paths">
                    <article>
                        <p>Current path</p>
                        <ol>
                            <?php foreach ($snapshot['current_path'] as $step): ?>
                                <li><?= snapshot_e($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </article>
                    <article class="snapshot-path-recommended">
                        <p>Recommended path</p>
                        <ol>
                            <?php foreach ($snapshot['recommended_path'] as $step): ?>
                                <li><?= snapshot_e($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </article>
                </div>
            </div>
        </section>

        <section class="snapshot-next" data-analytics-location="outreach_snapshot">
            <div class="snapshot-shell snapshot-next-grid">
                <div>
                    <p class="snapshot-section-label">Useful next step</p>
                    <h2>This is a focused first look, not a disguised generic audit.</h2>
                    <p>If the direction is useful, reply to the email I sent. I can show what the full diagnosis would cover before any build decision.</p>
                </div>
                <div class="snapshot-actions">
                    <a class="snapshot-button" href="mailto:liana.webdev@gmail.com?subject=<?= rawurlencode($snapshot['business'] . ' conversion snapshot') ?>">Reply to Liana</a>
                    <a class="snapshot-text-link" href="/#contact">View the full diagnosis <span aria-hidden="true">↗</span></a>
                </div>
            </div>
        </section>
    </main>
<?php endif; ?>

<footer class="snapshot-footer">
    <p>Web Girl Studio / Sydney</p>
    <p>This independent review is based on the public page above. It is not a full technical audit or implementation specification.</p>
</footer>
</body>
</html>
