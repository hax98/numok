<?php require __DIR__ . '/shell-start.php'; ?>
<p class="rp-eyebrow">Partner portal</p>
<h1 class="rp-heading-m mt-2">Log in to Repostit Partners</h1>
<p class="text-text-secondary mt-2">See your referrals, commission and referral link.</p>

<div class="mt-8 grid gap-4">
    <?php if (!empty($error)): ?>
        <div class="rp-alert rp-alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['register_success'])): ?>
        <div class="rp-alert rp-alert-success" role="status"><?= htmlspecialchars($_SESSION['register_success']) ?></div>
    <?php unset($_SESSION['register_success']); endif; ?>

    <form class="grid gap-5" action="/auth/login" method="POST">
        <div class="rp-field">
            <label for="email">Email address</label>
            <input class="rp-input" id="email" name="email" type="email" autocomplete="email" required autofocus placeholder="you@example.com"
                <?php if (($_SERVER['HTTP_HOST'] ?? '') == 'demo.numok.com') { echo ' value="partner@numok.com"'; } ?> />
        </div>
        <div class="rp-field">
            <div class="flex items-baseline justify-between gap-4">
                <label class="rp-field-label" for="password">Password</label>
                <a class="rp-link text-[15px]" href="/forgot-password">Forgot password?</a>
            </div>
            <input class="rp-input" id="password" name="password" type="password" autocomplete="current-password" required
                <?php if (($_SERVER['HTTP_HOST'] ?? '') == 'demo.numok.com') { echo ' value="partner123"'; } ?> />
        </div>
        <label class="rp-check"><input id="remember-me" name="remember-me" type="checkbox" /> Keep me logged in</label>
        <button class="rp-btn rp-btn-l w-full" type="submit">Log in</button>
    </form>

    <p class="text-text-secondary text-center mt-4 m-0">New to the program? <a class="rp-link" href="/register">Create a free partner account</a></p>
</div>
<?php require __DIR__ . '/shell-end.php'; ?>
