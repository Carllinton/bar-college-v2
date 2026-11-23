<div class="page-heading">
    <div>
        <span class="eyebrow">YOUR CAMPUS IDENTITY</span>
        <h1>
            My profile
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted">For record corrections, contact your college registrar.</p>
    </div>
</div>
<?php if (!$profile): ?>
<div class="notice">Your profile has not been linked yet. Contact the registrar.</div>
<?php else: ?>
<section class="card card-pad">
    <div class="profile-heading">
        <span class="profile-avatar"><?= e(mb_substr($profile['fname'], 0, 1) . mb_substr($profile['lname'], 0, 1)) ?></span>
        <div>
            <h2><?= e(trim($profile['fname'] . ' ' . $profile['minitial'] . ' ' . $profile['lname'])) ?></h2>
            <p class="muted"><?= e($profile['studnum']) ?> · <?= e(strtoupper($profile['course'])) ?></p>
        </div>
    </div>
    <dl class="profile-grid">
        <?php foreach (BarCollege\Repositories\Catalog::get('students')['fields'] as $key => $field): if ($key === 'accountID') { continue; } ?>
        <div>
            <dt><?= e($field[0]) ?></dt>
            <dd><?= e($profile[$key] ?: 'Not provided') ?></dd>
        </div>
        <?php endforeach; ?>
    </dl>
</section>
<?php endif; ?>
