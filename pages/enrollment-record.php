<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user();
$app->allowMethods(["GET"]);
$id = query_id();
$row = $app->db->one("SELECT * FROM student_subject WHERE ID = ?", [$id]);
$profile = $user["user_type"] === "student" ? $app->enrollment->profile((int) $user["ID"]) : null;
if (
    !$row ||
    ($user["user_type"] === "student" && (!$profile || $profile["studnum"] !== $row["studentID"]))
) {
    $app->abort(404, "Record not found", "This enrollment record is unavailable.");
}
$details = $app->enrollmentDetails($row);
$printOnly = true;
view(
    "enrollment",
    $details + compact("user", "row", "printOnly") + ["title" => "Enrollment record"],
);
