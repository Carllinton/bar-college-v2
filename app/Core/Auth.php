<?php
declare(strict_types=1);
namespace BarCollege\Core;
final class Auth
{
    public function __construct(private Database $db) {}
    public function user(): ?array
    {
        if (!isset($_SESSION["user_id"])) {
            return null;
        }
        return $this->db->one(
            "SELECT ID, username, fullname, user_type FROM usertbl WHERE ID = ?",
            [$_SESSION["user_id"]],
        );
    }
    public function login(string $username, string $password): bool
    {
        $users = $this->db->all("SELECT ID, password, user_type FROM usertbl WHERE username = ?", [
            $username,
        ]);
        $row = count($users) === 1 ? $users[0] : null;
        if (
            !$row ||
            !in_array($row["user_type"], ["admin", "student"], true) ||
            !hash_equals((string) $row["password"], $password)
        ) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION["user_id"] = (int) $row["ID"];
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
        return true;
    }
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $p["path"],
                $p["domain"],
                $p["secure"],
                $p["httponly"],
            );
        }
        session_destroy();
    }
}
