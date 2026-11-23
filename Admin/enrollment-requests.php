<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$app->allowMethods(["GET"]);
$profile = null;
$rows = $app->db->all("SELECT * FROM student_subject ORDER BY ID DESC LIMIT 250");
view("enrollment-history", compact("user", "profile", "rows") + ["title" => "Enrollment requests"]);
