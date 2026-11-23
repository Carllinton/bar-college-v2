<div class="page-heading">
    <div>
        <span class="eyebrow">CAMPUS DIRECTORY</span>
        <h1>
            <?= e($meta['title']) ?>
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted"><?= e($meta['description']) ?></p>
    </div>
    <a class="button" href="<?= e(url('edit', ['resource' => $resource])) ?>">Add <?= e($meta['singular']) ?> ＋</a>
</div>
<section class="card">
    <div class="directory-toolbar">
        <div>
            <h2>
                <?= e($meta['title']) ?> directory
                <span class="count-pill"><?= e($count) ?></span>
            </h2>
            <p class="muted">Search, review, and manage your records.</p>
        </div>
        <form method="get" class="search-form" action="<?= e(url('records', ['resource' => $resource])) ?>">
            <label class="sr-only" for="search">Search <?= e($meta['title']) ?></label>
            <input
                id="search"
                name="q"
                value="<?= e($q) ?>"
                maxlength="100"
                placeholder="Search records…"
            />
            <button class="button secondary" type="submit">Search</button>
            <?php if ($q !== ''): ?>
            <a class="text-link" href="<?= e(url('records', ['resource' => $resource])) ?>">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <?php if (!$rows): ?>
    <div class="empty">
        <span aria-hidden="true">◇</span>
        <h3><?= $q === '' ? 'Room for something new.' : 'No matching records.' ?></h3>
        <p><?= $q === '' ? 'Add your first record to get started.' : 'Try another name or code.' ?></p>
    </div>
    <?php else: ?>
    <div class="table-scroll">
        <table>
            <caption class="sr-only"><?= e($meta['title']) ?> directory</caption>
            <thead>
                <tr>
                    <?php foreach ($meta['columns'] as $label): ?>
                    <th scope="col"><?= e($label) ?></th>
                    <?php endforeach; ?>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($meta['columns'] as $key => $label): ?>
                    <td><?= e($row[$key]) ?></td>
                    <?php endforeach; ?>
                    <td class="actions">
                        <a class="text-link" href="<?= e(url('edit', ['resource' => $resource, 'id' => $row[$meta['key']]])) ?>">Edit</a>
                        <form
                            method="post"
                            action="<?= e(url('delete', ['resource' => $resource])) ?>"
                            data-confirm="Delete this <?= e($meta['singular']) ?>? This cannot be undone."
                        >
                            <?= csrf() ?>
                            <input type="hidden" name="id" value="<?= e($row[$meta['key']]) ?>" />
                            <button type="submit" class="text-button danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination" aria-label="Directory pages">
        <span class="muted">
            Page <?= e($page) ?> of <?= e(max(1, ceil($count / 12))) ?> · <?= e($count) ?> records
        </span>
        <div>
            <?php if ($page > 1): ?>
            <a class="button secondary small" href="<?= e(url('records', ['resource' => $resource, 'q' => $q, 'p' => $page - 1])) ?>">← Previous</a>
            <?php endif; ?><?php if ($page * 12 < $count): ?>
            <a class="button secondary small" href="<?= e(url('records', ['resource' => $resource, 'q' => $q, 'p' => $page + 1])) ?>">Next →</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</section>
<?php if ($resource === 'schedules'): ?>
<p class="form-note">
    Schedule IDs refer to the existing section and subject tables. Room and instructor IDs are
    legacy reference numbers; their directories and schedule days are not stored in this table.
</p>
<?php endif; ?>
