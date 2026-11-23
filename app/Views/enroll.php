<div class="page-heading"><div><span class="eyebrow">YOUR NEXT CHAPTER</span><h1>Choose your subjects.</h1><p class="muted">Select subjects for your program and submit them for registrar review.</p></div><a class="button secondary" href="<?= e(url('enrollments')) ?>">View my requests ↗</a></div>
<?php if ($user['user_type'] === 'student' && !$profile): ?>
<div class="notice">Ask the registrar to create your student profile before enrolling.</div>
<?php endif; ?> <?php if ($user['user_type'] === 'student' && $profile && !$rows): ?>
<form method="post" action="<?= e(url('submit')) ?>" class="card" id="subject-selection">
    <?= csrf() ?>
    <div class="card-heading">
        <div>
            <h2>Available subjects</h2>
            <p class="muted"><?= e(strtoupper($profile['course'])) ?> · <?= e($profile['yearlvl']) ?></p>
        </div>
        <div class="selection-summary" aria-live="polite">
            <strong id="unit-count">0</strong>
            units selected
        </div>
    </div>
    <?php if (!$available): ?>
    <div class="empty">
        <h3>Your curriculum is on its way.</h3>
        <p>No subjects are available for your program yet. Contact the registrar.</p>
    </div>
    <?php else: ?>
    <div class="table-scroll">
        <table>
            <caption class="sr-only">Select subjects for enrollment</caption>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Day / time</th>
                    <th>Instructor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($available as $subject): ?>
                <tr>
                    <td>
                        <input
                            class="subject-check"
                            id="subject-<?= e($subject['ID']) ?>"
                            name="subjects[]"
                            value="<?= e($subject['ID']) ?>"
                            type="checkbox"
                            <?= in_array((string) $subject['ID'], $selected, true) ? 'checked' : '' ?>
                            data-units="<?= e($subject['numofunits']) ?>"
                            aria-label="Select <?= e($subject['subjectcode']) ?>"
                        />
                    </td>
                    <td>
                        <label for="subject-<?= e($subject['ID']) ?>">
                            <strong><?= e($subject['subjectcode']) ?></strong>
                            <small class="cell-sub"><?= e($subject['subjecttitle']) ?></small>
                        </label>
                    </td>
                    <td><?= e($subject['numofunits']) ?></td>
                    <td><?= e(trim($subject['day'] . ' ' . $subject['time']) ?: 'To be announced') ?></td>
                    <td><?= e($subject['instructor'] ?: 'To be announced') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="form-actions">
        <span class="muted">Review your selection before submitting.</span>
        <button class="button" type="submit">Submit for review ↗</button>
    </div>
    <?php endif; ?>
</form>
<?php endif; ?>

<?php if ($rows): ?>
<section class="card card-pad"><h2>You already have an enrollment record.</h2><p class="muted">View your requests to check the status. You may withdraw a pending request before submitting another selection.</p><a class="button" href="<?= e(url('enrollments')) ?>">Open my enrollment ↗</a></section>
<?php endif; ?>
