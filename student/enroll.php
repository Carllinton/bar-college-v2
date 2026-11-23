<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("student");
if ($app->isPost()) {
    $saved = $app->attempt(function () use ($app, $user): void {
        if (!is_array($_POST["subjects"] ?? null)) {
            throw new InvalidArgumentException("Select at least one subject.");
        }
        $app->enrollment->submit((int) $user["ID"], $_POST["subjects"]);
    });
    if ($saved) {
        flash("Your request is ready for registrar review.");
        redirect("enrollments");
    }
}
$profile = $app->enrollment->profile((int) $user["ID"]);
$rows = $profile
    ? $app->db->all("SELECT * FROM student_subject WHERE studentID = ? ORDER BY ID DESC", [
        $profile["studnum"],
    ])
    : [];
$available = $profile
    ? $app->db->all("SELECT * FROM subjecttbl WHERE course_con = ? ORDER BY subjectcode", [
        $profile["course"],
    ])
    : [];
$selected = is_array($_POST["subjects"] ?? null)
    ? array_filter($_POST["subjects"], "is_string")
    : [];
view(
    "enroll",
    compact("user", "profile", "rows", "available", "selected") + ["title" => "Choose subjects"],
);
