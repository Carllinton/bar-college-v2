<?php
declare(strict_types=1);
namespace BarCollege\Core;
use PDO;
use RuntimeException;
final class Database
{
    public readonly PDO $pdo;
    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
                $config["host"],
                $config["port"],
                $config["name"],
            ),
            $config["username"],
            $config["password"],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        $this->pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION'");
    }
    public function all(string $sql, array $params = []): array
    {
        $q = $this->pdo->prepare($sql);
        $q->execute($params);
        return $q->fetchAll();
    }
    public function one(string $sql, array $params = []): ?array
    {
        return $this->all($sql, $params)[0] ?? null;
    }
    public function execute(string $sql, array $params = []): int
    {
        $q = $this->pdo->prepare($sql);
        $q->execute($params);
        return $q->rowCount();
    }
    // Advisory lock serializes this application's writes without DDL or LOCK TABLES privileges.
    // MyISAM has no rollback. Each workflow deliberately writes ONE row in ONE statement.
    public function write(callable $callback): mixed
    {
        $name = "bar-college:" . $this->pdo->query("SELECT DATABASE()")->fetchColumn();
        if ((int) $this->one("SELECT GET_LOCK(?, 5) AS acquired", [$name])["acquired"] !== 1) {
            throw new RuntimeException("Another update is in progress. Please try again.");
        }
        try {
            return $callback();
        } finally {
            $this->one("SELECT RELEASE_LOCK(?)", [$name]);
        }
    }
}
