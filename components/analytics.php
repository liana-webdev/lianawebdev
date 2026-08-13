<?php
declare(strict_types=1);

const WGS_GA4_MEASUREMENT_ID = 'G-LPBCC632PW';

function wgs_analytics_head(): void
{
    $asset = dirname(__DIR__) . '/assets/analytics-events.js';
    $version = is_file($asset) ? substr((string) hash_file('sha256', $asset), 0, 12) : '1';
    $measurementId = json_encode(WGS_GA4_MEASUREMENT_ID, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $tagUrl = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode(WGS_GA4_MEASUREMENT_ID);

    echo '<script>';
    echo 'window["ga-disable-' . WGS_GA4_MEASUREMENT_ID . '"]=!/^((www)\.)?webgirl\.studio$/i.test(window.location.hostname);';
    echo 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}';
    echo 'gtag("js",new Date());gtag("config",' . $measurementId . ');';
    echo '</script>';
    echo '<script async src="' . htmlspecialchars($tagUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"></script>';
    echo '<script defer src="/assets/analytics-events.js?v=' . htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"></script>';
}
