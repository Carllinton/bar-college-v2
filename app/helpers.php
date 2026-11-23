<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function resource_file(string $resource): string
{
    return match ($resource) {
        "accounts" => "account",
        "students" => "student",
        "subjects" => "subject",
        "courses" => "program",
        "sections" => "section",
        "schedules" => "schedule",
        default => throw new InvalidArgumentException("Unknown resource."),
    };
}

/** Build a project-relative URL that works at the web root and in an XAMPP subfolder. */
function app_url(string $path, array $params = []): string
{
    $root = realpath(dirname(__DIR__));
    $scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (!$root || !$scriptFile || !str_starts_with($scriptFile, $root . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Cannot resolve the application URL.');
    }
    $relativeScript = str_replace('\\', '/', substr($scriptFile, strlen($root) + 1));
    if (!str_ends_with($scriptName, $relativeScript)) {
        throw new RuntimeException('Unexpected script URL.');
    }
    $prefix = substr($scriptName, 0, -strlen($relativeScript));
    $prefix = '/' . (trim($prefix, '/') !== '' ? trim($prefix, '/') . '/' : '');
    return $prefix . $path . ($params ? '?' . http_build_query($params) : '');
}

// Build links to actual pages in their role-specific folders; this does not dispatch requests.
function url(string $page, array $params = []): string
{
    $isAdmin = ($GLOBALS["current_user"]["user_type"] ?? "") === "admin";
    $resource = (string) ($params["resource"] ?? "");
    $id = (int) ($params["id"] ?? 0);
    $file = match ($page) {
        "login" => "index.php",
        "dashboard" => $isAdmin ? "Admin/admin-dashboard.php" : "student/student-dashboard.php",
        "records", "delete" => "Admin/" . resource_file($resource) . "s.php",
        "edit", "save" => "Admin/" . resource_file($resource) . ($id ? "-edit.php" : "-create.php"),
        "enrollments" => $isAdmin ? "Admin/enrollment-requests.php" : "student/my-enrollments.php",
        "enrollment" => $isAdmin ? "Admin/enrollment-review.php" : "pages/enrollment-record.php",
        "edit-enrollment", "revise" => "Admin/enrollment-correct.php",
        "approve", "remove-pending" => "Admin/enrollment-review.php",
        "withdraw" => "student/my-enrollments.php",
        "submit", "enroll" => "student/enroll.php",
        "profile" => "student/my-profile.php",
        "settings", "password" => "pages/account-settings.php",
        "logout" => "logout.php",
        default => throw new InvalidArgumentException("Unknown page."),
    };
    unset($params["resource"]);
    if (!$id) {
        unset($params["id"]);
    }
    return app_url($file, $params);
}

function redirect(string $page, array $params = []): never
{
    header("Location: " . url($page, $params), true, 303);
    exit();
}

function csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION["csrf"]) . '">';
}

function flash(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = [$message, $type];
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . "/Views/" . $template . ".php";
    $content = ob_get_clean();
    require __DIR__ . "/Views/layout.php";
}

function codes(string $value): array
{
    return array_values(
        array_filter(array_map("trim", explode(",", $value)), static fn($v) => $v !== ""),
    );
}

function status_label(mixed $value): string
{
    return match ((string) $value) {
        "0" => "Pending",
        "1" => "Approved",
        default => "Needs review",
    };
}

function query_id(): int
{
    $value = $_GET["id"] ?? null;
    if (
        !is_string($value) ||
        !ctype_digit($value) ||
        (int) $value < 1 ||
        (int) $value > 2147483647
    ) {
        throw new InvalidArgumentException("A valid record ID is required.");
    }
    return (int) $value;
}

function posted_id(): int
{
    $value = $_POST["id"] ?? null;
    if (
        !is_string($value) ||
        !ctype_digit($value) ||
        (int) $value < 1 ||
        (int) $value > 2147483647
    ) {
        throw new InvalidArgumentException("A valid record ID is required.");
    }
    return (int) $value;
}
