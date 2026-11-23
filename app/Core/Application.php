<?php
declare(strict_types=1);
namespace BarCollege\Core;

use BarCollege\Repositories\Records;
use BarCollege\Services\{Enrollment, Registry};
use InvalidArgumentException;
use RuntimeException;
use PDOException;

/** Shared dependencies and HTTP protections. No page routing happens here. */
final class Application
{
    public readonly Database $db;
    public readonly Auth $auth;
    public readonly Records $records;
    public readonly Registry $registry;
    public readonly Enrollment $enrollment;

    public function __construct(array $config)
    {
        $this->db = require dirname(__DIR__, 2) . "/conn.php";
        $this->auth = new Auth($this->db);
        $this->records = new Records($this->db);
        $this->registry = new Registry($this->db, $this->records);
        $this->enrollment = new Enrollment($this->db);
        $GLOBALS["current_user"] = $this->auth->user();
        $this->allowMethods(["GET", "POST"]);
        if (
            $this->isPost() &&
            (!is_string($_POST["csrf"] ?? null) || !hash_equals($_SESSION["csrf"], $_POST["csrf"]))
        ) {
            $this->abort(419, "Form expired", "Reload the page and try again.");
        }
    }

    public function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    public function user(?string $role = null): array
    {
        $user = $GLOBALS["current_user"];
        if (!$user) {
            redirect("login");
        }
        if (
            !in_array($user["user_type"], ["admin", "student"], true) ||
            ($role !== null && $user["user_type"] !== $role)
        ) {
            $this->abort(
                403,
                "Access restricted",
                "Your account does not have access to this page.",
            );
        }
        return $user;
    }

    public function allowMethods(array $allowed): void
    {
        if (!in_array($_SERVER["REQUEST_METHOD"], $allowed, true)) {
            header("Allow: " . implode(", ", $allowed));
            $this->abort(405, "Method not allowed", "Use the form on the relevant page.");
        }
    }

    public function abort(int $status, string $title, string $message): never
    {
        http_response_code($status);
        view("error", [
            "user" => $GLOBALS["current_user"] ?? null,
            "title" => $title,
            "message" => $message,
        ]);
        exit();
    }

    /** Recover from user validation / write-lock errors, while leaving unexpected DB failures to the error handler. */
    public function attempt(callable $action): bool
    {
        try {
            $action();
            return true;
        } catch (InvalidArgumentException | RuntimeException $error) {
            if ($error instanceof PDOException) {
                throw $error;
            }
            flash($error->getMessage(), "error");
            return false;
        }
    }

    public function options(): array
    {
        return [
            "account" => $this->db->all(
                "SELECT ID AS value, CONCAT(username, ' · ', fullname) AS label FROM usertbl WHERE user_type = 'student' ORDER BY username",
            ),
            "subject" => $this->db->all(
                "SELECT ID AS value, CONCAT(subjectcode, ' · ', subjecttitle) AS label FROM subjecttbl ORDER BY subjectcode",
            ),
            "section" => $this->db->all(
                "SELECT section_ID AS value, section_name AS label FROM sections_tbl ORDER BY section_name",
            ),
            "role" => [
                ["value" => "student", "label" => "Student"],
                ["value" => "admin", "label" => "Administrator"],
            ],
            "bool" => [["value" => "0", "label" => "No"], ["value" => "1", "label" => "Yes"]],
        ];
    }

    public function enrollmentDetails(array $row): array
    {
        $profiles = $this->db->all("SELECT * FROM studenttbl WHERE studnum = ?", [
            $row["studentID"],
        ]);
        $student = count($profiles) === 1 ? $profiles[0] : null;
        $subjects = [];
        foreach (codes($row["subjects"]) as $code) {
            $matches = $this->db->all("SELECT * FROM subjecttbl WHERE subjectcode = ?", [$code]);
            $subjects[] =
                count($matches) === 1
                    ? $matches[0]
                    : [
                        "subjectcode" => $code,
                        "subjecttitle" => "Missing or ambiguous catalog entry",
                        "numofunits" => "?",
                        "day" => "",
                        "time" => "",
                        "room" => "",
                        "instructor" => "",
                    ];
        }
        return compact("student", "subjects");
    }
}
