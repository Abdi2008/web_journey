# JOBSO — Simple Job Portal

A minimal job portal built with PHP, MySQL, HTML, CSS, and JavaScript intended to run under XAMPP (Windows).

Quick start

1. Copy the `JOBSO` folder into your XAMPP `htdocs` (example: `C:\xampp\htdocs\JOBSO`).
2. Start Apache and MySQL via XAMPP Control Panel.
3. Import the database:

   - Using phpMyAdmin: open `http://localhost/phpmyadmin`, create or import `db.sql`.
   - Or from PowerShell (MySQL must be in PATH):

```powershell
mysql -u root < "C:\xampp\htdocs\JOBSO\db.sql"
```

4. Open the site: `http://localhost/JOBSO/`

Notes

- Default DB credentials in `config.php` assume user `root` with empty password. Change if needed.
- Register a new user, then post jobs via `Post Job`.
- This is intentionally minimal for learning — consider adding CSRF protection, input validation improvements, and file uploads for a production site.

Files of interest

- `config.php` — DB connection
- `functions.php` — small helper functions
- `index.php` — job listing
- `register.php`, `login.php`, `logout.php` — authentication
- `post_job.php`, `view_job.php`, `dashboard.php` — job CRUD
- `css/style.css`, `js/app.js` — static assets


