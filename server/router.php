<?php
declare(strict_types=1);
// From the project folder: php -S 127.0.0.1:8080 server/router.php
$allowed = ['Admin/account-create.php', 'Admin/account-edit.php', 'Admin/accounts.php', 'Admin/admin-dashboard.php', 'Admin/enrollment-correct.php', 'Admin/enrollment-requests.php', 'Admin/enrollment-review.php', 'Admin/program-create.php', 'Admin/program-edit.php', 'Admin/programs.php', 'Admin/schedule-create.php', 'Admin/schedule-edit.php', 'Admin/schedules.php', 'Admin/section-create.php', 'Admin/section-edit.php', 'Admin/sections.php', 'Admin/student-create.php', 'Admin/student-edit.php', 'Admin/students.php', 'Admin/subject-create.php', 'Admin/subject-edit.php', 'Admin/subjects.php', 'assets/app.js', 'assets/favicon.svg', 'index.php', 'logout.php', 'main.css', 'pages/account-settings.php', 'pages/enrollment-record.php', 'student/enroll.php', 'student/my-enrollments.php', 'student/my-profile.php', 'student/student-dashboard.php'];
$projectRoot = realpath(dirname(__DIR__));
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$base = '/';
if ($projectRoot !== $documentRoot) {
    if (!str_starts_with($projectRoot, $documentRoot . DIRECTORY_SEPARATOR)) {
        http_response_code(500); exit('Invalid document root.');
    }
    $base .= str_replace(DIRECTORY_SEPARATOR, '/', substr($projectRoot, strlen($documentRoot) + 1)) . '/';
}
if ($path === $base) { return false; }
if (str_starts_with($path, $base) && in_array(substr($path, strlen($base)), $allowed, true)) {
    return false;
}
http_response_code(403);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Access restricted.';
return true;
