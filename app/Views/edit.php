<a class="back-link" href="<?= e(url('records', ['resource' => $resource])) ?>">← Back to <?= e(strtolower($meta['title'])) ?></a>
<div class="page-heading">
    <div>
        <span class="eyebrow">KEEP THE DETAILS IN ORDER</span>
        <h1>
            <?= e($title) ?>
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted">Fields marked with an asterisk (*) are required.</p>
    </div>
</div>
<?php if ($resource === 'students'): ?>
<div class="notice">
    Create a student account in Accounts before adding a profile. The original database allows only
    14 characters for email; leave it blank if the address does not fit.
</div>
<?php endif; ?> <?php if ($resource === 'accounts'): ?>
<div class="notice">
    Classroom compatibility mode: the fixed database stores short passwords as plain text. Use
    fictional accounts and unique test passwords only. <?= $id ? 'Leave the password blank to keep it unchanged.' : 'New passwords must be 8 to 11 characters.' ?>
</div>
<?php endif; ?> <?php if ($resource === 'schedules'): ?>
<div class="notice">
    This table has no day field and no instructor or room directory. Enter reference IDs supplied by
    your registrar. Conflict detection is unavailable with these records.
</div>
<?php endif; ?>
<form method="post" action="<?= e(url('save', ['resource' => $resource, 'id' => $id])) ?>" class="card editor">
    <?= csrf() ?>
    <input type="hidden" name="resource" value="<?= e($resource) ?>" />
    <input type="hidden" name="id" value="<?= e($id) ?>" />
    <div class="form-grid">
        <?php foreach ($meta['fields'] as $name => $field): [$label, $max] = $field; $required = (bool) ($field[2] ?? false); $type = $field[3] ?? 'text'; $value = $type === 'password' ? '' : ($row[$name] ?? ($type === 'bool' ? '0' : '')); if ($type === 'password' && $id) { $required = false; } ?>
        <div class="field <?= $type === 'textarea' || $name === 'address' ? 'field-wide' : '' ?>">
            <label for="field-<?= e($name) ?>"><?= e($label) ?><?= $required ? ' *' : '' ?></label>
            <?php if (isset($options[$type])): ?>
            <select id="field-<?= e($name) ?>" name="<?= e($name) ?>" <?= $required ? 'required' : '' ?>>
                <?php if (!in_array($type, ['role', 'bool'], true)): ?>
                <option value="">Choose <?= e(strtolower($label)) ?></option>
                <?php endif; ?><?php foreach ($options[$type] as $option): ?>
                <option value="<?= e($option['value']) ?>" <?= (string) $value === (string) $option['value'] ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php elseif ($type === 'textarea'): ?>
            <textarea
                id="field-<?= e($name) ?>"
                name="<?= e($name) ?>"
                maxlength="<?= e($max) ?>"
                rows="4"
                <?= $required ? 'required' : '' ?>
            >
<?= e($value) ?></textarea>
            <?php else: ?>
            <input
                id="field-<?= e($name) ?>"
                type="<?= e(in_array($type, ['password', 'date', 'time', 'number', 'units'], true) ? ($type === 'units' ? 'number' : $type) : 'text') ?>"
                name="<?= e($name) ?>"
                value="<?= e($value) ?>"
                maxlength="<?= e($max) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $type === 'password' ? 'autocomplete="new-password" minlength="8"' : '' ?>
                <?= in_array($type, ['number', 'units'], true) ? 'min="0" step="1" max="2147483647"' : '' ?>
            />
            <?php endif; ?> <?php if ($name === 'emailaddress'): ?>
            <small class="muted">14 characters maximum (original database limit).</small>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="form-actions">
        <a class="button secondary" href="<?= e(url('records', ['resource' => $resource])) ?>">Cancel</a>
        <button class="button" type="submit">Save <?= e($meta['singular']) ?> ↗</button>
    </div>
</form>
