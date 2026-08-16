<?php
$partnerShareImage = '/assets/images/partner-og.png';
?>

<div class="partner-landing">
    <header class="partner-nav">
        <a href="/" class="partner-brand" aria-label="Repostit Partners home">
            <span class="partner-brand-mark"><img src="/assets/favicon/android-chrome-512x512.png" alt="" /></span>
            <span class="partner-brand-wordmark">Repostit</span>
            <span class="partner-brand-label">PARTNERS</span>
        </a>
        <nav class="partner-nav-links" aria-label="Primary navigation">
            <a href="#how-it-works">How it works</a>
            <a href="https://repostit.io" target="_blank" rel="noreferrer">Repostit</a>
        </nav>
        <div class="partner-nav-actions">
            <a href="/login" class="partner-login">Log in</a>
            <a href="/register" class="partner-button partner-button-small">Join free</a>
        </div>
    </header>

    <main>
        <section class="partner-hero" aria-labelledby="partner-hero-title">
            <div class="partner-hero-copy">
                <p class="partner-eyebrow">REPOSTIT AFFILIATE PARTNER PROGRAM</p>
                <h1 id="partner-hero-title">Help creators grow further. <em>Earn 20% recurring.</em></h1>
                <p class="partner-hero-lede">Join the Repostit partner program and earn recurring commission by sharing the tool creators, founders, and online businesses use to turn one video into content that performs everywhere.</p>
                <div class="partner-hero-actions">
                    <a href="/register" class="partner-button">Join free</a>
                    <a href="/login" class="partner-text-link">Already a partner? Log in</a>
                </div>
                <p class="partner-hero-note">Free to join. Share what you genuinely use.</p>
            </div>

            <div class="partner-hero-art" aria-label="One video flowing into content for multiple platforms">
                <div class="partner-art-label">ONE VIDEO, MORE REACH</div>
                <img src="/assets/images/partner-workflow.png" alt="A creator video flowing into multiple platform-ready posts" />
            </div>
        </section>

        <section class="partner-benefits" aria-label="Partner program benefits">
            <article>
                <span class="partner-benefit-kicker">01 / REVENUE</span>
                <h2>20% recurring</h2>
                <p>Earn on every valid paid subscription you refer for as long as the customer stays active.</p>
            </article>
            <article>
                <span class="partner-benefit-kicker">02 / SHARING</span>
                <h2>One simple link</h2>
                <p>Create your partner link, mention Repostit when it genuinely fits, and keep your voice.</p>
            </article>
            <article>
                <span class="partner-benefit-kicker">03 / VISIBILITY</span>
                <h2>Clear reporting</h2>
                <p>See clicks, referrals, conversions, and earnings from your partner dashboard.</p>
            </article>
        </section>

        <section class="partner-how" id="how-it-works" aria-labelledby="how-title">
            <div class="partner-section-intro">
                <p class="partner-eyebrow">HOW IT WORKS</p>
                <h2 id="how-title">A useful product makes a better recommendation.</h2>
                <p>Try Repostit first. If it earns a place in your workflow, share it with the people who would genuinely benefit.</p>
            </div>
            <ol class="partner-steps">
                <li>
                    <span class="partner-step-number">01</span>
                    <div><h3>Create your link</h3><p>Open a free partner account and copy your personal referral link.</p></div>
                </li>
                <li>
                    <span class="partner-step-number">02</span>
                    <div><h3>Share naturally</h3><p>Include Repostit in the videos, posts, or conversations where it is genuinely useful.</p></div>
                </li>
                <li>
                    <span class="partner-step-number">03</span>
                    <div><h3>Track earnings</h3><p>Watch your referrals and recurring commission from one simple dashboard.</p></div>
                </li>
            </ol>
        </section>

        <section class="partner-final-cta" aria-labelledby="final-cta-title">
            <div>
                <p class="partner-eyebrow">BUILT FOR PEOPLE WHO PUBLISH</p>
                <h2 id="final-cta-title">Make your next recommendation count.</h2>
            </div>
            <a href="/register" class="partner-button">Join the partner program</a>
        </section>
    </main>

    <footer class="partner-footer">
        <div class="partner-footer-brand">
            <span class="partner-brand-mark"><img src="/assets/favicon/android-chrome-512x512.png" alt="" /></span>
            <span>Repostit Partners</span>
        </div>
        <span>© <?= date('Y') ?> Repostit. Built for people who publish.</span>
        <a href="https://repostit.io" target="_blank" rel="noreferrer">repostit.io</a>
    </footer>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');

    .partner-landing {
        --ink: #17141c;
        --muted: #655f6d;
        --line: #dfdbe5;
        --paper: #fbfaf8;
        --purple: #5b3df5;
        --purple-dark: #4224cb;
        --lavender: #ebe7ff;
        --blush: #f7d6df;
        min-height: 100vh;
        background: var(--paper);
        color: var(--ink);
        font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
    }

    .partner-landing *, .partner-landing *::before, .partner-landing *::after { box-sizing: border-box; }
    .partner-landing a { color: inherit; text-decoration: none; }
    .partner-nav, .partner-hero, .partner-benefits, .partner-how, .partner-final-cta, .partner-footer { width: min(1180px, calc(100% - 48px)); margin-inline: auto; }
    .partner-nav { display: flex; align-items: center; justify-content: space-between; gap: 28px; padding: 26px 0; }
    .partner-brand, .partner-footer-brand { display: inline-flex; align-items: center; gap: 9px; font-weight: 700; }
    .partner-brand-mark { display: inline-grid; place-items: center; width: 31px; height: 31px; border-radius: 9px; background: var(--purple); overflow: hidden; }
    .partner-brand-mark img { width: 25px; height: 25px; object-fit: contain; filter: brightness(0) invert(1); }
    .partner-brand-wordmark { font-family: 'Space Grotesk', sans-serif; font-size: 19px; letter-spacing: -0.04em; }
    .partner-brand-label { margin-left: 5px; padding-left: 12px; border-left: 1px solid var(--line); color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .16em; }
    .partner-nav-links { display: flex; gap: 26px; margin-left: auto; color: var(--muted); font-size: 13px; }
    .partner-nav-links a:hover, .partner-login:hover, .partner-text-link:hover, .partner-footer a:hover { color: var(--purple); }
    .partner-nav-actions { display: flex; align-items: center; gap: 20px; font-size: 13px; font-weight: 600; }
    .partner-login { color: var(--muted); }
    .partner-button { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 0 23px; border: 1px solid var(--purple); border-radius: 999px; background: var(--purple); color: #fff !important; font-size: 14px; font-weight: 700; box-shadow: 0 8px 20px rgba(91, 61, 245, .16); transition: background .18s ease, transform .18s ease; }
    .partner-button:hover { background: var(--purple-dark); transform: translateY(-1px); }
    .partner-button-small { min-height: 39px; padding: 0 17px; font-size: 12px; }
    .partner-hero { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); align-items: center; gap: 64px; padding: 76px 0 88px; }
    .partner-eyebrow { margin: 0; color: var(--purple); font-size: 11px; font-weight: 700; letter-spacing: .17em; line-height: 1.4; }
    .partner-hero h1 { max-width: 620px; margin: 20px 0 22px; font-family: 'Space Grotesk', sans-serif; font-size: clamp(42px, 5.6vw, 76px); font-weight: 600; letter-spacing: -.075em; line-height: .98; }
    .partner-hero h1 em { color: var(--purple); font-style: normal; }
    .partner-hero-lede { max-width: 560px; margin: 0; color: var(--muted); font-size: 17px; line-height: 1.65; }
    .partner-hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 22px; margin-top: 31px; }
    .partner-text-link { color: var(--ink); font-size: 13px; font-weight: 600; }
    .partner-hero-note { margin: 18px 0 0; color: #8a8492; font-size: 12px; }
    .partner-hero-art { position: relative; padding: 17px; border: 1px solid var(--line); border-radius: 28px; background: var(--lavender); }
    .partner-hero-art::before { content: ''; position: absolute; top: -13px; right: 30px; width: 76px; height: 26px; border-radius: 2px; background: var(--blush); transform: rotate(4deg); }
    .partner-art-label { position: absolute; z-index: 1; top: 30px; left: 34px; padding: 7px 10px; border: 1px solid rgba(23, 20, 28, .14); border-radius: 999px; background: rgba(251, 250, 248, .9); color: var(--ink); font-size: 9px; font-weight: 700; letter-spacing: .15em; }
    .partner-hero-art img { display: block; width: 100%; height: auto; min-height: 370px; border-radius: 17px; object-fit: cover; object-position: center; }
    .partner-benefits { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
    .partner-benefits article { min-height: 218px; padding: 32px 32px 32px 0; }
    .partner-benefits article + article { padding-left: 32px; border-left: 1px solid var(--line); }
    .partner-benefit-kicker { color: var(--purple); font-size: 10px; font-weight: 700; letter-spacing: .15em; }
    .partner-benefits h2 { margin: 16px 0 8px; font-family: 'Space Grotesk', sans-serif; font-size: 25px; letter-spacing: -.045em; }
    .partner-benefits p, .partner-section-intro p, .partner-steps p { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.6; }
    .partner-how { display: grid; grid-template-columns: .85fr 1.15fr; gap: 100px; padding: 120px 0; }
    .partner-section-intro h2 { max-width: 440px; margin: 18px 0 18px; font-family: 'Space Grotesk', sans-serif; font-size: clamp(30px, 3.2vw, 46px); letter-spacing: -.065em; line-height: 1.02; }
    .partner-section-intro > p:last-child { max-width: 400px; }
    .partner-steps { list-style: none; margin: 0; padding: 0; }
    .partner-steps li { display: grid; grid-template-columns: 65px 1fr; gap: 21px; padding: 0 0 30px; }
    .partner-steps li + li { padding-top: 30px; border-top: 1px solid var(--line); }
    .partner-step-number { color: var(--purple); font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 700; }
    .partner-steps h3 { margin: -3px 0 7px; font-family: 'Space Grotesk', sans-serif; font-size: 22px; letter-spacing: -.04em; }
    .partner-final-cta { display: flex; align-items: center; justify-content: space-between; gap: 30px; margin-bottom: 80px; padding: 42px 47px; border-radius: 22px; background: var(--ink); color: #fff; }
    .partner-final-cta .partner-eyebrow { color: var(--blush); }
    .partner-final-cta h2 { margin: 11px 0 0; font-family: 'Space Grotesk', sans-serif; font-size: clamp(25px, 3vw, 40px); letter-spacing: -.06em; }
    .partner-final-cta .partner-button { flex-shrink: 0; border-color: #fff; background: #fff; color: var(--ink) !important; box-shadow: none; }
    .partner-final-cta .partner-button:hover { background: var(--blush); border-color: var(--blush); }
    .partner-footer { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 24px 0 30px; border-top: 1px solid var(--line); color: #8a8492; font-size: 11px; }
    .partner-footer-brand { color: var(--ink); font-size: 12px; }
    .partner-footer-brand .partner-brand-mark { width: 23px; height: 23px; border-radius: 7px; }
    .partner-footer-brand .partner-brand-mark img { width: 19px; height: 19px; }

    @media (max-width: 840px) {
        .partner-nav, .partner-hero, .partner-benefits, .partner-how, .partner-final-cta, .partner-footer { width: min(100% - 34px, 620px); }
        .partner-nav-links { display: none; }
        .partner-hero { grid-template-columns: 1fr; gap: 42px; padding: 56px 0 66px; }
        .partner-hero-art img { min-height: 0; }
        .partner-benefits { grid-template-columns: 1fr; }
        .partner-benefits article, .partner-benefits article + article { min-height: 0; padding: 27px 0; border-left: 0; }
        .partner-benefits article + article { border-top: 1px solid var(--line); }
        .partner-how { grid-template-columns: 1fr; gap: 48px; padding: 82px 0; }
        .partner-final-cta { align-items: flex-start; flex-direction: column; margin-bottom: 55px; padding: 32px 27px; }
        .partner-footer { align-items: flex-start; flex-direction: column; }
    }
    @media (max-width: 520px) {
        .partner-nav { padding: 18px 0; }
        .partner-brand-label, .partner-login { display: none; }
        .partner-hero h1 { font-size: 44px; }
        .partner-hero-lede { font-size: 15px; }
        .partner-hero-actions { align-items: flex-start; flex-direction: column; gap: 17px; }
        .partner-button { width: 100%; }
        .partner-art-label { top: 28px; left: 28px; font-size: 8px; }
    }
</style>
