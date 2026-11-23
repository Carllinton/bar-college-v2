<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="color-scheme" content="light" />
        <title><?= e($title) ?> · BAR College</title>
        <link rel="icon" href="<?= e(app_url('assets/favicon.svg')) ?>" type="image/svg+xml" />
        <link rel="stylesheet" href="<?= e(app_url('main.css')) ?>" />
        <script src="<?= e(app_url('assets/app.js')) ?>" defer></script>
    </head>
    <body class="<?= empty($user) ? 'guest' : 'portal' ?>">
        <a class="skip" href="#main">Skip to content</a>
        <?php if (!empty($user)): ?>
        <aside class="sidebar" id="navigation">
            <a class="brand" href="<?= e(url('dashboard')) ?>">
                <span class="brand-icon">
                    B
                    <span>↗</span>
                </span>
                <span>
                    BAR College
                    <small>WINBAR CAMPUS PORTAL</small>
                </span>
            </a>
            <div class="nav-label">YOUR WORKSPACE</div>
            <nav aria-label="Main navigation">
                <?php
                $nav = ['dashboard' => ['Overview', '◫']];
                if ($user['user_type'] === 'admin') {
                    foreach (BarCollege\Repositories\Catalog::resources() as $key => $item) {
                        $nav['records:' . $key] = [$item['title'], '◇'];
                    }
                } else {
                    $nav['profile'] = ['My profile', '◉'];
                    $nav['enroll'] = ['Choose subjects', '▤'];
                }
                $nav['enrollments'] = [$user['user_type'] === 'admin' ? 'Enrollment requests' : 'My enrollment', '↗'];
                $nav['settings'] = ['Account settings', '⚙'];
                $currentFile = basename($_SERVER['SCRIPT_NAME']);
                foreach ($nav as $key => [$label, $icon]):
                    $bits = explode(':', $key);
                    $target = url($bits[0], isset($bits[1]) ? ['resource' => $bits[1]] : []);
                    $active = $currentFile === basename(parse_url($target, PHP_URL_PATH));
                    if (isset($bits[1])) {
                        $prefix = resource_file($bits[1]);
                        $active = $active || in_array($currentFile, [$prefix . '-create.php', $prefix . '-edit.php'], true);
                    }
                    if ($key === 'enrollments' && in_array($currentFile, ['enrollment-review.php', 'enrollment-correct.php', 'enrollment-record.php'], true)) {
                        $active = true;
                    }
                ?>
                <a
                    class="nav-link <?= $active ? 'active' : '' ?>"
                    <?= $active ? 'aria-current="page"' : '' ?>
                    href="<?= e($target) ?>"
                >
                    <span aria-hidden="true"><?= e($icon) ?></span>
                    <?= e($label) ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-note">
                <span class="tiny-label">BINARY APPLICATION RESEARCH</span>
                <p>
                    Small steps.
                    <br />
                    Brighter futures.
                </p>
                <span class="note-mark" aria-hidden="true">✳</span>
            </div>
            <div class="sidebar-user">
                <span class="avatar"><?= e(mb_substr($user['fullname'], 0, 1)) ?></span>
                <div>
                    <strong><?= e($user['fullname']) ?></strong>
                    <small><?= e(ucfirst($user['user_type'])) ?></small>
                </div>
            </div>
            <form method="post" action="<?= e(url('logout')) ?>" class="logout">
                <?= csrf() ?>
                <button class="text-button" type="submit">
                    Sign out
                    <span aria-hidden="true">↗</span>
                </button>
            </form>
        </aside>
        <div class="workspace">
            <header class="topbar">
                <button
                    class="mobile-toggle"
                    aria-controls="navigation"
                    aria-expanded="false"
                    type="button"
                >
                    Menu
                </button>
                <span>
                    Campus /
                    <strong><?= e($title) ?></strong>
                </span>
                <span class="topbar-right">
                    <span class="live-dot"></span>
                    BAR College
                    <span class="role-label"><?= e(ucfirst($user['user_type'])) ?> portal</span>
                </span>
            </header>
            <?php endif; ?>
            <main id="main" class="<?= !empty($user) ? 'main-content' : 'guest-content' ?>">
                <?php if (isset($_SESSION['flash'])): [$message, $type] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                <div class="flash <?= e($type) ?>" role="alert"><?= e($message) ?></div>
                <?php endif; ?> <?= $content ?>
            </main>
            <?php if (!empty($user)): ?>
            <footer class="footer">
                <span>© <?= e(date('Y')) ?> BAR College</span>
                <span>
                    Crafted by WinBAR
                    <span aria-hidden="true">↗</span>
                </span>
            </footer>
        </div>
        <?php endif; ?>
    </body>
</html>
