<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("student");
if ($app->isPost()) {
    $app->attempt(function () use ($app, $user): void {
        $app->enrollment->withdraw((int) $user["ID"], posted_id());
        flash("Pending request withdrawn.");
    });
    redirect("enrollments");
}
$profile = $app->enrollment->profile((int) $user["ID"]);
$rows = $profile
    ? $app->db->all("SELECT * FROM student_subject WHERE studentID = ? ORDER BY ID DESC", [
        $profile["studnum"],
    ])
    : [];
view("enrollment-history", compact("user", "profile", "rows") + ["title" => "My enrollment"]);
