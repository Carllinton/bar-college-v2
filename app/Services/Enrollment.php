<?php
declare(strict_types=1);
namespace BarCollege\Services;
use BarCollege\Core\Database;
use InvalidArgumentException;
final class Enrollment
{
    public function __construct(private Database $db) {}
    public function profile(int $account): ?array
    {
        $rows = $this->db->all("SELECT * FROM studenttbl WHERE accountID = ?", [(string) $account]);
        if (count($rows) > 1) {
            throw new InvalidArgumentException(
                "Multiple profiles are linked to this account. Contact the registrar.",
            );
        }
        $profile = $rows[0] ?? null;
        if (
            $profile &&
            ($profile["studnum"] === "" ||
                (int) $this->db->one("SELECT COUNT(*) AS n FROM studenttbl WHERE studnum = ?", [
                    $profile["studnum"],
                ])["n"] !== 1)
        ) {
            throw new InvalidArgumentException(
                "Your student number is missing or duplicated. Contact the registrar.",
            );
        }
        return $profile;
    }
    public function selection(array $ids, array $profile): array
    {
        if (!$ids || count($ids) > 40 || count($ids) !== count(array_unique($ids, SORT_REGULAR))) {
            throw new InvalidArgumentException("Choose 1 to 40 distinct subjects.");
        }
        foreach ($ids as $id) {
            if (!is_scalar($id) || !ctype_digit((string) $id)) {
                throw new InvalidArgumentException("Invalid subject selection.");
            }
        }
        $marks = implode(",", array_fill(0, count($ids), "?"));
        $rows = $this->db->all(
            "SELECT * FROM subjecttbl WHERE ID IN ($marks) ORDER BY subjectcode",
            array_map("intval", $ids),
        );
        if (count($rows) !== count($ids)) {
            throw new InvalidArgumentException("A selected subject is no longer available.");
        }
        $units = 0;
        $seen = [];
        foreach ($rows as $row) {
            if (strcasecmp($row["course_con"], $profile["course"]) !== 0) {
                throw new InvalidArgumentException("Choose only subjects in your program.");
            }
            $code = trim($row["subjectcode"]);
            if (
                $code === "" ||
                str_contains($code, ",") ||
                in_array($code, $seen, true) ||
                !ctype_digit($row["numofunits"]) ||
                (int) $row["numofunits"] < 1 ||
                (int) $row["numofunits"] > 12
            ) {
                throw new InvalidArgumentException(
                    "A selected subject has invalid legacy data. Ask the registrar to review it.",
                );
            }
            if (
                (int) $this->db->one("SELECT COUNT(*) AS n FROM subjecttbl WHERE subjectcode = ?", [
                    $code,
                ])["n"] !== 1
            ) {
                throw new InvalidArgumentException(
                    "A subject code is ambiguous. Contact the registrar.",
                );
            }
            $seen[] = $code;
            $units += (int) $row["numofunits"];
        }
        return ["rows" => $rows, "subjects" => implode(",", $seen), "units" => $units];
    }
    public function submit(int $account, array $ids): void
    {
        $this->db->write(function () use ($account, $ids): void {
            $profile = $this->profile($account);
            if (!$profile) {
                throw new InvalidArgumentException(
                    "Ask the registrar to create your student profile first.",
                );
            }
            if (
                $this->db->one("SELECT ID FROM student_subject WHERE studentID = ?", [
                    $profile["studnum"],
                ])
            ) {
                throw new InvalidArgumentException(
                    "You already have an enrollment record. View it below or contact the registrar.",
                );
            }
            $selection = $this->selection($ids, $profile);
            $this->db->execute(
                "INSERT INTO student_subject (studentID, subjects, totalunits, approved) VALUES (?, ?, ?, ?)",
                [$profile["studnum"], $selection["subjects"], $selection["units"], "0"],
            );
        });
    }
    public function withdraw(int $account, int $id): void
    {
        $this->db->write(function () use ($account, $id): void {
            $profile = $this->profile($account);
            if (
                !$profile ||
                $this->db->execute(
                    "DELETE FROM student_subject WHERE ID = ? AND studentID = ? AND approved = '0'",
                    [$id, $profile["studnum"]],
                ) !== 1
            ) {
                throw new InvalidArgumentException(
                    "Only your pending request can be withdrawn. Refresh and try again.",
                );
            }
        });
    }
    public function removePending(int $id): void
    {
        $this->db->write(function () use ($id): void {
            if (
                $this->db->execute("DELETE FROM student_subject WHERE ID = ? AND approved = '0'", [
                    $id,
                ]) !== 1
            ) {
                throw new InvalidArgumentException("Only pending requests may be removed.");
            }
        });
    }
    public function revise(int $id, int $profileId, array $ids): void
    {
        $this->db->write(function () use ($id, $profileId, $ids): void {
            $row = $this->db->one("SELECT * FROM student_subject WHERE ID = ?", [$id]);
            if (!$row || $row["approved"] !== "0") {
                throw new InvalidArgumentException("Only pending requests can be corrected.");
            }
            $profile = $this->db->one("SELECT * FROM studenttbl WHERE ID = ?", [$profileId]);
            if (
                !$profile ||
                $profile["studnum"] === "" ||
                (int) $this->db->one("SELECT COUNT(*) AS n FROM studenttbl WHERE studnum = ?", [
                    $profile["studnum"],
                ])["n"] !== 1
            ) {
                throw new InvalidArgumentException("Choose an unambiguous student profile.");
            }
            if (
                $this->db->one("SELECT ID FROM student_subject WHERE studentID = ? AND ID <> ?", [
                    $profile["studnum"],
                    $id,
                ])
            ) {
                throw new InvalidArgumentException(
                    "This student already has another enrollment. Review duplicates first.",
                );
            }
            $selection = $this->selection($ids, $profile);
            $this->db->execute(
                "UPDATE student_subject SET studentID = ?, subjects = ?, totalunits = ? WHERE ID = ? AND approved = '0'",
                [$profile["studnum"], $selection["subjects"], $selection["units"], $id],
            );
        });
    }
    public function approve(int $id): void
    {
        $this->db->write(function () use ($id): void {
            $row = $this->db->one("SELECT * FROM student_subject WHERE ID = ?", [$id]);
            if (!$row || $row["approved"] !== "0") {
                throw new InvalidArgumentException("Only pending requests can be approved.");
            }
            $profiles = $this->db->all("SELECT * FROM studenttbl WHERE studnum = ?", [
                $row["studentID"],
            ]);
            if (count($profiles) !== 1) {
                throw new InvalidArgumentException(
                    "This record has a missing or ambiguous student.",
                );
            }
            if (
                (int) $this->db->one(
                    "SELECT COUNT(*) AS n FROM student_subject WHERE studentID = ?",
                    [$row["studentID"]],
                )["n"] !== 1
            ) {
                throw new InvalidArgumentException(
                    "Duplicate legacy enrollments require registrar review before approval. No history was deleted.",
                );
            }
            $subjectCodes = codes($row["subjects"]);
            $ids = [];
            foreach ($subjectCodes as $code) {
                $matches = $this->db->all("SELECT ID FROM subjecttbl WHERE subjectcode = ?", [
                    $code,
                ]);
                if (count($matches) !== 1) {
                    throw new InvalidArgumentException(
                        "Unknown or ambiguous subject code in this enrollment.",
                    );
                }
                $ids[] = (string) $matches[0]["ID"];
            }
            $selection = $this->selection($ids, $profiles[0]);
            if ($selection["units"] !== (int) $row["totalunits"]) {
                throw new InvalidArgumentException(
                    "Stored units do not match the catalog. Registrar review is required.",
                );
            }
            $this->db->execute(
                "UPDATE student_subject SET approved = '1' WHERE ID = ? AND approved = '0'",
                [$id],
            );
        });
    }
}
