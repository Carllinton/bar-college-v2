<?php
declare(strict_types=1);
require dirname(__DIR__) . "/app/autoload.php";
use BarCollege\Services\Validation;
use BarCollege\Repositories\Catalog;
$passed = 0;
function check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($label);
    }
    $passed++;
    echo "PASS $label\n";
}
function rejects(callable $fn, string $label): void
{
    try {
        $fn();
    } catch (InvalidArgumentException) {
        check(true, $label);
        return;
    }
    check(false, $label);
}
check(codes(",IT201, IT202,,") === ["IT201", "IT202"], "legacy comma parsing");
check(e("<script>") === "&lt;script&gt;", "HTML output escaping");
rejects(fn() => Validation::text("long@example.com", "Email", 14), "email storage limit");
rejects(fn() => Validation::text("Test 😀", "Name", 100), "unsupported legacy characters");
check(Validation::text("Muñoz", "Name", 100) === "Muñoz", "Latin names supported");
rejects(fn() => Validation::text(["bad"], "Name", 100), "array input rejected");
rejects(fn() => Catalog::get("usertbl; DROP TABLE usertbl"), "SQL identifier allowlist");
$meta = Catalog::get("accounts");
rejects(
    fn() => Validation::fields(
        $meta,
        ["username" => "demo", "password" => "short", "fullname" => "Demo", "user_type" => "admin"],
        false,
    ),
    "new password minimum",
);
rejects(
    fn() => Validation::fields(
        $meta,
        [
            "username" => "demo",
            "password" => "Demo12345",
            "fullname" => "Demo",
            "user_type" => "superadmin",
        ],
        false,
    ),
    "role allowlist",
);
$v = Validation::fields(
    $meta,
    ["username" => "demo", "password" => "", "fullname" => "Demo", "user_type" => "student"],
    true,
);
check(!array_key_exists("password", $v), "blank edit password preserves existing value");
$meta = ["fields" => ["birthdate" => ["Birth date", 100, true, "date"]]];
rejects(
    fn() => Validation::fields($meta, ["birthdate" => "2026-02-30"], false),
    "invalid calendar date",
);
$meta = ["fields" => ["units" => ["Units", 100, true, "units"]]];
rejects(
    fn() => Validation::fields($meta, ["units" => "2.5"], false),
    "fractional units rejected for integer storage",
);
rejects(fn() => Validation::fields($meta, ["units" => "-3"], false), "negative units rejected");
check(Validation::fields($meta, ["units" => "3"], false)["units"] === "3", "valid units accepted");
$meta = ["fields" => ["time" => ["Start time", 100, true, "time"]]];
rejects(fn() => Validation::fields($meta, ["time" => "25:99"], false), "invalid clock time");
echo "$passed checks passed.\n";
