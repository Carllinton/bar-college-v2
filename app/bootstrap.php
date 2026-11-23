<?php
declare(strict_types=1);
require __DIR__ . "/autoload.php";
$configFile = dirname(__DIR__) . "/config/app.php";
$config = require is_file($configFile) ? $configFile : dirname(__DIR__) . "/config/app.example.php";
$isProduction = getenv("APP_ENV") === "production";
if ($isProduction) {
    foreach (["DB_HOST", "DB_NAME", "DB_USER", "DB_PASSWORD", "SESSION_REDIS_URL"] as $key) {
        if (!is_string(getenv($key)) || getenv($key) === "") {
            throw new RuntimeException("Missing production setting: " . $key);
        }
    }
    $config["database"] = [
        "host" => getenv("DB_HOST"),
        "port" => (int) (getenv("DB_PORT") ?: "3306"),
        "name" => getenv("DB_NAME"),
        "username" => getenv("DB_USER"),
        "password" => getenv("DB_PASSWORD"),
    ];
}
date_default_timezone_set($config["timezone"]);
ini_set("display_errors", "0");
ini_set("log_errors", "1");
ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");
session_name("bar_college_v2");
if ($isProduction) {
    session_set_save_handler(
        new BarCollege\Session\RedisSessionHandler(
            getenv("SESSION_REDIS_URL"),
            (int) $config["session_timeout"],
        ),
        true,
    );
}
session_set_cookie_params([
    "httponly" => true,
    "secure" => $isProduction || (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"),
    "samesite" => "Lax",
    "path" => "/",
]);
session_start();
header(
    "Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'",
);
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
header("Cache-Control: no-store");
if (isset($_SESSION["last_seen"]) && time() - $_SESSION["last_seen"] > $config["session_timeout"]) {
    unset($_SESSION["user_id"]);
    session_regenerate_id(true);
}
$_SESSION["last_seen"] = time();
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));
return $config;
