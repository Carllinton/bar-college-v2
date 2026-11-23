<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$id = query_id();
$row = $app->db->one("SELECT * FROM student_subject WHERE ID = ?", [$id]);
if (!$row) {
    $app->abort(404, "Record not found", "This enrollment request is no longer available.");
}
if ($app->isPost()) {
    $app->attempt(function () use ($app, $id): void {
        switch ($_POST["action"] ?? "") {
            case "approve":
                $app->enrollment->approve($id);
                flash("Enrollment approved.");
                break;
            case "remove":
                $app->enrollment->removePending($id);
                flash("Pending enrollment removed.");
                break;
            default:
                throw new InvalidArgumentException("Choose a valid registrar action.");
        }
    });
    redirect("enrollments");
}
$details = $app->enrollmentDetails($row);
view("enrollment", $details + compact("user", "row") + ["title" => "Review enrollment"]);
