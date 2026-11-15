<?php $authAside = false; require ROOT_PATH . '/src/Views/partner/auth/shell-start.php'; ?>
<h1 class="rp-heading-m">Reset your password</h1>
<p class="text-text-secondary mt-2">Enter the email you use for Repostit Partners. We'll send you a link to choose a new password.</p>

<div class="mt-8 grid gap-4">
    <?php if (!empty($_SESSION['status'])): ?>
        <div class="rp-alert rp-alert-success" role="status"><?= htmlspecialchars($_SESSION['status']) ?></div>
        <?php unset($_SESSION['status']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="rp-alert rp-alert-danger" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form class="grid gap-5" action="/password/email" method="POST">
        <div class="rp-field">
            <label for="email">Email address</label>
            <input class="rp-input" id="email" name="email" type="email" autocomplete="email" required autofocus placeholder="you@example.com">
        </div>
        <button type="submit" class="rp-btn rp-btn-l w-full">Send reset link</button>
    </form>

    <p class="text-text-secondary text-center mt-4 m-0">Remembered it? <a href="/login" class="rp-link">Log in</a></p>
</div>
<?php require ROOT_PATH . '/src/Views/partner/auth/shell-end.php'; ?>
