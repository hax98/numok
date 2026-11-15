<?php
// Shared frame for partner sign-in, sign-up and password screens.
// Set $authAside = false before including to hide the product panel.
$authAside = $authAside ?? true;
?>
<div class="min-h-screen bg-surface text-ink grid <?= $authAside ? 'lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]' : '' ?>">
    <div class="flex min-h-screen flex-col">
        <header class="rp-header-bar w-full" style="max-width:none">
            <a href="/" class="rp-brand" aria-label="Repostit Partners home">
                <img src="/assets/brand/repostit-mark.png" alt="" width="34" height="24">
                Repostit
                <span class="rp-brand-tag">Partners</span>
            </a>
            <a class="rp-ghost hidden sm:inline-flex" href="/">Partner program</a>
        </header>
        <main class="flex-1 flex items-start sm:items-center justify-center px-4 sm:px-6 py-10 sm:py-16">
            <div class="w-full max-w-[440px]">
