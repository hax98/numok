<?php
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$cookieDays = (int) ($program['cookie_days'] ?? 0);
?>
<div class="bg-surface text-ink min-h-screen">
    <header class="rp-header">
        <div class="rp-header-bar">
            <a href="/" class="rp-brand" aria-label="Repostit Partners home">
                <img src="/assets/brand/repostit-mark.png" alt="" width="34" height="24">
                Repostit
                <span class="rp-brand-tag hidden sm:inline">Partners</span>
            </a>
            <nav aria-label="Main" class="hidden md:flex items-center gap-0.5 mr-2.5">
                <a class="rp-ghost" href="#how-it-works">How it works</a>
                <a class="rp-ghost" href="#program">Program details</a>
                <a class="rp-ghost" href="https://repostit.io/">About Repostit</a>
            </nav>
            <a class="rp-ghost" href="/login">Log in</a>
            <a class="rp-btn rp-btn-s ml-1" href="/register">Join free</a>
        </div>
    </header>

    <div>
        <section class="rp-page grid items-center gap-12 lg:gap-16 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] pt-14 sm:pt-20 pb-6" aria-labelledby="hero-title">
            <div>
                <p class="rp-eyebrow">Repostit partner program</p>
                <h1 id="hero-title" class="rp-display-xl mt-4 max-w-[13ch]">Recommend Repostit. Earn 20% every month.</h1>
                <p class="rp-lead mt-5 max-w-[34rem]">Share Repostit with creators and businesses who post on TikTok first. You earn 20% of every paid subscription you refer, for as long as they stay subscribed.</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-2">
                    <a class="rp-btn rp-btn-l" href="/register">Join free</a>
                    <a class="rp-btn rp-btn-l rp-btn-secondary" href="/login">Log in</a>
                </div>
                <p class="rp-small text-text-muted mt-4">Free to join. A $200 bonus at 20 paying subscribers, and $5,000 more at 500.</p>
            </div>
            <figure class="m-0">
                <video class="block w-full h-auto rounded-2xl border border-line" src="/assets/media/workflows-loop.mp4" poster="/assets/media/workflows-poster.jpg" width="1600" height="1094" muted loop playsinline autoplay preload="metadata" aria-label="The Repostit Workflows screen: TikTok reposting to Instagram Reels, YouTube Shorts, Facebook Reels and X, each switching from Manual to Auto"></video>
                <figcaption class="rp-caption text-text-muted mt-3">The Workflows screen your audience will use. Every new TikTok goes out to the other apps.</figcaption>
            </figure>
        </section>

        <section class="rp-page rp-section" aria-labelledby="why-title">
            <h2 id="why-title" class="sr-only">Why partners join</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <article class="rp-card">
                    <span class="rp-tile" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                    <h3 class="rp-heading-s mt-5">20% recurring</h3>
                    <p class="text-text-secondary mt-2">You earn on every payment from every subscription you refer, not only the first one.</p>
                </article>
                <article class="rp-card">
                    <span class="rp-tile" style="background:#e8f0fb;color:#1f5bb8" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>
                    <h3 class="rp-heading-s mt-5">One link, your code</h3>
                    <p class="text-text-secondary mt-2">Pick a code that sounds like you. Add separate links for a Reel, a Story or your bio to see which one works.</p>
                </article>
                <article class="rp-card">
                    <span class="rp-tile" style="background:#e5f3ea;color:#2f7a4c" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18M7 15l4-4 3 3 5-6"/></svg></span>
                    <h3 class="rp-heading-s mt-5">Clear reporting</h3>
                    <p class="text-text-secondary mt-2">Clicks, sign-ups, paying customers and earnings, all in your partner dashboard.</p>
                </article>
            </div>
        </section>

        <section class="rp-page rp-section" id="how-it-works" aria-labelledby="how-title">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                <div>
                    <p class="rp-eyebrow">How it works</p>
                    <h2 id="how-title" class="rp-display-l mt-3 max-w-[16ch]">Use it first, then share it.</h2>
                    <p class="text-text-secondary mt-4 max-w-[28rem]">Recommendations work best when your audience sees you use the product. Try Repostit on your own account, then show the workflow you actually run.</p>
                </div>
                <ol class="list-none m-0 p-0 border-t border-line">
                    <li class="grid grid-cols-[48px_1fr] gap-4 py-6 border-b border-line">
                        <span class="rp-heading-s text-text-muted">1</span>
                        <div><h3 class="rp-heading-s">Create your partner account</h3><p class="text-text-secondary mt-1">It's free. Join the program and choose your referral code.</p></div>
                    </li>
                    <li class="grid grid-cols-[48px_1fr] gap-4 py-6 border-b border-line">
                        <span class="rp-heading-s text-text-muted">2</span>
                        <div><h3 class="rp-heading-s">Share your link</h3><p class="text-text-secondary mt-1">Put it in your bio, a Story link sticker, a video description, or a reply to someone who asked.</p></div>
                    </li>
                    <li class="grid grid-cols-[48px_1fr] gap-4 py-6 border-b border-line">
                        <span class="rp-heading-s text-text-muted">3</span>
                        <div><h3 class="rp-heading-s">Get paid every month</h3><p class="text-text-secondary mt-1">When someone you referred pays for Repostit, 20% of each payment is yours while they stay subscribed.</p></div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="rp-page rp-section" id="program" aria-labelledby="program-title">
            <p class="rp-eyebrow">Program details</p>
            <h2 id="program-title" class="rp-display-l mt-3">What you earn</h2>
            <dl class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 m-0">
                <div class="rp-card"><dt class="rp-label">Commission</dt><dd class="m-0 mt-3 rp-display-l">20%</dd><dd class="m-0 mt-2 text-text-secondary">Of every payment, every month the customer stays</dd></div>
                <div class="rp-card"><dt class="rp-label">First bonus</dt><dd class="m-0 mt-3 rp-display-l">$200</dd><dd class="m-0 mt-2 text-text-secondary">When 20 of your referrals are paying subscribers</dd></div>
                <div class="rp-card"><dt class="rp-label">Second bonus</dt><dd class="m-0 mt-3 rp-display-l">$5,000</dd><dd class="m-0 mt-2 text-text-secondary">On top, when you reach 500 paying subscribers</dd></div>
                <div class="rp-card"><dt class="rp-label">Cost to join</dt><dd class="m-0 mt-3 rp-display-l">$0</dd><dd class="m-0 mt-2 text-text-secondary"><?= $cookieDays > 0 ? 'Referrals are credited to you for ' . $e($cookieDays) . ' days after a click' : 'No fees and no minimum audience size' ?></dd></div>
            </dl>
            <p class="rp-caption text-text-muted mt-4">Full terms are shown when you create your account.</p>
        </section>

        <section class="rp-page rp-section pb-24" aria-labelledby="cta-title">
            <div class="rp-panel-dark flex flex-col md:flex-row md:items-center justify-between gap-8 p-8 sm:p-12">
                <div>
                    <h2 id="cta-title" class="rp-display-l text-surface">Start with your link.</h2>
                    <p class="mt-3 text-on-dark max-w-[32rem]">Create your free partner account and get your referral link right away.</p>
                </div>
                <a class="rp-btn rp-btn-l rp-btn-on-dark shrink-0" href="/register">Join free</a>
            </div>
        </section>
    </div>

    <footer class="border-t border-line-soft">
        <div class="rp-page py-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="/" class="rp-brand" style="font-size:18px" aria-label="Repostit Partners home"><img src="/assets/brand/repostit-mark.png" alt="" width="28" height="20" style="width:28px">Repostit Partners</a>
            <nav aria-label="Footer" class="flex flex-wrap gap-x-1 -ml-3 sm:ml-0">
                <a class="rp-ghost text-text-secondary" style="font-size:15px" href="https://repostit.io/">repostit.io</a>
                <a class="rp-ghost text-text-secondary" style="font-size:15px" href="/login">Log in</a>
                <a class="rp-ghost text-text-secondary" style="font-size:15px" href="/register">Join free</a>
            </nav>
            <p class="rp-caption text-text-muted m-0">© <?= date('Y') ?> Repostit</p>
        </div>
    </footer>
</div>
