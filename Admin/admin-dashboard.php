<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$app->allowMethods(["GET"]);
$stats = [];
foreach (
    ["Students" => "studenttbl", "Subjects" => "subjecttbl", "Programs" => "courses_tbl"]
    as $label => $table
) {
    $stats[$label] = (int) $app->db->one("SELECT COUNT(*) AS n FROM `$table`")["n"];
}
$stats["Pending requests"] = (int) $app->db->one(
    "SELECT COUNT(*) AS n FROM student_subject WHERE approved = '0'",
)["n"];
$recent = $app->db->all(
    "SELECT ID, studentID, subjects, totalunits, approved FROM student_subject ORDER BY ID DESC LIMIT 5",
);
view("dashboard", compact("user", "stats", "recent") + ["title" => "Overview"]);
