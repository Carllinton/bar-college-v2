# Version 2 structure and coding conventions

## Corrected structure

The main folder contains only `conn.php`, `index.php`, `logout.php`, `main.css`, and `original.sql`, following your corrected requirement. Administrator pages live in `Admin/`, student pages in `student/`, shared settings/printing in `pages/`, and JavaScript/images in `assets/`. This guide is under `docs/`.

Each page still contains meaningful request handling and reuses the services and templates in `app/`. `conn.php` is loaded by the shared application object to instantiate the PDO database wrapper. Database settings remain in `config/app.php`.

The shared URL helper derives the project's URL prefix from the executing script, so links work from nested pages and when the whole project is installed in an XAMPP subfolder.

## Follow a request

For adding a subject:

1. A link opens `Admin/subject-create.php` directly.
2. The file loads `app/start.php`, which initializes the session, PDO connection, shared dependencies, CSRF protection, and error handler.
3. The page requires an administrator account and fixes its resource to `subjects` in server code.
4. On GET, it renders the shared subject form.
5. On POST, `Registry::save()` validates the fields and relationships under an application write lock.
6. `Records::save()` inserts explicitly named columns with prepared values.
7. The page redirects to `Admin/subjects.php` using HTTP 303 and shows a success message.

For editing, the URL is `subject-edit.php?id=4`. The ID is validated from the URL. A posted resource name cannot change which table the page manages.

The `url()` helper builds ordinary filenames and query strings. It does not load pages or dispatch requests. `index.php` is the sign-in page only.

## Naming conventions

| Item | Convention | Example |
| --- | --- | --- |
| Page file | Lowercase descriptive words separated by hyphens | `Admin/enrollment-review.php` |
| Class and class file | PascalCase, matching names | `Enrollment.php` / `Enrollment` |
| Methods and PHP variables | camelCase | `removePending`, `$profileId` |
| Namespace | Match the supporting directory | `BarCollege\Services` |
| Database names | Preserve original spelling | `studenttbl`, `Instructor_ID` |
| CSS classes | Lowercase words separated by hyphens | `.page-heading` |
| JavaScript variables | camelCase, `const` by default | `unitCount` |
| Source indentation | Four spaces | PHP/JS/CSS source |
| Pure PHP files | Strict types; omit the closing PHP tag | Page controllers and services |

Database naming cannot be cleaned up without violating the assignment. Adapt at the application boundary rather than renaming columns.

## Responsibilities

| Layer | What belongs here | What to avoid |
| --- | --- | --- |
| Page controller | HTTP method, role, fixed resource, form handling, data preparation | Duplicated business rules |
| Application/core | Shared initialization and security checks | A hidden replacement for all page controllers |
| Service | Validation, uniqueness, relationship and enrollment rules | HTML markup |
| Repository | Prepared queries and explicit persistence | Decisions based on browser-supplied SQL identifiers |
| View | Escaped presentation and labeled forms | SQL and authority decisions |
| CSS/JS | Shared appearance and browser convenience | Trusting the browser for totals, roles, or ownership |

Six directories share a form template because their structure is similar. Enrollment is a dedicated workflow because selecting, reviewing, and approving subjects has additional rules.

## Security and correctness

- Each privileged page verifies the role before loading protected records.
- Student identity comes from the authenticated account, never a posted student number.
- Every mutation uses POST and a valid CSRF token.
- User data is bound to prepared queries and escaped with `e()` when rendered.
- SQL identifiers come from server-owned resource definitions.
- Form lengths reflect the fixed schema; overlong input is rejected rather than truncated.
- New enrollment units are calculated from the actual subject catalog.
- Subject selections are checked against the student's program.
- Referenced profiles, accounts, subjects, programs, and schedules have deletion/update guards.
- `logout.php` is a POST-only action; following a GET link does not log the user out.
- SQL and private source files remain inaccessible through the supported server setups, even though the SQL resides in the main folder. Apache users must include server/apache-bar-college.conf; the local development command must use server/router.php.

The existing 11-character plaintext password column remains a material security limitation. The application uses compatible test credentials, not standard password hashing. The login throttle is session-based, so it does not provide production-grade protection against distributed or cookie-reset attempts.

References: [PDO prepared statements](https://www.php.net/manual/en/pdo.prepare.php), [PHP password hashing](https://www.php.net/manual/en/function.password-hash.php), [MySQL nontransactional rollback limits](https://dev.mysql.com/doc/refman/8.4/en/nontransactional-tables.html).

## MyISAM and separate account/profile steps

The database uses MyISAM, so transaction rollback cannot make a two-table operation atomic. Account creation and student-profile creation are separate, single-row operations. A profile requires an existing student account, and a linked account cannot be deleted until the profile is removed. Profiles with enrollment history cannot be deleted.

Application writes share a named advisory lock. This prevents concurrent requests through this code from both passing the same uniqueness check. Other applications or direct SQL that ignore the lock can still introduce inconsistencies. These are application checks, not foreign keys or new database constraints.

## Design conventions

The BAR College design uses deep green, lime accents, warm neutral backgrounds, local assets, and a shared brand mark. Styling stays in `main.css`. Forms have associated labels, keyboard focus styles, and server validation. The print stylesheet hides the portal navigation and controls.

JavaScript improves convenience. Core form submission, permissions, and unit calculation do not depend on it. The individual pages share navigation so users can move through the system without typing file URLs.

## Group work suggestion

| Member | Focus | Pages to demonstrate |
| --- | --- | --- |
| 1 | Authentication and setup | `index.php`, `pages/account-settings.php`, `logout.php` |
| 2 | Accounts and students | Account/student directory, create, and edit files |
| 3 | Curriculum and scheduling | Subject/program/section/schedule files |
| 4 | Enrollment | Selection, history, review, correction, printable record |
| 5 | Shared design and QA | Dashboards, `main.css`, `app.js`, file inventory and tests |

Use the file inventory to explain the five-file main folder and the responsibilities of the supporting subfolders.

## Extending the reference

Describe the behavior, check that the existing schema supports it, add a purposeful page in the appropriate subfolder if necessary, reuse services and views, enforce permissions, then test both success and failure paths. Keep the SQL unchanged.

If you adopt a PHP formatter, explicitly target PHP 8.2 (the minimum supported syntax) and inspect mixed PHP/HTML templates after formatting. A tool's default target can be newer than the PHP version installed in XAMPP.

Future semester history, normalized enrollment details, modern in-database password hashes, longer emails, full Unicode, foreign keys, persistent audit history, and transactional multi-table writes require schema changes. No migration script for those changes is included.
