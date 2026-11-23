<?php
declare(strict_types=1);
use BarCollege\Services\Validation;
$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user();
if ($app->isPost()) {
    $app->attempt(function () use ($app, $user): void {
        $current = is_string($_POST["current_password"] ?? null) ? $_POST["current_password"] : "";
        $password = Validation::text($_POST["password"] ?? "", "New password", 11, true);
        if (strlen($password) < 8 || $password !== ($_POST["confirmation"] ?? null)) {
            throw new InvalidArgumentException(
                "Use 8 to 11 characters and make sure the new passwords match.",
            );
        }
        $app->db->write(function () use ($app, $user, $current, $password): void {
            $account = $app->db->one("SELECT password FROM usertbl WHERE ID = ?", [$user["ID"]]);
            if (!$account || !hash_equals($account["password"], $current)) {
                throw new InvalidArgumentException("The current password is incorrect.");
            }
            $app->db->execute("UPDATE usertbl SET password = ? WHERE ID = ?", [
                $password,
                $user["ID"],
            ]);
        });
        session_regenerate_id(true);
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
        flash("Password updated.");
    });
    redirect("settings");
}
view("settings", compact("user") + ["title" => "Account settings"]);
