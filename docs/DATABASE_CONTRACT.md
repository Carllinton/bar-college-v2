# Fixed database contract

## Preserved source (main-folder edition)

`original.sql` is copied unchanged from `Online_enroll_for_sharing/online_enroll (2).sql`. The original archive remains separate. The reference does not rewrite, normalize, migrate, or seed the database on startup.

Initial import is installation, not a migration. After installation, application data mutations use form-driven `INSERT`, `UPDATE`, or `DELETE`. Reads use `SELECT`. Connection-level SQL mode and advisory-lock functions coordinate application behavior without creating or modifying schema objects.

## Table mapping

| Original table | Reference workflow | Important fields and behavior |
| --- | --- | --- |
| `usertbl` | Accounts and login | `ID`, `username`, `password`, `fullname`, `user_type`; only `admin` and `student` roles |
| `studenttbl` | Student profiles | `accountID` refers to account ID as text; `studnum` is the enrollment link |
| `subjecttbl` | Subject catalog | Codes, titles, units, textual schedule details, program, lab/tutorial flags |
| `student_subject` | Enrollment requests | Student number, comma-separated subject codes, integer total units, approval flag |
| `courses_tbl` | Programs | Program title/code and description |
| `sections_tbl` | Sections | Program text, year text, minimum and maximum sizes |
| `sched_tbl` | Legacy schedule records | Section/subject IDs, numeric room/instructor references, text time range |

No triggers, views, indexes, columns, or database-enforced relationships were added.

## Constraints and consequences

| Fixed constraint | Application response | Remaining limit |
| --- | --- | --- |
| Password `varchar(11)` with plaintext sample values | Compatible authentication; 8–11 character new test passwords; no password rendering | Cannot store standard secure password hashes in this field |
| Username `varchar(11)` | Reject overlong values and duplicates through the application | Legacy duplicates would need registrar correction |
| Display name `varchar(20)` | Separate account display name from full student profile name | Long account display names do not fit |
| Email `varchar(14)` | Validate new/changed nonblank addresses and reject overlong input | Many normal addresses cannot be stored; unchanged malformed legacy email is retained |
| `latin1` tables | Preserve database charset; explicitly reject unsupported new characters | Full Unicode names and emoji cannot be stored |
| MyISAM | Single-row writes and application advisory locking | No transactional rollback; external writers can bypass advisory locks |
| No unique constraints for username/student number | Check under the shared application write lock | Direct SQL or another application can still create duplicates |
| Comma-separated subject codes | Parse legacy delimiters; serialize new selections consistently | No normalized enrollment detail rows or foreign keys |
| `totalunits` is integer | Only whole units; calculate totals from catalog | Fractional credit units unsupported |
| Only `approved` flag | `0` = pending, `1` = approved; unknown values marked for review | No rejected/cancelled/archived status or approval audit trail |
| No semester/year/date columns | Show records without invented terms/dates; one enrollment per student | No reliable multi-term enrollment history |
| No grades or payment tables | No grade entry, payment receipt, or invented billing | Printed record is an enrollment request only |
| No instructor/room directory | Preserve numeric reference IDs in schedules | Cannot validate those IDs against absent tables |
| No schedule day in `sched_tbl` | Store its existing fields only | Cannot reliably detect scheduling conflicts |
| No subject snapshot in enrollment | Resolve codes against current catalog | Later title/instructor/schedule edits affect displayed past records |
| No audit/version columns | Review pending changes explicitly | No persistent audit trail or conflict detection for stale edit forms |

The `course_desc` form uses an application limit of 5,000 characters for usability; the original LONGTEXT column remains unchanged. New subject units are limited to 1–12 per subject as an explicit application validation rule. Section sizes are stored and validated, but no automatic assignment or capacity enforcement is claimed because there is no reliable enrollment-to-section relation.

## Existing data that requires human review

The original dump has two account rows, one student profile, three subjects, and eight enrollment rows. Program, section, and schedule tables are empty.

- Enrollment #1 has a blank student number.
- Enrollments #2–#8 all use student number `2014-052`.
- Some subject strings contain duplicate codes or extra commas.
- Several stored total-unit values do not match the catalog subjects.
- The existing profile email is not a valid email address.
- The account display name differs from the associated profile name.
- Original subject schedule fields are blank.

These rows are not silently repaired. The registrar can open pending requests, remove reviewed duplicates, and correct the retained request through forms. Correction recalculates units and validates the student/program/subject relationships. Approval blocks ambiguous, orphaned, duplicated, or inconsistent requests. Approved records cannot be deleted or revised through the reference UI.

## Relationship protection

- Student profiles require an existing student account and unique account linkage.
- Existing profile account ownership cannot be reassigned.
- Student numbers and program codes cannot change while enrollments reference the profile.
- Accounts with profiles cannot be deleted or promoted to administrator.
- Referenced subjects cannot be deleted; their code, units, and program are locked.
- Programs referenced by students, subjects, or sections cannot be renamed/deleted.
- Sections/subjects with legacy schedule references cannot be deleted until those schedules are removed. Their program edits cannot create a mismatch with scheduled subjects/sections.
- The current administrator cannot delete or demote themselves; at least one administrator must remain.

All restrictions are implemented in PHP. Do not claim they are foreign keys or schema constraints.

## Features requiring professor approval to change the schema

Secure in-database password hashing, full Unicode support, normal-length emails, multi-term enrollment, normalized subject selections, enforceable foreign keys, database unique constraints, persistent audit history, and transactional multi-table workflows.

There is deliberately no migration script for these changes in this package.
