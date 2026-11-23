# BAR College · Vercel deployment

Use branch `deploy/vercel`, keeping the professor's five-root-file `main` intact. This deployment does not change `original.sql` or the database schema.

## Persistent services

1. Provision externally accessible MySQL/MariaDB supporting MyISAM, GET_LOCK and RELEASE_LOCK. For an **empty** database only, import the unchanged `original.sql` from a local SQL client. Never reimport onto an existing populated database. On Railway, use the **public** TCP host and port, not its private `*.railway.internal` hostname.
2. Provision Redis accessible from Vercel, preferably using TLS `rediss://`. This stores login sessions and CSRF tokens across stateless Vercel instances.
3. Restrict the deployment to fictional classroom records. Original sample logins and the legacy 11-character plaintext password schema are unsafe for public real-world data. Enable Deployment Protection or equivalent access control.

## Connect Vercel

1. Import GitHub repo `Carllinton/bar-college-v2` at https://vercel.com/new.
2. Select branch `deploy/vercel` for production deployment (Project Settings > Git > Production Branch), root directory `./`. Vercel recognizes the root `Dockerfile.vercel` and runs the PHP/Apache container.
3. Set these Production environment variables, using credentials from your hosting provider:
   - `DB_HOST`: external MySQL host
   - `DB_PORT`: external MySQL port, e.g. 3306 or a provider-assigned port
   - `DB_NAME`: imported database name
   - `DB_USER`: MySQL username
   - `DB_PASSWORD`: MySQL password (secret)
   - `SESSION_REDIS_URL`: `rediss://user:password@host:port/0` (secret; URL-encode reserved password characters)
4. Deploy, then view logs and verify functions.

The Dockerfile sets `APP_ENV=production`; do not set `DB_HOST` to localhost. No build command or `vercel.json` is necessary.

## Verify

- `/` should display sign-in; `/main.css` and `/assets/app.js` should load.
- Requests for `/original.sql`, `/conn.php`, `/app/start.php`, `/config/app.example.php`, and `/docs/README.md` must be denied.
- A fictional admin/student login should survive reloads, cross-page navigation, and redirects; logout must invalidate session.
- Run CRUD checks only on a disposable demo database.
- If the container build fails, check PHP extension installation. If requests fail, inspect Vercel runtime logs, MySQL network access, Redis service, and env variables.

## Limits

- No durable files inside the Vercel container; records persist in MySQL and sessions in Redis.
- The original schema uses plaintext passwords with an 11-character column; do not deploy with real student data or reused passwords.
- Hosted services can incur fees. Check provider TLS options and billing.
- Do not merge this branch into `main` while the professor's strict root file count is in force.
