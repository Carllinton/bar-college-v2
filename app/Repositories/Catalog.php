<?php
declare(strict_types=1);
namespace BarCollege\Repositories;
final class Catalog
{
    // Metadata is a server-owned allowlist. Request values never become SQL identifiers.
    public static function resources(): array
    {
        return [
            "students" => [
                "table" => "studenttbl",
                "key" => "ID",
                "title" => "Students",
                "singular" => "student profile",
                "description" => "Student records, made clear.",
                "columns" => [
                    "studnum" => "Student no.",
                    "fname" => "First name",
                    "lname" => "Last name",
                    "course" => "Program",
                    "yearlvl" => "Year / section",
                ],
                "fields" => [
                    "accountID" => ["Student account", 11, true, "account"],
                    "studnum" => ["Student number", 11, true],
                    "fname" => ["First name", 100, true],
                    "minitial" => ["Middle name / initial", 100],
                    "lname" => ["Last name", 100, true],
                    "address" => ["Home address", 500],
                    "contact" => ["Contact number", 14],
                    "emailaddress" => ["Email address", 14],
                    "birthdate" => ["Birth date", 100, false, "date"],
                    "course" => ["Program code", 100, true],
                    "yearlvl" => ["Year / section", 100, true],
                    "gender" => ["Gender", 11],
                    "fathername" => ["Father's name", 100],
                    "mothername" => ["Mother's name", 100],
                    "guardianname" => ["Guardian's name", 100],
                    "birthplace" => ["Birthplace", 100],
                    "civilstatus" => ["Civil status", 100],
                    "elemschool" => ["Elementary school", 100],
                    "dategradelem" => ["Elementary graduation year", 100],
                    "hsschool" => ["High school", 100],
                    "datehsgrad" => ["High school graduation year", 100],
                    "lastgrad" => ["Last school attended", 100],
                    "datelastgrad" => ["Last graduation year", 100],
                    "addresslastgrad" => ["Last school address", 100],
                ],
            ],
            "subjects" => [
                "table" => "subjecttbl",
                "key" => "ID",
                "title" => "Subjects",
                "singular" => "subject",
                "description" => "Build a thoughtful curriculum.",
                "columns" => [
                    "subjectcode" => "Code",
                    "subjecttitle" => "Subject",
                    "numofunits" => "Units",
                    "course_con" => "Program",
                    "instructor" => "Instructor",
                ],
                "fields" => [
                    "subjectcode" => ["Subject code", 100, true],
                    "subjecttitle" => ["Subject title", 100, true],
                    "numofunits" => ["Units", 100, true, "units"],
                    "room" => ["Room", 100],
                    "time" => ["Time (e.g. 09:00-10:30)", 100],
                    "instructor" => ["Instructor", 100],
                    "day" => ["Day", 100],
                    "islab" => ["Laboratory subject", 1, false, "bool"],
                    "istutorial" => ["Tutorial subject", 1, false, "bool"],
                    "description" => ["Description", 100],
                    "course_con" => ["Program code", 100, true],
                ],
            ],
            "courses" => [
                "table" => "courses_tbl",
                "key" => "course_ID",
                "title" => "Programs",
                "singular" => "program",
                "description" => "A place for every path.",
                "columns" => [
                    "course_title" => "Program code / title",
                    "course_desc" => "Description",
                ],
                "fields" => [
                    "course_title" => ["Program code / title", 100, true],
                    "course_desc" => ["Description", 5000, true, "textarea"],
                ],
            ],
            "sections" => [
                "table" => "sections_tbl",
                "key" => "section_ID",
                "title" => "Sections",
                "singular" => "section",
                "description" => "Organize your learning community.",
                "columns" => [
                    "section_name" => "Section",
                    "section_course" => "Program",
                    "yr_level" => "Year",
                    "min_num" => "Minimum",
                    "max_num" => "Maximum",
                ],
                "fields" => [
                    "section_name" => ["Section name", 100, true],
                    "section_course" => ["Program code", 100, true],
                    "yr_level" => ["Year level", 100, true],
                    "min_num" => ["Minimum class size", 9, true, "number"],
                    "max_num" => ["Maximum class size", 9, true, "number"],
                ],
            ],
            "schedules" => [
                "table" => "sched_tbl",
                "key" => "sched_ID",
                "title" => "Schedules",
                "singular" => "schedule",
                "description" => "Make room for learning.",
                "columns" => [
                    "sched_ID" => "Schedule",
                    "section_ID" => "Section ID",
                    "subject_ID" => "Subject ID",
                    "time_from" => "From",
                    "time_to" => "To",
                    "room_ID" => "Room ID",
                ],
                "fields" => [
                    "section_ID" => ["Section", 9, true, "section"],
                    "subject_ID" => ["Subject", 9, true, "subject"],
                    "Instructor_ID" => ["Instructor reference ID", 9, true, "number"],
                    "time_from" => ["Start time", 100, true, "time"],
                    "time_to" => ["End time", 100, true, "time"],
                    "room_ID" => ["Room reference ID", 9, true, "number"],
                ],
            ],
            "accounts" => [
                "table" => "usertbl",
                "key" => "ID",
                "title" => "Accounts",
                "singular" => "account",
                "description" => "The right access for everyone.",
                "columns" => [
                    "username" => "Username",
                    "fullname" => "Display name",
                    "user_type" => "Role",
                ],
                "fields" => [
                    "username" => ["Username", 11, true],
                    "password" => ["Password", 11, true, "password"],
                    "fullname" => ["Display name", 20, true],
                    "user_type" => ["Role", 11, true, "role"],
                ],
            ],
        ];
    }
    public static function get(string $name): array
    {
        return self::resources()[$name] ?? throw new \InvalidArgumentException("Unknown resource.");
    }
}
