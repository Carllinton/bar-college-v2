<?php
declare(strict_types=1);
use BarCollege\Repositories\Catalog;

$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$resource = "courses";
$meta = Catalog::get($resource);
$id = query_id();
$row = $app->records->find($meta, $id);
if (!$row) {
    $app->abort(404, "Record not found", "This record is no longer available.");
}
unset($row["password"]);
if ($app->isPost()) {
    $saved = $app->attempt(function () use ($app, $resource, $id, $user): void {
        // Resource is fixed by this file; an edited record ID comes from the URL.
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
        "title" => "Edit " . $meta["singular"],
    ],
);
