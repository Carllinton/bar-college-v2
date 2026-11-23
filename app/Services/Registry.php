<?php
declare(strict_types=1);
namespace BarCollege\Services;
use BarCollege\Core\Database;
use BarCollege\Repositories\{Catalog, Records};
use InvalidArgumentException;
final class Registry
{
    public function __construct(private Database $db, private Records $records) {}
    private function ensure(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InvalidArgumentException($message);
        }
    }
    private function unique(
        string $table,
        string $column,
        string $value,
        string $key,
        int $id,
    ): void {
        $this->ensure(
            !$this->db->one("SELECT `$key` FROM `$table` WHERE `$column` = ? AND `$key` <> ?", [
                $value,
                $id,
            ]),
            "That value is already in use: " . $column . ".",
        );
    }
    private function subjectUsed(string $code): bool
    {
        foreach ($this->db->all("SELECT subjects FROM student_subject") as $row) {
            if (in_array($code, codes($row["subjects"]), true)) {
                return true;
            }
        }
        return false;
    }
    public function save(string $resource, array $input, int $id, int $actor): void
    {
        $meta = Catalog::get($resource);
        $this->db->write(function () use ($resource, $input, $id, $actor, $meta): void {
            $old = $id ? $this->records->find($meta, $id) : null;
            $this->ensure(!$id || (bool) $old, "Record no longer exists.");
            $v = Validation::fields($meta, $input, (bool) $id);
            switch ($resource) {
                case "accounts":
                    $this->unique("usertbl", "username", $v["username"], "ID", $id);
                    $this->ensure(
                        !$id || $id !== $actor || $v["user_type"] === "admin",
                        "You cannot remove your own administrator access.",
                    );
                    $this->ensure(
                        $v["user_type"] === "student" ||
                            !$this->db->one("SELECT ID FROM studenttbl WHERE accountID = ?", [
                                (string) $id,
                            ]),
                        "An account linked to a student profile must retain the student role.",
                    );
                    if ($old && $old["user_type"] === "admin" && $v["user_type"] !== "admin") {
                        $this->ensure(
                            (int) $this->db->one(
                                "SELECT COUNT(*) AS n FROM usertbl WHERE user_type = 'admin'",
                            )["n"] > 1,
                            "Keep at least one administrator.",
                        );
                    }
                    break;
                case "students":
                    $this->unique("studenttbl", "studnum", $v["studnum"], "ID", $id);
                    $this->unique("studenttbl", "accountID", $v["accountID"], "ID", $id);
                    $this->ensure(
                        (bool) $this->db->one(
                            "SELECT ID FROM usertbl WHERE ID = ? AND user_type = 'student'",
                            [$v["accountID"]],
                        ),
                        "Choose an existing student account.",
                    );
                    if ($old) {
                        $this->ensure(
                            $v["accountID"] === $old["accountID"],
                            "Account ownership cannot be reassigned.",
                        );
                    }
                    if (
                        $old &&
                        $this->db->one("SELECT ID FROM student_subject WHERE studentID = ?", [
                            $old["studnum"],
                        ])
                    ) {
                        $this->ensure(
                            $v["studnum"] === $old["studnum"] && $v["course"] === $old["course"],
                            "Student number and program are locked while enrollment records exist.",
                        );
                    }
                    if (
                        $v["emailaddress"] !== "" &&
                        (!$old || $v["emailaddress"] !== $old["emailaddress"])
                    ) {
                        $this->ensure(
                            (bool) filter_var($v["emailaddress"], FILTER_VALIDATE_EMAIL),
                            "Enter a valid email of at most 14 characters, or leave it blank.",
                        );
                    }
                    break;
                case "subjects":
                    if ($old && $v["course_con"] !== $old["course_con"]) {
                        $this->ensure(
                            !$this->db->one(
                                "SELECT sched_ID FROM sched_tbl JOIN sections_tbl ON sections_tbl.section_ID = sched_tbl.section_ID WHERE subject_ID = ? AND section_course <> ? LIMIT 1",
                                [$id, $v["course_con"]],
                            ),
                            "The new program would conflict with this subject's section schedules.",
                        );
                    }
                    $this->ensure(
                        !str_contains($v["subjectcode"], ","),
                        "Subject codes cannot contain commas.",
                    );
                    $this->unique("subjecttbl", "subjectcode", $v["subjectcode"], "ID", $id);
                    if ($old && $this->subjectUsed($old["subjectcode"])) {
                        $this->ensure(
                            $v["subjectcode"] === $old["subjectcode"] &&
                                (int) $v["numofunits"] === (int) $old["numofunits"] &&
                                $v["course_con"] === $old["course_con"],
                            "Code, units, and program are locked while enrollment records refer to this subject.",
                        );
                    }
                    break;
                case "courses":
                    $this->unique(
                        "courses_tbl",
                        "course_title",
                        $v["course_title"],
                        "course_ID",
                        $id,
                    );
                    if ($old && $old["course_title"] !== $v["course_title"]) {
                        $this->courseUnused($old["course_title"]);
                    }
                    break;
                case "sections":
                    if ($old && $v["section_course"] !== $old["section_course"]) {
                        $this->ensure(
                            !$this->db->one(
                                "SELECT sched_ID FROM sched_tbl JOIN subjecttbl ON subjecttbl.ID = sched_tbl.subject_ID WHERE section_ID = ? AND course_con <> ? LIMIT 1",
                                [$id, $v["section_course"]],
                            ),
                            "The new program would conflict with this section's subject schedules.",
                        );
                    }
                    $this->unique(
                        "sections_tbl",
                        "section_name",
                        $v["section_name"],
                        "section_ID",
                        $id,
                    );
                    $this->ensure(
                        (int) $v["max_num"] > 0 && (int) $v["min_num"] <= (int) $v["max_num"],
                        "Maximum must be positive and minimum cannot exceed maximum.",
                    );
                    break;
                case "schedules":
                    $section = $this->db->one("SELECT * FROM sections_tbl WHERE section_ID = ?", [
                        $v["section_ID"],
                    ]);
                    $subject = $this->db->one("SELECT * FROM subjecttbl WHERE ID = ?", [
                        $v["subject_ID"],
                    ]);
                    $this->ensure(
                        (bool) $section && (bool) $subject,
                        "Choose an existing section and subject.",
                    );
                    $this->ensure(
                        strcasecmp($section["section_course"], $subject["course_con"]) === 0,
                        "Section and subject must have the same program code.",
                    );
                    $this->ensure(
                        $v["time_from"] < $v["time_to"],
                        "End time must be after start time.",
                    );
                    $this->ensure(
                        (int) $v["Instructor_ID"] > 0 && (int) $v["room_ID"] > 0,
                        "Room and instructor reference IDs must be positive.",
                    );
                    break;
            }
            $this->records->save($meta, $v, $id);
        });
    }
    private function courseUnused(string $title): void
    {
        foreach (
            [
                ["studenttbl", "course"],
                ["subjecttbl", "course_con"],
                ["sections_tbl", "section_course"],
            ]
            as [$table, $column]
        ) {
            $this->ensure(
                !$this->db->one("SELECT `$column` FROM `$table` WHERE `$column` = ? LIMIT 1", [
                    $title,
                ]),
                "This program is referenced by records. Update those through the application first.",
            );
        }
    }
    public function delete(string $resource, int $id, int $actor): void
    {
        $meta = Catalog::get($resource);
        $this->db->write(function () use ($resource, $id, $actor, $meta): void {
            $old = $this->records->find($meta, $id);
            $this->ensure((bool) $old, "Record no longer exists.");
            switch ($resource) {
                case "accounts":
                    $this->ensure($id !== $actor, "You cannot delete your own account.");
                    $this->ensure(
                        !$this->db->one("SELECT ID FROM studenttbl WHERE accountID = ?", [
                            (string) $id,
                        ]),
                        "Remove the linked student profile first.",
                    );
                    if ($old["user_type"] === "admin") {
                        $this->ensure(
                            (int) $this->db->one(
                                "SELECT COUNT(*) AS n FROM usertbl WHERE user_type = 'admin'",
                            )["n"] > 1,
                            "Keep at least one administrator.",
                        );
                    }
                    break;
                case "students":
                    $this->ensure(
                        !$this->db->one("SELECT ID FROM student_subject WHERE studentID = ?", [
                            $old["studnum"],
                        ]),
                        "Enrollment history exists. This profile cannot be deleted.",
                    );
                    break;
                case "subjects":
                    $this->ensure(
                        !$this->subjectUsed($old["subjectcode"]),
                        "Enrollment records refer to this subject.",
                    );
                    $this->ensure(
                        !$this->db->one("SELECT sched_ID FROM sched_tbl WHERE subject_ID = ?", [
                            $id,
                        ]),
                        "Remove related schedules first.",
                    );
                    break;
                case "sections":
                    $this->ensure(
                        !$this->db->one("SELECT sched_ID FROM sched_tbl WHERE section_ID = ?", [
                            $id,
                        ]),
                        "Remove related schedules first.",
                    );
                    break;
                case "courses":
                    $this->courseUnused($old["course_title"]);
                    break;
            }
            $this->records->delete($meta, $id);
        });
    }
}
