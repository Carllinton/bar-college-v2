<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("student");
$app->allowMethods(["GET"]);
$profile = $app->enrollment->profile((int) $user["ID"]);
$recent = $profile
    ? $app->db->all("SELECT * FROM student_subject WHERE studentID = ? ORDER BY ID DESC", [
        $profile["studnum"],
    ])
    : [];
view("student", compact("user", "profile", "recent") + ["title" => "My campus"]);
