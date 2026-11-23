<?php
declare(strict_types=1);
use BarCollege\Repositories\Catalog;
use BarCollege\Services\Validation;

$app = require dirname(__DIR__) . "/app/start.php";
$user = $app->user("admin");
$resource = "sections";
$meta = Catalog::get($resource);
if ($app->isPost()) {
    $app->attempt(function () use ($app, $user, $resource): void {
        $app->registry->delete($resource, posted_id(), (int) $user["ID"]);
        flash("Record deleted.");
    });
    redirect("records", ["resource" => $resource]);
}
$q = Validation::text($_GET["q"] ?? "", "Search", 100);
$result = $app->records->listing($meta, $q, max(1, (int) ($_GET["p"] ?? 1)));
view("records", $result + compact("user", "resource", "meta", "q") + ["title" => $meta["title"]]);
