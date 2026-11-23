<?php
declare(strict_types=1);

$config = require __DIR__ . "/bootstrap.php";
set_exception_handler(static function (Throwable $error): void {
    // Avoid sending a partially rendered page together with an error page.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if ($error instanceof InvalidArgumentException) {
        http_response_code(400);
        view("error", [
            "user" => $GLOBALS["current_user"] ?? null,
            "title" => "Check this request",
            "message" => $error->getMessage(),
        ]);
        return;
    }
    $reference = bin2hex(random_bytes(4));
    error_log(
        "BAR College v2 incident " .
            $reference .
            ": " .
            get_class($error) .
            " at " .
            basename($error->getFile()) .
            ":" .
            $error->getLine(),
    );
    http_response_code(503);
    view("error", [
        "user" => null,
        "title" => "We could not load the portal",
        "message" =>
            "Try again or check the setup instructions in docs/README.md. Reference: " . $reference,
    ]);
});
return new BarCollege\Core\Application($config);
