<?php
declare(strict_types=1);

// Connection entry point used by app/Core/Application.php.
// Configuration and autoloading are initialized by app/start.php first.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Access restricted.');
}

if (!isset($config['database'])) {
    throw new LogicException('Load the application configuration before connecting.');
}

return new BarCollege\Core\Database($config['database']);
