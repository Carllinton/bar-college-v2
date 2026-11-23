<div class="page-heading">
    <div>
        <span class="eyebrow">YOUR ACCOUNT</span>
        <h1>
            A little housekeeping
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted">Update the password for <?= e($user['username']) ?>.</p>
    </div>
</div>
<div class="notice">
    This classroom system retains the original database’s plain-text password field and 11-character
    limit. Use a unique test password. This authentication design is unsuitable for public
    deployment.
</div>
<form method="post" action="<?= e(url('password')) ?>" class="card editor narrow">
    <?= csrf() ?>
    <h2>Change password</h2>
    <div class="stack-form">
        <label for="current-password">Current password</label>
        <input
            id="current-password"
            type="password"
            name="current_password"
            autocomplete="current-password"
            required
            maxlength="11"
        />
        <label for="new-password">New password</label>
        <input
            id="new-password"
            type="password"
            name="password"
            autocomplete="new-password"
            minlength="8"
            maxlength="11"
            required
            aria-describedby="password-hint"
        />
        <small class="muted" id="password-hint">
            8 to 11 characters. Do not reuse a personal password.
        </small>
        <label for="confirm-password">Confirm new password</label>
        <input
            id="confirm-password"
            type="password"
            name="confirmation"
            autocomplete="new-password"
            minlength="8"
            maxlength="11"
            required
        />
        <button class="button" type="submit">Update password ↗</button>
    </div>
</form>
