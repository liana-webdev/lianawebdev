<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function portfolio_asset(string $path): string
{
    $clean = ltrim($path, '/');
    $absolute = dirname(__DIR__) . '/' . $clean;
    $version = is_file($absolute) ? substr((string) hash_file('sha256', $absolute), 0, 12) : '1';
    return '/' . e($clean) . '?v=' . $version;
}

function portfolio_brand_mark(bool $light = false): void
{
    $class = $light ? 'brand-mark brand-mark-light' : 'brand-mark';
    echo '<span class="' . $class . '" aria-label="Web Girl Studio">';
    echo '<span class="brand-web">WEB GIRL</span><span class="brand-star" aria-hidden="true">✦</span>';
    echo '<span class="brand-studio">studio</span></span>';
}

function portfolio_header(string $current = ''): void
{
    $links = ['work' => ['/work/', 'Work'], 'culture' => ['/culture/', 'Culture'], 'services' => ['/#services', 'Services'], 'about' => ['/#about', 'About']];
    echo '<a class="skip-link" href="#main-content">Skip to content</a><div class="progress-line" aria-hidden="true"></div>';
    echo '<header class="site-header portfolio-header"><a href="/" class="header-brand">';
    portfolio_brand_mark(true);
    echo '</a><nav class="site-nav" aria-label="Primary navigation">';
    foreach ($links as $key => [$href, $label]) {
        $active = $current === $key ? ' aria-current="page"' : '';
        echo '<a href="' . e($href) . '"' . $active . '>' . e($label) . '</a>';
    }
    echo '</nav><a class="header-cta" href="/#contact">Start a project <span>↗</span></a>';
    echo '<button class="menu-toggle" type="button" aria-expanded="false" aria-label="Toggle navigation"><span></span><span></span></button></header>';
}

function portfolio_footer(): void
{
    echo '<footer class="site-footer portfolio-footer"><div class="footer-top">';
    portfolio_brand_mark();
    echo '<p>Built to be felt. Structured to work.</p><a href="#top">Back to top ↑</a></div>';
    echo '<div class="footer-bottom"><span>© ' . date('Y') . ' Web Girl Studio</span><span>Sydney / Australia-wide</span><span>Founder-led strategy, design + development</span></div></footer>';
}

function project_palette_style(array $project): string
{
    $palette = $project['palette'];
    return '--project-bg:' . e($palette[0]) . ';--project-paper:' . e($palette[1]) . ';--project-mid:' . e($palette[2]) . ';--project-accent:' . e($palette[3]) . ';--project-signal:' . e($palette[4]) . ';';
}

function project_art(array $project, string $role = 'cover', bool $compact = false): void
{
    $media = $project['media'][$role] ?? null;
    if (is_array($media)) {
        $class = 'project-art project-art--real project-art--' . e($project['slug']) . ' project-art--' . e($role) . ($compact ? ' project-art--compact' : '');
        $position = $media['position'] ?? 'center center';
        echo '<picture class="' . $class . '" style="' . project_palette_style($project) . '--media-position:' . e($position) . ';">';
        if (!empty($media['mobileSrc'])) {
            echo '<source media="(max-width: 560px)" srcset="' . portfolio_asset($media['mobileSrc']) . '" width="' . e((string) $media['mobileWidth']) . '" height="' . e((string) $media['mobileHeight']) . '">';
        }
        if (!empty($media['fallback'])) {
            echo '<source type="image/webp" srcset="' . portfolio_asset($media['src']) . '">';
            $src = $media['fallback'];
        } else {
            $src = $media['src'];
        }
        $loading = $role === 'case-hero' ? 'eager' : 'lazy';
        $priority = $role === 'case-hero' ? ' fetchpriority="high"' : '';
        echo '<img src="' . portfolio_asset($src) . '" alt="' . e($media['alt']) . '" width="' . e((string) $media['width']) . '" height="' . e((string) $media['height']) . '" loading="' . $loading . '" decoding="async"' . $priority . '>';
        echo '</picture>';
        return;
    }

    $class = 'project-art project-art--' . e($project['slug']) . ' project-art--' . e($role) . ($compact ? ' project-art--compact' : '');
    echo '<div class="' . $class . '" style="' . project_palette_style($project) . '" role="img" aria-label="Art direction placeholder for ' . e($project['name']) . '">';
    echo '<span class="project-art__index">0' . e((string) $project['order']) . ' / ' . e($project['industry']) . '</span>';
    echo '<span class="project-art__name">' . e($project['name']) . '</span>';
    echo '<span class="project-art__role">' . e(ucfirst(str_replace('-', ' ', $role))) . ' · final project media pending</span>';
    echo '<span class="project-art__shape" aria-hidden="true"></span><span class="project-art__signal" aria-hidden="true"></span></div>';
}

function project_media_frame(array $project, string $key, string $class = ''): void
{
    $media = $project['media'][$key] ?? null;
    if (!is_array($media)) {
        project_art($project, $key);
        return;
    }
    $figureClass = trim('media-frame media-frame--' . $key . ' ' . $class);
    echo '<figure class="' . e($figureClass) . '">';
    echo '<div class="media-frame__image" style="--media-ratio:' . e((string) $media['width']) . ' / ' . e((string) $media['height']) . ';--media-position:' . e($media['position'] ?? 'center center') . ';">';
    echo '<img src="' . portfolio_asset($media['src']) . '" alt="' . e($media['alt']) . '" width="' . e((string) $media['width']) . '" height="' . e((string) $media['height']) . '" loading="lazy" decoding="async">';
    echo '</div>';
    if (!empty($media['caption']) || !empty($media['purpose'])) {
        echo '<figcaption><strong>' . e($media['purpose'] ?? 'Project media') . '</strong>';
        if (!empty($media['caption'])) echo '<span>' . e($media['caption']) . '</span>';
        echo '</figcaption>';
    }
    echo '</figure>';
}

function project_card(array $project, bool $viewer = false): void
{
    $tagValue = implode(' ', $project['tags']);
    $viewerAttr = $viewer ? ' data-viewer-link' : '';
    echo '<article class="project-card reveal" data-project-card data-tags="' . e($tagValue) . '" style="' . project_palette_style($project) . '">';
    echo '<a class="project-card__link" href="' . e(wgs_project_url($project['slug'])) . '"' . $viewerAttr . ' aria-label="View ' . e($project['name']) . ' case study">';
    echo '<div class="project-card__media">'; project_art($project, 'work-cover', true); echo '</div>';
    echo '<div class="project-card__body"><p class="project-kicker">' . e($project['status']) . ' / ' . e($project['industry']) . '</p>';
    echo '<h2>' . e($project['name']) . '</h2><p class="project-card__starting">' . e($project['starting']) . '</p>';
    echo '<p class="project-card__transformation">' . e($project['transformation']) . '</p><ul class="project-tags">';
    foreach (array_slice($project['capabilities'], 0, 4) as $capability) echo '<li>' . e($capability) . '</li>';
    echo '</ul><span class="project-card__action">View case study <b aria-hidden="true">↗</b></span></div></a></article>';
}

function project_disclosure(): void
{
    echo '<aside class="project-disclosure"><strong>Truth note</strong><p>' . e(WGS_CONCEPT_DISCLOSURE) . '</p></aside>';
}
