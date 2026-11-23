<?php
declare(strict_types=1);
namespace BarCollege\Repositories;
use BarCollege\Core\Database;
final class Records
{
    public function __construct(private Database $db) {}
    public function find(array $meta, int $id): ?array
    {
        return $this->db->one("SELECT * FROM `{$meta["table"]}` WHERE `{$meta["key"]}` = ?", [$id]);
    }
    public function listing(array $meta, string $search, int $page): array
    {
        $where = "";
        $params = [];
        if ($search !== "") {
            $where =
                " WHERE " .
                implode(
                    " OR ",
                    array_map(
                        static fn($column) => "`$column` LIKE ?",
                        array_keys($meta["columns"]),
                    ),
                );
            $params = array_fill(0, count($meta["columns"]), "%" . $search . "%");
        }
        $count = (int) $this->db->one(
            "SELECT COUNT(*) AS n FROM `{$meta["table"]}`" . $where,
            $params,
        )["n"];
        $page = max(1, min($page, max(1, (int) ceil($count / 12))));
        $offset = ($page - 1) * 12;
        $columns = implode(
            ", ",
            array_map(
                static fn($v) => "`$v`",
                array_unique([$meta["key"], ...array_keys($meta["columns"])]),
            ),
        );
        $rows = $this->db->all(
            "SELECT $columns FROM `{$meta["table"]}` $where ORDER BY `{$meta["key"]}` DESC LIMIT 12 OFFSET $offset",
            $params,
        );
        return compact("count", "page", "rows");
    }
    public function save(array $meta, array $values, int $id): void
    {
        if ($id) {
            $set = implode(", ", array_map(static fn($key) => "`$key` = ?", array_keys($values)));
            $this->db->execute("UPDATE `{$meta["table"]}` SET $set WHERE `{$meta["key"]}` = ?", [
                ...array_values($values),
                $id,
            ]);
        } else {
            $columns = implode(", ", array_map(static fn($v) => "`$v`", array_keys($values)));
            $marks = implode(", ", array_fill(0, count($values), "?"));
            $this->db->execute(
                "INSERT INTO `{$meta["table"]}` ($columns) VALUES ($marks)",
                array_values($values),
            );
        }
    }
    public function delete(array $meta, int $id): void
    {
        $this->db->execute("DELETE FROM `{$meta["table"]}` WHERE `{$meta["key"]}` = ?", [$id]);
    }
}
