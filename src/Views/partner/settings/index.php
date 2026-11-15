<div class="py-6">
    <div class="w-full">
        <h1 class="rp-heading-m text-ink">Account settings</h1>
    </div>
    
    <div class="w-full">
        <?php if (isset($_SESSION['settings_success'])): ?>
        <div class="rounded-md bg-green-50 p-4 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['settings_success']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['settings_success']); endif; ?>

        <?php if (isset($_SESSION['settings_error'])): ?>
        <div class="rounded-md bg-red-50 p-4 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($_SESSION['settings_error']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['settings_error']); endif; ?>

        <div class="mt-6 max-w-2xl">
            <div>
                <!-- Profile Settings -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-[21px] font-medium text-ink">Profile and password</h2>
                        <div class="mt-4 max-w-xl">
                            <form action="/settings/update" method="POST">
                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="block text-[15px] font-medium text-ink">Email address</label>
                                    <input type="email" name="email" id="email" 
                                        value="<?= htmlspecialchars($partner['email']) ?>"
                                        class="rp-input mt-1.5">
                                    <p class="mt-1 text-sm text-gray-500">You log in with this email. We also send account updates here.</p>
                                </div>

                                <!-- Payment Email -->
                                <div class="mb-4">
                                    <label for="payment_email" class="block text-[15px] font-medium text-ink">Payment email</label>
                                    <input type="email" name="payment_email" id="payment_email"
                                        value="<?= htmlspecialchars($partner['payment_email']) ?>"
                                        class="rp-input mt-1.5">
                                    <p class="mt-1 text-sm text-gray-500">We contact you here to arrange your payouts.</p>
                                </div>

                                <!-- Current Password -->
                                <div class="mb-4">
                                    <label for="current_password" class="block text-[15px] font-medium text-ink">Current password</label>
                                    <p class="mt-1 text-sm text-gray-500">Needed to save any change.</p>
                                    <input type="password" name="current_password" id="current_password" required
                                           class="rp-input mt-1.5">
                                </div>

                                <!-- New Password -->
                                <div class="mb-4">
                                    <label for="new_password" class="block text-[15px] font-medium text-ink">New password</label>
                                    <input type="password" name="new_password" id="new_password" minlength="8"
                                           class="rp-input mt-1.5">
                                    <p class="mt-1 text-sm text-gray-500">At least 8 characters. Leave blank to keep your current password.</p>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="mb-4">
                                    <label for="confirm_password" class="block text-[15px] font-medium text-ink">Confirm new password</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="rp-input mt-1.5">
                                </div>

                                <div class="mt-6">
                                    <button type="submit" 
                                            class="rp-btn">
                                        Save changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>