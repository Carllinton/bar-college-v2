<section class="card error-card">
    <span class="eyebrow">LET’S GET YOU BACK ON TRACK</span>
    <h1><?= e($title) ?></h1>
    <p class="muted"><?= e($message) ?></p>
    <a class="button" href="<?= e(url(empty($user) ? 'login' : 'dashboard')) ?>">Return to portal ↗</a>
</section>
