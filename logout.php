<?php
declare(strict_types=1);
$app = require __DIR__ . "/app/start.php";
$app->user();
$app->allowMethods(["POST"]);
$app->auth->logout();
redirect("login");
