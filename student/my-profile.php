<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("student");
$app->allowMethods(["GET"]);
$profile = $app->enrollment->profile((int) $user["ID"]);
view("profile", compact("user", "profile") + ["title" => "My profile"]);
