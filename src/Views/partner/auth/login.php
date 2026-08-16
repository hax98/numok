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
            <p class="partner-auth-eyebrow">WELCOME BACK</p>
            <h1>Pick up where your content left off.</h1>
            <p class="partner-auth-lede">Sign in to your Repostit partner account to check referrals, recurring commission, and your personal link.</p>

            <?php if (!empty($error)): ?>
                <div class="partner-auth-alert" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="partner-auth-alert success" role="status"><?= htmlspecialchars($_SESSION['register_success']) ?></div>
            <?php unset($_SESSION['register_success']); endif; ?>

            <form class="partner-auth-form" action="/auth/login" method="POST">
                <div class="partner-auth-field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required autofocus placeholder="you@example.com"
                        <?php if ($_SERVER['HTTP_HOST']=='demo.numok.com') { echo ' value="partner@numok.com"'; } ?> />
                </div>
                <div class="partner-auth-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Your password"
                        <?php if ($_SERVER['HTTP_HOST']=='demo.numok.com') { echo ' value="partner123"'; } ?> />
                </div>
                <div class="partner-auth-row">
                    <label class="partner-auth-check"><input id="remember-me" name="remember-me" type="checkbox" /> Remember me</label>
                    <a class="partner-auth-forgot" href="/forgot-password">Forgot password?</a>
                </div>
                <button class="partner-auth-submit" type="submit">Sign in to Repostit Partners</button>
            </form>

            <p class="partner-auth-secondary">New to the program? <a href="/register">Create a free partner account</a></p>
        </div>
        <p class="partner-auth-footnote">Free to join · 20% recurring commission · Clear reporting</p>
    </section>

    <aside class="partner-auth-visual" aria-label="Repostit partner program overview">
        <div class="partner-auth-visual-copy">
            <p class="partner-auth-visual-kicker">REPOSTIT PARTNER PROGRAM</p>
            <h2>Your content can travel <em>further.</em></h2>
        </div>
        <div class="partner-auth-art"><img src="/assets/images/partner-workflow.png" alt="One creator video flowing into multiple content formats" /></div>
        <div class="partner-auth-visual-footer"><span>One useful recommendation.</span><strong>20% recurring.</strong></div>
    </aside>
</div>
