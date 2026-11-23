<div class="page-heading">
    <div>
        <span class="eyebrow">YOUR SPACE TO MOVE FORWARD</span>
        <h1>
            Hello, <?= e($profile['fname'] ?? $user['fullname']) ?>
            <span class="heading-dot">.</span>
        </h1>
        <p class="muted">Your academic journey, a little more connected.</p>
    </div>
    <a class="button" href="<?= e(url('enroll')) ?>">Choose subjects ↗</a>
</div>
<section class="welcome-banner">
    <div>
        <span class="eyebrow light">WELCOME TO BAR COLLEGE</span>
        <h2>
            Your next chapter
            <br />
            starts with you.
        </h2>
        <p>Explore your subjects, submit your enrollment, and stay on track.</p>
        <a class="light-link" href="<?= e(url('profile')) ?>">View your student profile ↗</a>
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
<?php if (!$profile): ?>
<div class="notice">
    Your account is ready. Ask the registrar to link your student profile before enrolling.
</div>
<?php else: ?>
<section class="stats">
    <article class="stat">
        <span>Student number</span>
        <strong class="stat-text"><?= e($profile['studnum']) ?></strong>
        <small>Your campus identity</small>
    </article>
    <article class="stat">
        <span>Program</span>
        <strong class="stat-text"><?= e(strtoupper($profile['course'])) ?></strong>
        <small>Binary Application Research College</small>
    </article>
    <article class="stat">
        <span>Year / section</span>
        <strong class="stat-text"><?= e($profile['yearlvl']) ?></strong>
        <small>As recorded by the registrar</small>
    </article>
    <article class="stat">
        <span>Enrollment</span>
        <strong class="stat-text"><?= e($recent ? status_label($recent[0]['approved']) : 'Not submitted') ?></strong>
        <small><?= e(count($recent)) ?> stored records</small>
    </article>
</section>
<?php endif; ?>
<section class="card card-pad">
    <span class="eyebrow">A SIMPLE NEXT STEP</span>
    <h2>Choose your subjects. Start your journey.</h2>
    <p class="muted">
        Review the subjects available for your program, then submit your selection for registrar
        approval.
    </p>
    <a class="text-link" href="<?= e(url('enroll')) ?>">Choose my subjects ↗</a>
</section>
