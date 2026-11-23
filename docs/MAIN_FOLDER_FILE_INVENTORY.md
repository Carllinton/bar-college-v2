# Corrected folder and file inventory

## Exactly five main-folder files

| File | Responsibility |
| --- | --- |
| `conn.php` | Establish the configured database connection when loaded by the application; reject direct HTTP access |
| `index.php` | Display sign-in and authenticate administrators/students |
| `logout.php` | Validate the session/form request and sign out through POST |
| `main.css` | Shared design, responsive layouts, accessibility focus styles, and printing |
| `original.sql` | Preserve the professor's SQL exactly for initial import |

No additional page, JavaScript, image, documentation, or configuration file is placed directly in the main folder. Subfolders contain those supporting files.

## Subfolders

| Folder | Responsibility |
| --- | --- |
| `Admin/` | 22 admin pages: dashboard, six CRUD directories, twelve create/edit forms, and enrollment directory/review/correction |
| `student/` | Four student pages: dashboard, profile, subject selection, enrollment history |
| `pages/` | Shared account settings and printable enrollment record |
| `assets/` | `app.js` and `favicon.svg` |
| `app/` | Shared PHP classes, services, repositories, bootstrap, and templates |
| `config/` | Local database configuration and its example |
| `server/` | Apache access configuration and the local development-server gatekeeper |
| `tests/` | Automated checks |
| `docs/` | Setup instructions, guides, inventory, and verification |

The original 29 screens and POST-only logout action remain functional. `conn.php` is now the additional root connection entry point. `page-inventory.json` lists the actual paths of the screens and logout action.

## Review path

Start at `index.php`. Use the navigation to reach the role-specific pages. Open the five main-folder files locally in your editor when reviewing their source. SQL should remain blocked from browser downloads.

For installation, read `docs/README.md`. Apache users must include the supplied server configuration because the root contains only the five requested files.

## SQL preservation

SHA-256 of both the original uploaded SQL and the delivered `original.sql`:

```text
4e4da37f246d5b237402baf6d808d88e3f93f3b6974aea288435ba96960ed932
```
