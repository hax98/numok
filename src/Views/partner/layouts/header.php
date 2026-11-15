<?php
// File: src/Views/layouts/header.php
$path='partner/'.trim(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/');
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Partner Portal - ' . ($settings['custom_app_name'] ?? 'Repostit Partners')) ?></title>
<?php require ROOT_PATH . '/src/Views/partials/head.php'; ?>
</head>
<body class="h-full">
    <?php if (isset($_SESSION['partner_id'])): ?>
    <div class="min-h-full">
        <header class="rp-header" x-data="{ mobileMenuOpen: false, open: false }" @keydown.escape.window="mobileMenuOpen = false; open = false">
            <div class="rp-header-bar">
                <a href="/dashboard" class="rp-brand" aria-label="Repostit Partners dashboard">
                    <?php if (!empty($settings['custom_logo'])): ?>
                        <img src="/assets/uploads/<?= htmlspecialchars($settings['custom_logo']) ?>" alt="<?= htmlspecialchars($settings['custom_app_name'] ?? 'App') ?>" style="width:auto;height:32px;max-width:12rem;object-fit:contain" />
                    <?php else: ?>
                        <img src="/assets/brand/repostit-mark.png" alt="" width="34" height="24" />Repostit<span class="rp-brand-tag hidden sm:inline">Partners</span>
                    <?php endif; ?>
                </a>
                <?php $navItems = ['partner/dashboard' => ['/dashboard', 'Dashboard'], 'partner/programs' => ['/programs', 'Programs'], 'partner/earnings' => ['/earnings', 'Earnings']]; ?>
                <nav aria-label="Main" class="hidden sm:flex items-center gap-0.5 mr-2">
                    <?php foreach ($navItems as $key => [$href, $label]): ?>
                        <a href="<?= $href ?>" class="rp-ghost" <?= $path === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
                    <?php endforeach; ?>
                </nav>
                <div class="relative hidden sm:block">
                    <button @click="open = !open" type="button" class="rp-ghost gap-2" :aria-expanded="open.toString()" aria-haspopup="true" aria-controls="account-menu">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-surface-subtle border border-line text-sm font-medium" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['partner_company'] ?? 'P', 0, 1))) ?></span>
                        <span class="sr-only">Account menu</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div id="account-menu" x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 w-60 rounded-[14px] border border-line bg-surface p-1.5 shadow-menu" role="menu">
                        <div class="px-3 py-2">
                            <p class="rp-caption text-text-muted m-0">Logged in as</p>
                            <p class="font-medium m-0 truncate"><?= htmlspecialchars($_SESSION['partner_company'] ?? '') ?></p>
                        </div>
                        <div class="my-1 border-t border-line-soft"></div>
                        <a href="/settings" class="flex min-h-[44px] items-center rounded-[10px] px-3 hover:bg-surface-subtle" role="menuitem">Settings</a>
                        <a href="/logout" class="flex min-h-[44px] items-center rounded-[10px] px-3 hover:bg-surface-subtle" role="menuitem">Log out</a>
                    </div>
                </div>
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="sm:hidden grid h-11 w-11 place-items-center rounded-[10px] hover:bg-surface-hover" :aria-expanded="mobileMenuOpen.toString()" aria-controls="mobile-menu">
                    <span class="sr-only" x-text="mobileMenuOpen ? 'Close menu' : 'Open menu'">Open menu</span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path :d="mobileMenuOpen ? 'M18 6 6 18M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div id="mobile-menu" class="sm:hidden border-t border-line-soft px-3 pb-4 pt-2" x-show="mobileMenuOpen" x-cloak>
                <?php foreach ($navItems as $key => [$href, $label]): ?>
                    <a href="<?= $href ?>" class="flex min-h-[48px] items-center rounded-[10px] px-3 font-medium <?= $path === $key ? 'bg-brand-tint text-brand-text' : 'hover:bg-surface-subtle' ?>" <?= $path === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
                <?php endforeach; ?>
                <div class="my-2 border-t border-line-soft"></div>
                <p class="px-3 py-1 rp-caption text-text-muted m-0"><?= htmlspecialchars($_SESSION['partner_email'] ?? $_SESSION['partner_company'] ?? '') ?></p>
                <a href="/settings" class="flex min-h-[48px] items-center rounded-[10px] px-3 font-medium hover:bg-surface-subtle">Settings</a>
                <a href="/logout" class="flex min-h-[48px] items-center rounded-[10px] px-3 font-medium hover:bg-surface-subtle">Log out</a>
            </div>
        </header>

        <main>
            <div class="rp-page py-8">
    <?php else: ?>
        <main>
    <?php endif; ?>
