# Version 2 verification — corrected five-file main folder

Date: November 6, 2026 (Asia/Manila).

## Completed

| Check | Result |
| --- | --- |
| PHP syntax checks across all pages and supporting code | Passed using PHP 8.3.6 |
| JavaScript syntax | Passed |
| Validation, parsing, escaping unit checks | 15 passed |
| Direct-page HTTP workflow checks | 108 passed using PHP 8.3.6 and MariaDB 10.11.7 |
| Exact main-folder file inventory | `conn.php`, `index.php`, `logout.php`, `main.css`, `original.sql` only |
| Public page relocation | 22 admin pages, 4 student pages, and 2 shared pages moved to their subfolders |
| Root CSS and relocated JavaScript/favicon | All served successfully |
| Navigation and link checks at web root | Passed: 513 assertions |
| Navigation and link checks in `/bar-college-v2/` | Passed: 513 assertions |
| Main-folder SQL and private source protection | HTTP 403 using the supplied development-server gatekeeper |
| SQL preservation | Exact SHA-256 match with the uploaded SQL |
| Database schema before/after workflow tests | No change to columns, types, defaults, keys, charsets, collations, or engines |
| PHP application log during HTTP tests | No application warnings, notices, fatal errors, or incident entries |

The 108 HTTP checks include CRUD for all six administrator directories, separate create/edit screens, original/new login, password changes, CSRF rejection, escaped output, field limits, schedule relationship guards, ownership and role checks, subject selection, server-owned enrollment identity/status/units, duplicate prevention, withdrawal, approval, pending correction/removal, protected approved history, and GET rejection for the logout action.

All test writes were submitted through the relocated physical PHP pages on a **disposable database import**. They did not modify the original ZIP, original SQL file, Version 1 package, or the user's installation. Test fixtures are not included in the delivered `original.sql`.

Original SQL SHA-256:

```text
4e4da37f246d5b237402baf6d808d88e3f93f3b6974aea288435ba96960ed932
```

## Verification still required locally

Desktop/mobile visual appearance, live JavaScript interactions, browser print preview, and PDF pagination have **not been visually verified for Version 2**. The earlier browser-launch attempt in this environment was blocked by unavailable socket operations. No browser screenshots are presented as evidence for this edition.

Responsive CSS, mobile navigation, unit preview, and print rules are implemented. Follow `DEMO_AND_TESTING.md` to verify them in your local browser. These checks are not a full accessibility audit.

XAMPP/Apache on Windows was not executed here. The PHP development-server access rules were tested. On Apache, include `server/apache-bar-college.conf` using the instructions in `docs/README.md` and verify the access restrictions locally; requesting `original.sql` through the website must return 403.

Public deployment, load testing, crash recovery, and secure credential storage are not certified. The unchanged 11-character plaintext password field remains a documented limitation.
