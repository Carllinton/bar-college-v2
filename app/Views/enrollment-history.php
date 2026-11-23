<div class="page-heading"><div><span class="eyebrow">ACADEMICS, IN ONE PLACE</span><h1><?= e($title) ?>.</h1><p class="muted"><?= $user['user_type'] === 'admin' ? 'Open each request to inspect its subjects and approve it.' : 'Follow the status of your submitted subjects.' ?></p></div><?php if ($user['user_type'] === 'student'): ?><a class="button" href="<?= e(url('enroll')) ?>">Choose subjects ↗</a><?php endif; ?></div>
<section class="card enrollment-history">
    <div class="card-heading">
        <div>
            <h2><?= $user['user_type'] === 'admin' ? 'Requests directory' : 'My requests' ?></h2>
            <p class="muted"><?= $user['user_type'] === 'admin' ? 'Showing up to 250 latest records. Open a record to review its subject details.' : 'A submitted request stays pending until approved.' ?></p>
        </div>
        <span class="count-pill"><?= e(count($rows)) ?></span>
    </div>
    <?php if (!$rows): ?>
    <div class="empty">
        <span aria-hidden="true">↗</span>
        <h3>No requests yet.</h3>
        <p>Submitted enrollments will appear here.</p>
    </div>
    <?php else: ?>
    <div class="table-scroll">
        <table>
            <caption class="sr-only">Enrollment requests</caption>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Student number</th>
                    <th>Subjects</th>
                    <th>Units</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>#<?= e($row['ID']) ?></td>
                    <td><?= e($row['studentID'] ?: 'Missing student') ?></td>
                    <td class="code-list"><?= e(implode(', ', codes($row['subjects']))) ?></td>
                    <td><?= e($row['totalunits']) ?></td>
                    <td><span class="badge <?= $row['approved'] === '1' ? 'approved' : 'pending' ?>"><?= e(status_label($row['approved'])) ?></span></td>
                    <td class="actions">
                        <a class="text-link" href="<?= e(url('enrollment', ['id' => $row['ID']])) ?>">Review</a>
                        <?php if ($user['user_type'] === 'student' && $row['approved'] === '0'): ?>
                        <form method="post" action="<?= e(url('withdraw')) ?>" data-confirm="Withdraw this pending request? You can submit another selection afterward.">
                            <?= csrf() ?><input type="hidden" name="id" value="<?= e($row['ID']) ?>" />
                            <button type="submit" class="text-button danger">Withdraw</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<p class="form-note">
    The original database does not store semesters or school years. Records shown here are not
    grouped by term. Existing duplicate records are preserved for registrar review.
</p>
