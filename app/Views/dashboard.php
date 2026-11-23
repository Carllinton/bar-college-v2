<div class="page-heading">
    <div>
        <span class="eyebrow">THE BIG PICTURE</span>
        <h1>
            Your campus at a glance
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted">Welcome back, <?= e($user['fullname']) ?>. Let’s keep things moving.</p>
    </div>
    <a class="button" href="<?= e(url('edit', ['resource' => 'students'])) ?>">
        Add student
        <span aria-hidden="true">＋</span>
    </a>
</div>
<section class="welcome-banner">
    <div>
        <span class="eyebrow light">BUILT FOR YOUR NEXT CHAPTER</span>
        <h2>
            More connection.
            <br />
            Less administration.
        </h2>
        <p>Keep records organized and help every student move forward.</p>
        <a href="<?= e(url('enrollments')) ?>" class="light-link">
            Review enrollment requests
            <span aria-hidden="true">↗</span>
        </a>
    </div>
    <div class="banner-graphic" aria-hidden="true">
        <div class="graphic-square">
            B
            <span>↗</span>
        </div>
        <span class="graphic-star">✳</span>
        <span class="graphic-label">LEARN · BUILD · BECOME</span>
    </div>
</section>
<div class="section-heading">
    <h2>Campus snapshot</h2>
    <span class="muted">Live from your records</span>
</div>
<section class="stats" aria-label="Campus statistics">
    <?php $i = 0; foreach ($stats as $label => $number): ?>
    <article class="stat">
        <div class="stat-top">
            <span><?= e($label) ?></span>
            <span class="stat-icon" aria-hidden="true"><?= ['◉', '▤', '◇', '↗'][$i++] ?></span>
        </div>
        <strong><?= e(number_format($number)) ?></strong>
        <small><?= $label === 'Pending requests' ? 'Awaiting registrar review' : 'Records in the directory' ?></small>
    </article>
    <?php endforeach; ?>
</section>
<div class="dashboard-bottom">
    <section class="card">
        <div class="card-heading">
            <div>
                <h2>Latest enrollment requests</h2>
                <p class="muted">Most recently added records</p>
            </div>
            <a href="<?= e(url('enrollments')) ?>" class="text-link">View all ↗</a>
        </div>
        <?php if (!$recent): ?>
        <div class="empty">
            <span aria-hidden="true">↗</span>
            <h3>A fresh start.</h3>
            <p>Enrollment requests will appear here when students submit them.</p>
        </div>
        <?php else: ?>
        <div class="table-scroll">
            <table>
                <caption class="sr-only">Latest enrollment requests</caption>
                <thead>
                    <tr>
                        <th>Student number</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th><span class="sr-only">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                    <tr>
                        <td>
                            <strong><?= e($row['studentID'] ?: 'Missing student') ?></strong>
                            <small class="cell-sub">Request #<?= e($row['ID']) ?></small>
                        </td>
                        <td><?= e($row['totalunits']) ?></td>
                        <td><span class="badge <?= $row['approved'] === '1' ? 'approved' : 'pending' ?>"><?= e(status_label($row['approved'])) ?></span></td>
                        <td><a class="text-link" href="<?= e(url('enrollment', ['id' => $row['ID']])) ?>">Open ↗</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <aside class="card quick-start">
        <span class="eyebrow">A LITTLE DIRECTION</span>
        <h2>
            Ready for
            <br />
            the next step?
        </h2>
        <p class="muted">Create a student account first, then link it to a student profile.</p>
        <a class="quick-link" href="<?= e(url('edit', ['resource' => 'accounts'])) ?>">
            <span>Create an account</span>
            <span>01 ↗</span>
        </a>
        <a class="quick-link" href="<?= e(url('edit', ['resource' => 'students'])) ?>">
            <span>Add a student profile</span>
            <span>02 ↗</span>
        </a>
        <a class="quick-link" href="<?= e(url('records', ['resource' => 'subjects'])) ?>">
            <span>Explore the curriculum</span>
            <span>03 ↗</span>
        </a>
    </aside>
</div>
