<?php
// Shared <head> assets for every layout: Repostit design system stylesheet, self-hosted Geist,
// pinned Alpine and the Repostit icons. Bump $assetVersion when the built CSS changes.
$assetVersion = '20261115';
?>
    <link rel="preload" href="/assets/fonts/geist-variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $assetVersion ?>">
    <script src="/assets/js/vendor/alpine-3.14.9.min.js" defer></script>
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="/assets/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
    <style>[x-cloak]{display:none!important}</style>
