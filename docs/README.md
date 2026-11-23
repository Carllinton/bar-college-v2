# BAR College · WinBAR Version 2 — corrected folder structure

The main folder contains **exactly five files**:

| File | Purpose |
| --- | --- |
| `conn.php` | Database connection entry point used by the application |
| `index.php` | Sign-in page |
| `logout.php` | POST-only sign-out action |
| `main.css` | All shared, responsive, and print styling |
| `original.sql` | Unchanged SQL from the professor's original archive |

All other files are in subfolders. The application still has separate working pages; their functions have not been removed.

| Folder | Contents |
| --- | --- |
| `Admin/` | Administrator dashboard; account/student/subject/program/section/schedule directories and forms; enrollment review and correction |
| `student/` | Student dashboard, profile, subject selection, and enrollment history |
| `pages/` | Shared account settings and printable enrollment record |
| `assets/` | JavaScript and the BAR College favicon |
| `app/` | Shared authentication, database class, validation, services, repositories, and templates |
| `config/` | Local database settings and the configuration example |
| `server/` | Local development server and Apache configuration |
| `tests/` | Validation, workflow, and navigation tests |
| `docs/` | This setup guide, structure guide, database contract, and verification |

## Quick local setup

Requirements: PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `ctype`, and sessions, plus a MySQL/MariaDB server supporting MyISAM. No Node, Composer, npm installation, CDN, or internet connection is needed to run the app.

1. Extract the `bar-college-v2` folder.
2. Start MySQL. If the original database already exists, use it. **Do not reimport the SQL into a populated database.** For a new empty installation only, create an empty `online_enroll` database, select it, and import `original.sql` unchanged.
3. Copy `config/app.example.php` to `config/app.php` and enter your host, port, database name, username, and password. Typical local XAMPP defaults are `127.0.0.1`, port `3306`, database `online_enroll`, username `root`, and an empty password; use your actual values.
4. From inside `bar-college-v2`, run:

```sh
php -S 127.0.0.1:8080 server/router.php
```

5. Open `http://127.0.0.1:8080/`.

On Windows, if PHP is not on PATH, use `C:\xampp\php\php.exe` in place of `php`. Keep MySQL running through XAMPP. The command above starts its own local web server, so Apache is not required for this option.

**Keep `server/router.php` in the command.** It permits the public pages and assets while blocking SQL/configuration/source downloads. It is a server access gatekeeper, not an application page dispatcher.

## Using XAMPP Apache instead

Because your main folder must contain only the five specified files, its Apache access configuration lives in `server/`.

1. Put the project at `C:\xampp\htdocs\bar-college-v2`.
2. Complete the database and `config/app.php` steps above.
3. Open `C:\xampp\apache\conf\httpd.conf` and add this line once:

```apache
Include "C:/xampp/htdocs/bar-college-v2/server/apache-bar-college.conf"
```

4. If your project is in another location, update both that Include path and the directory path inside `server/apache-bar-college.conf`.
5. Restart Apache. Open `http://localhost/bar-college-v2/`.
6. Verify that `http://localhost/bar-college-v2/original.sql` returns **403**. Open/import the SQL locally instead of through the website.

The supplied Apache configuration protects the root SQL and `conn.php`, disables directory listings, and enables the access restrictions already present in private subfolders. This configuration step is necessary when using Apache; PHP cannot intercept an SQL file that Apache serves directly.

Do not run a plain `php -S` command without the supplied gatekeeper, or expose this project with a server configuration that permits downloading the SQL.

## Original demonstration accounts

| Role | Username | Password |
| --- | --- | --- |
| Administrator | `admins` | `admins` |
| Student | `dennis` | `123456` |

These are the original SQL's accounts. No new accounts were inserted into the delivered dump. The supplied student has repeated enrollment requests and mismatched account/profile names, which remain for explicit registrar review.

## First walkthrough

Sign in as administrator. Create a student account in `Admin/account-create.php`, then link it to a profile in `Admin/student-create.php`. Use the same program code in the profile and subject catalog. Sign in as the student, select subjects in `student/enroll.php`, and submit. Return as administrator to review and approve the request. The student can then open and print the enrollment record.

Navigation, form actions, redirects, assets, and print links account for the new subfolders. The application works from a web root or a project subfolder such as `/bar-college-v2/` without manually editing a base URL.

## Database restriction

`original.sql` is byte-for-byte identical to the uploaded `online_enroll (2).sql`. No schema objects, engines, columns, indexes, or sample values were changed. No migrations or automatic cleanup run. Record changes occur through application forms and validated server-side CRUD.

The fixed database still has an 11-character plaintext password field, a 14-character email field, latin1 storage, MyISAM tables, comma-separated subject codes, and no semester or enrollment timestamp. Use fictional information and unique test passwords. This is a classroom reference; the fixed password field cannot hold standard secure password hashes.

## Documentation

- `MAIN_FOLDER_FILE_INVENTORY.md`: exact five-file root and folder responsibilities.
- `MODERNIZATION_GUIDE.md`: request flow and coding conventions.
- `DATABASE_CONTRACT.md`: fixed schema limitations and relationship protections.
- `DEMO_AND_TESTING.md`: demonstration and failure cases.
- `VERIFICATION.md`: completed checks and outstanding visual testing.

## Tests

From the main project folder:

```sh
php tests/unit.php
```

The HTTP workflow suite **writes fictional records and modifies selected pending sample requests**. Run it only against a disposable database import in a separate local installation:

```sh
python3 tests/http_workflows.py
```

The navigation suite reads the fictional fixtures created by that workflow suite:

```sh
python3 tests/navigation.py
```

Both HTTP scripts default to `http://127.0.0.1:8080/`. Set `BAR_TEST_URL` to the project folder URL when running elsewhere. Do not point them at your working group database.

## Troubleshooting

- **Portal cannot load:** check MySQL, database name/port, imported tables, and PHP extensions. An incident reference is written to the PHP error log without submitted passwords or connection details.
- **Email cannot save:** use a short valid fictional address such as `a@bar.test`, or leave it blank. The original limit is 14 characters.
- **Approval blocked for old records:** inspect pending duplicates, explicitly remove reviewed duplicates, correct the retained request, and then approve it.
- **Student cannot enroll again:** the database has no semester identifier; one enrollment per student is enforced. Pending requests can be withdrawn, while approved history stays protected.
- **A protected file returns 403:** expected. Use the portal pages; open configuration and SQL locally in your editor.
