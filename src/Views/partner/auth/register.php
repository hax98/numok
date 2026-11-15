<?php require __DIR__ . '/shell-start.php'; ?>
<p class="rp-eyebrow">Join the partner program</p>
<h1 class="rp-heading-m mt-2">Create your partner account</h1>
<p class="text-text-secondary mt-2">It's free. You get your referral link and a dashboard for clicks, referrals and earnings.</p>

<div class="mt-6 grid gap-4">
    <p class="rp-small text-text-secondary bg-surface-subtle rounded-md p-4 m-0">This account is only for the partner program. Posting uses your Repostit app account: <a class="rp-link" href="<?= htmlspecialchars(\Numok\Services\PortalEnvironment::appUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">open Repostit</a> to log in or start a free trial.</p>

    <?php if (isset($_SESSION['register_error'])): ?>
        <div class="rp-alert rp-alert-danger" role="alert"><?= htmlspecialchars($_SESSION['register_error']) ?></div>
    <?php unset($_SESSION['register_error']); endif; ?>

    <form class="grid gap-5 mt-2" action="/auth/register" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\Numok\Services\PortalSecurity::csrf(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="rp-field">
            <label for="company_name">Creator or business name</label>
            <input class="rp-input" id="company_name" name="company_name" type="text" autocomplete="organization" required autofocus placeholder="How your audience knows you" />
        </div>
        <div class="rp-field">
            <label for="contact_name">Your name</label>
            <input class="rp-input" id="contact_name" name="contact_name" type="text" autocomplete="name" required />
        </div>
        <div class="rp-field">
            <label for="email">Email address</label>
            <input class="rp-input" id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com" />
        </div>
        <div class="rp-field">
            <label for="password">Password</label>
            <input class="rp-input" id="password" name="password" type="password" autocomplete="new-password" required minlength="8" aria-describedby="password-hint" />
            <p id="password-hint" class="rp-caption text-text-muted m-0">At least 8 characters</p>
        </div>
        <?php if ($program): ?>
            <details class="rounded-md border border-line">
                <summary class="cursor-pointer px-4 py-3 font-medium">Read the program terms</summary>
                <div class="max-h-56 overflow-auto whitespace-pre-wrap px-4 pb-4 rp-small text-text-secondary"><?= htmlspecialchars(($program['terms'] ?? '') . "\n\n" . \Numok\Services\CreatorRules::BONUS_TERMS, ENT_QUOTES, 'UTF-8') ?></div>
            </details>
            <label class="rp-check"><input type="checkbox" name="terms_accepted" value="1" required> I accept the program terms and bonus conditions.</label>
        <?php endif; ?>
        <button class="rp-btn rp-btn-l w-full" type="submit">Create account</button>
    </form>

    <p class="text-text-secondary text-center mt-4 m-0">Already a partner? <a class="rp-link" href="/login">Log in</a></p>
</div>
<?php require __DIR__ . '/shell-end.php'; ?>
