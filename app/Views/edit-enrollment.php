<a class="back-link" href="<?= e(url('enrollment', ['id' => $row['ID']])) ?>">← Back to request</a>
<div class="page-heading">
    <div>
        <span class="eyebrow">REGISTRAR REVIEW</span>
        <h1>Correct pending enrollment.</h1>
        <p class="muted">Request #<?= e($row['ID']) ?>. Select the student and subjects; units are recalculated.</p>
    </div>
</div>
<div class="notice">
    Review duplicate records before correcting this one. A student can have only one enrollment in
    this database. Select subjects from the student's program.
</div>
<form method="post" action="<?= e(url('revise', ['id' => $row['ID']])) ?>" class="card editor">
    <?= csrf() ?>
    <div class="field">
        <label for="profile-id">Student *</label>
        <select name="profile_id" id="profile-id" required>
            <option value="">Choose student</option>
            <?php foreach ($profiles as $profile): ?>
                <?php $isSelected = $selectedProfile !== null
                    ? (string) $profile['ID'] === $selectedProfile
                    : $profile['studnum'] === $row['studentID']; ?>
                <option value="<?= e($profile['ID']) ?>" <?= $isSelected ? 'selected' : '' ?>>
                    <?= e($profile['studnum'] . ' · ' . $profile['fname'] . ' ' . $profile['lname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <fieldset class="subject-options">
        <legend>Subjects *</legend>
        <?php foreach ($subjects as $subject): ?>
            <?php $isSelected = $selectedSubjects !== null
                ? in_array((string) $subject['ID'], $selectedSubjects, true)
                : in_array($subject['subjectcode'], codes($row['subjects']), true); ?>
            <label>
                <input type="checkbox" name="subjects[]" value="<?= e($subject['ID']) ?>" <?= $isSelected ? 'checked' : '' ?> />
                <span>
                    <?= e($subject['subjectcode'] . ' · ' . $subject['subjecttitle']) ?>
                    <small class="cell-sub"><?= e(strtoupper($subject['course_con'])) ?></small>
                </span>
            </label>
        <?php endforeach; ?>
    </fieldset>
    <div class="form-actions">
        <a class="button secondary" href="<?= e(url('enrollments')) ?>">Cancel</a>
        <button class="button" type="submit">Save correction ↗</button>
    </div>
</form>
