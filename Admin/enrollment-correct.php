<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$id = query_id();
$row = $app->db->one("SELECT * FROM student_subject WHERE ID = ? AND approved = '0'", [$id]);
if (!$row) {
    $app->abort(
        404,
        "Pending request not found",
        "Only pending enrollment requests can be corrected.",
    );
}
if ($app->isPost()) {
    $saved = $app->attempt(function () use ($app, $id): void {
        if (!is_array($_POST["subjects"] ?? null)) {
            throw new InvalidArgumentException("Choose at least one subject.");
        }
        $profileId = $_POST["profile_id"] ?? "";
        if (!is_string($profileId) || !ctype_digit($profileId)) {
            throw new InvalidArgumentException("Choose a student profile.");
        }
        $app->enrollment->revise($id, (int) $profileId, $_POST["subjects"]);
    });
    if ($saved) {
        flash("Pending enrollment corrected. Review before approving.");
        redirect("enrollments");
    }
}
$profiles = $app->db->all("SELECT ID, studnum, fname, lname FROM studenttbl ORDER BY lname, fname");
$subjects = $app->db->all(
    "SELECT ID, subjectcode, subjecttitle, course_con FROM subjecttbl ORDER BY course_con, subjectcode",
);
$selectedProfile =
    $app->isPost() && is_string($_POST["profile_id"] ?? null) ? $_POST["profile_id"] : null;
$selectedSubjects =
    $app->isPost() && is_array($_POST["subjects"] ?? null)
        ? array_filter($_POST["subjects"], "is_string")
        : null;
view(
    "edit-enrollment",
    compact("user", "row", "profiles", "subjects", "selectedProfile", "selectedSubjects") + [
        "title" => "Correct pending enrollment",
    ],
);
