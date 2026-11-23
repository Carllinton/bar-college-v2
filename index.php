<?php
declare(strict_types=1);
$app = require __DIR__ . "/app/start.php";
if ($GLOBALS["current_user"]) {
    redirect("dashboard");
}
if ($app->isPost()) {
    $attempts = (int) ($_SESSION["login_attempts"] ?? 0);
    $last = (int) ($_SESSION["login_time"] ?? 0);
    if ($attempts >= 5 && time() - $last < 60) {
        flash("Too many attempts in this session. Wait one minute.", "error");
        redirect("login");
    }
    if (time() - $last >= 60) {
        $_SESSION["login_attempts"] = 0;
    }
    $username = is_string($_POST["username"] ?? null) ? trim($_POST["username"]) : "";
    $password = is_string($_POST["password"] ?? null) ? $_POST["password"] : "";
    if (
        strlen($username) <= 44 &&
        strlen($password) <= 44 &&
        $app->auth->login($username, $password)
    ) {
        unset($_SESSION["login_attempts"], $_SESSION["login_time"]);
        $GLOBALS["current_user"] = $app->auth->user();
        redirect("dashboard");
    }
    $_SESSION["login_attempts"] = (int) ($_SESSION["login_attempts"] ?? 0) + 1;
    $_SESSION["login_time"] = time();
    flash("The username or password is incorrect.", "error");
    redirect("login");
}
view("login", ["user" => null, "title" => "Sign in"]);
