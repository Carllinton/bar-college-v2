# Group demonstration and test checklist

Use a local copy with fictional information. The following creates demonstration rows through the application, not through modified SQL or a seed file.

## A fresh student journey through separate PHP pages

1. Open `index.php` and sign in as `admins` / `admins` from the original dump.
2. Open `Admin/accounts.php` → Add account (`Admin/account-create.php`). Use a short username, a unique 8–11 character test password, a display name of at most 20 characters, and the student role.
3. Open `Admin/students.php` → Add student profile (`Admin/student-create.php`). Select the account, choose a unique student number, enter the name, use program code `bsit`, and supply the year/section. Optional fields can remain blank. Use a short fictional email such as `a@bar.test` if demonstrating email.
4. Open Subjects. Review the original three subjects or create a new subject in `bsit`. Units must be a whole number from 1 to 12.
5. Sign out and sign in as the new student. Open `student/my-profile.php`, then Choose subjects (`student/enroll.php`). Select subjects and submit.
6. Verify that the request is pending and that its units match the actual catalog.
7. Sign in as administrator, open `Admin/enrollment-requests.php`, review the student's subjects, and approve the request.
8. Sign in as student again, open the record, and print it. Navigation and registrar controls must be absent from print preview.

## Programs, sections, and schedules

Create program `bsit` with a descriptive program name in the description. Create a section with program `bsit`, then a schedule referencing that section and a subject from the same program. Use registrar-supplied numeric room/instructor references. Explain that these are legacy references, not linked directories. Do not claim that schedule conflicts are automatically detected.

## Reviewing original problematic data

Open the old student's requests first. Discuss the duplicated records and wrong totals. Nothing changes merely by viewing them. If you choose to demonstrate cleanup, keep one pending request, explicitly remove the reviewed pending duplicates, and use Correct request on the retained record. Select the correct student and subjects, save the correction, then approve it. The orphaned pending record can be reviewed and removed separately.

Make these choices on a disposable classroom copy, or back up before intentional cleanup. No automatic data repair or deletion is provided.

## Manual failure checks

| Action | Expected result |
| --- | --- |
| Student opens an admin directory URL | 403 access restriction |
| Student guesses another student's enrollment ID | 404; no private record shown |
| Post without the form CSRF token | 419 form-expired response |
| Submit a long email | Clear 14-character error; no silent truncation |
| Save a repeated username or student number | Rejected |
| Student submits subjects from another program | Rejected on server |
| Modify posted units/student number/approval | Ignored; server owns these values |
| Submit the same enrollment twice | Second submission rejected |
| Student withdraws a pending request | Deleted; student may submit a new selection |
| Student withdraws an approved request | Rejected |
| Admin deletes a subject referenced by enrollment | Rejected |
| Admin deletes a linked account | Rejected |
| Enter HTML in a subject title | Displayed as text |
| Change password using wrong current password | Rejected |
| Open portal after signing out | Sign-in required |
| Resize to phone width | Menu button and readable responsive layout |
| Navigate using only keyboard | Visible focus and labeled controls |
| Print an enrollment | Record content only; no navigation or buttons |

## Automated checks

`php tests/unit.php` checks parsing, escaping, field lengths, roles, units, character support, password edit behavior, and date validation without connecting to a database.

`python3 tests/http_workflows.py` exercises HTTP forms on a separate disposable database and creates fictional data. It deliberately tests registrar correction/removal of some supplied pending rows and leaves approved test history. Never run it against your actual project database.

For a clean rerun, recreate only your disposable test database from the unchanged SQL. The application itself does not reset databases. The destructive test reset belongs to your local test harness, outside the runtime app.

## Folder-layout check

The main folder must contain exactly `conn.php`, `index.php`, `logout.php`, `main.css`, and `original.sql`. Confirm that navigation from `Admin/`, `student/`, and `pages/` loads the shared root stylesheet and assets correctly. Run `tests/navigation.py` against the disposable fixtures to check both links and protected file paths.
