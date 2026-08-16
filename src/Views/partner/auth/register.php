<?php require ROOT_PATH . '/src/Views/partner/auth/styles.php'; ?>

<div class="partner-auth-page">
    <section class="partner-auth-form-side">
        <nav class="partner-auth-nav" aria-label="Partner portal navigation">
            <a href="/" class="partner-auth-brand" aria-label="Repostit Partners home">
                <span class="partner-auth-mark"><img src="/assets/favicon/android-chrome-512x512.png" alt="" /></span>
                <span>Repostit <small style="font-family: 'DM Sans', sans-serif; font-size: 9px; letter-spacing: .16em; color: #77717e;">PARTNERS</small></span>
            </a>
            <a class="partner-auth-back" href="/">Back to partner program</a>
        </nav>

        <div class="partner-auth-content">
            <p class="partner-auth-eyebrow">JOIN THE CREW</p>
            <h1>Make your next recommendation count.</h1>
            <p class="partner-auth-lede">Create your free partner account, try Repostit, and share it when it genuinely helps your audience or clients.</p>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="partner-auth-alert" role="alert"><?= htmlspecialchars($_SESSION['register_error']) ?></div>
            <?php unset($_SESSION['register_error']); endif; ?>

            <form class="partner-auth-form" action="/auth/register" method="POST">
                <div class="partner-auth-field">
                    <label for="company_name">Company or creator name</label>
                    <input id="company_name" name="company_name" type="text" autocomplete="organization" required autofocus placeholder="Your name or business" />
                </div>
                <div class="partner-auth-field">
                    <label for="contact_name">Your name</label>
                    <input id="contact_name" name="contact_name" type="text" autocomplete="name" required placeholder="First and last name" />
                </div>
                <div class="partner-auth-field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com" />
                </div>
                <div class="partner-auth-field">
                    <label for="password">Create a password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" placeholder="At least 8 characters" />
                </div>
                <button class="partner-auth-submit" type="submit">Create my free account</button>
            </form>

            <p class="partner-auth-secondary">Already a partner? <a href="/login">Sign in to your account</a></p>
        </div>
        <p class="partner-auth-footnote">Free to join · 20% recurring commission · No upfront fee</p>
    </section>

    <aside class="partner-auth-visual" aria-label="Repostit partner program overview">
        <div class="partner-auth-visual-copy">
            <p class="partner-auth-visual-kicker">REPOSTIT PARTNER PROGRAM</p>
            <h2>Share what you genuinely <em>use.</em></h2>
        </div>
        <div class="partner-auth-art"><img src="/assets/images/partner-workflow.png" alt="One creator video flowing into multiple content formats" /></div>
        <div class="partner-auth-visual-footer"><span>One simple link.</span><strong>Recurring revenue.</strong></div>
    </aside>
</div>
