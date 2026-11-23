<div class="print-controls">
    <a class="back-link" href="<?= e(url('enrollments')) ?>">← Back to enrollments</a>
    <button class="button secondary" type="button" data-print>Print this record</button>
</div>
<article class="card record-sheet">
    <header class="sheet-header">
        <div class="sheet-brand">
            BAR
            <span>COLLEGE</span>
        </div>
        <div>
            <span class="eyebrow">BINARY APPLICATION RESEARCH</span>
            <h1>Enrollment record</h1>
            <p>Request #<?= e($row['ID']) ?> · <?= e(status_label($row['approved'])) ?></p>
        </div>
    </header>
    <div class="sheet-details">
        <div>
            <small>STUDENT</small>
            <strong><?= e($student ? trim($student['fname'] . ' ' . $student['minitial'] . ' ' . $student['lname']) : 'Missing or ambiguous student record') ?></strong>
        </div>
        <div>
            <small>STUDENT NUMBER</small>
            <strong><?= e($row['studentID'] ?: 'Missing') ?></strong>
        </div>
        <div>
            <small>PROGRAM / YEAR</small>
            <strong><?= e($student ? strtoupper($student['course']) . ' / ' . $student['yearlvl'] : 'Unavailable') ?></strong>
        </div>
    </div>
    <div class="table-scroll">
        <table>
            <caption class="sr-only">Subjects in this enrollment</caption>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Schedule</th>
                    <th>Room</th>
                    <th>Instructor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?= e($subject['subjectcode']) ?></td>
                    <td><?= e($subject['subjecttitle']) ?></td>
                    <td><?= e($subject['numofunits']) ?></td>
                    <td><?= e(trim($subject['day'] . ' ' . $subject['time']) ?: 'TBA') ?></td>
                    <td><?= e($subject['room'] ?: 'TBA') ?></td>
                    <td><?= e($subject['instructor'] ?: 'TBA') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="sheet-total">
        <span>Stored total units</span>
        <strong><?= e($row['totalunits']) ?></strong>
    </div>
    <p class="form-note">
        This is an enrollment request record, not a payment receipt or official certificate of
        registration. Subject details reflect the current catalog. No semester, school year,
        submission date, or payment is stored in the original database.
    </p>
</article>

<?php if ($user['user_type'] === 'admin' && empty($printOnly)): ?>
<section class="card card-pad enrollment-history print-controls">
    <div><h2>Registrar actions</h2><p class="muted">Review the student and subject details before choosing an action.</p></div>
    <div class="actions">
        <a class="button secondary" href="<?= e(app_url('pages/enrollment-record.php', ['id' => $row['ID']])) ?>">Printable record</a>
        <?php if ($row['approved'] === '0'): ?>
        <a class="button secondary" href="<?= e(url('edit-enrollment', ['id' => $row['ID']])) ?>">Correct request</a>
        <form method="post" action="<?= e(url('enrollment', ['id' => $row['ID']])) ?>" data-confirm="Approve this enrollment after reviewing its subjects?">
            <?= csrf() ?><input type="hidden" name="action" value="approve" /><button class="button" type="submit">Approve request</button>
        </form>
        <form method="post" action="<?= e(url('enrollment', ['id' => $row['ID']])) ?>" data-confirm="Permanently remove this pending request? This cannot be undone.">
            <?= csrf() ?><input type="hidden" name="action" value="remove" /><button class="text-button danger" type="submit">Remove pending request</button>
        </form>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
