<?php
declare(strict_types=1);
use BarCollege\Repositories\Catalog;

$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$resource = "courses";
$meta = Catalog::get($resource);
$id = 0;
$row = [];
if ($app->isPost()) {
    $saved = $app->attempt(function () use ($app, $resource, $id, $user): void {
        // This create page always inserts a new record; a posted ID cannot turn it into an update.
        $app->registry->save($resource, $_POST, $id, (int) $user["ID"]);
    });
    if ($saved) {
        flash("Record saved.");
        redirect("records", ["resource" => $resource]);
    }
    // Retain only expected non-secret fields when validation fails.
    foreach ($meta["fields"] as $name => $field) {
        if ($name !== "password" && is_string($_POST[$name] ?? null)) {
            $row[$name] = $_POST[$name];
        }
    }
}
$options = $app->options();
view(
    "edit",
    compact("user", "resource", "meta", "id", "row", "options") + [
        "title" => "New " . $meta["singular"],
    ],
);
