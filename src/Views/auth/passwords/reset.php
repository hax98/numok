<?php $authAside = false; require ROOT_PATH . '/src/Views/partner/auth/shell-start.php'; ?>
<h1 class="rp-heading-m">Choose a new password</h1>
<p class="text-text-secondary mt-2">Pick a password you don't use anywhere else.</p>

<div class="mt-8 grid gap-4">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="rp-alert rp-alert-danger" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form class="grid gap-5" action="/password/reset" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="rp-field">
            <label for="email">Email address</label>
            <input class="rp-input bg-surface-subtle" id="email" name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars($email ?? '') ?>" readonly>
        </div>
        <div class="rp-field">
            <label for="password">New password</label>
            <input class="rp-input" id="password" name="password" type="password" autocomplete="new-password" required autofocus>
        </div>
        <div class="rp-field">
            <label for="password_confirmation">Confirm new password</label>
            <input class="rp-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button type="submit" class="rp-btn rp-btn-l w-full">Save new password</button>
    </form>
</div>
<?php require ROOT_PATH . '/src/Views/partner/auth/shell-end.php'; ?>
