# Task Tracker

A full-stack **Task Tracker** web application built for **CSE 471 – Web Programming**.
Registered users can securely manage a personal to-do list — create, edit, delete,
and complete tasks — with search, filtering, and sorting. The app demonstrates
authentication, full CRUD, and core web-security practices.

---

## Tech Stack

| Layer     | Technology                                   |
|-----------|----------------------------------------------|
| Frontend  | HTML5, CSS3, vanilla JavaScript (no frameworks, no build tools) |
| Backend   | PHP (PDO for all database access)            |
| Database  | MySQL                                         |
| Hosting   | XAMPP / PHP built-in server (local), any PHP + MySQL host (live) |

---

## Features

- **Authentication**
  - Registration with server-side validation and a unique-email constraint
  - Passwords hashed with `password_hash(..., PASSWORD_DEFAULT)` (bcrypt)
  - Login via `password_verify()`, logout fully destroys the session
- **Task CRUD**
  - Create tasks (title, description, priority, due date)
  - Read tasks in a clean table with priority/status badges
  - Update any field via an edit form
  - Delete with a JavaScript confirmation prompt
  - One-click toggle between **pending** and **done**
- **Search / Filter / Sort**
  - Keyword search by task title
  - Filter by status (All / Pending / Done)
  - Sort by newest, due date (soonest/latest), or priority
- **Security & Validation**
  - PDO **prepared statements** everywhere (SQL-injection safe)
  - **Server-side** validation on every form, plus **client-side** validation
  - All output escaped with `htmlspecialchars()` (XSS safe)
  - **CSRF tokens** on every form
  - **Session-based** access control; strict per-user data scoping
- **UX**
  - One clean, modern, mobile-responsive stylesheet (no CSS frameworks)
  - Dashboard with task counts on the home page

---

## Folder Structure

```
task-tracker/
├── config/
│   └── db.php              # PDO connection (edit DB credentials here)
├── includes/
│   ├── header.php          # Shared header + navigation
│   ├── footer.php          # Shared footer
│   ├── auth_check.php      # Session guard for protected pages
│   └── csrf.php            # CSRF token helpers
├── css/
│   └── style.css           # Single responsive stylesheet
├── js/
│   └── main.js             # Client-side validation + interactivity
├── database/
│   └── schema.sql          # Full schema + sample seed data
├── index.php               # Home / landing + dashboard
├── register.php            # Registration form
├── login.php               # Login form
├── logout.php              # Destroys the session
├── tasks.php               # Main CRUD page (+ search/filter/sort)
├── about.php               # Project description + team table
├── README.md
└── .gitignore
```

---

## Local Setup (XAMPP) — Step by Step

1. **Install & start XAMPP.** Download XAMPP, open the **XAMPP Control Panel**,
   and click **Start** for both **Apache** and **MySQL**.

2. **Create the database and import the schema.**
   - Open <http://localhost/phpmyadmin> in your browser.
   - Click the **Import** tab at the top.
   - Click **Choose File** and select `database/schema.sql` from this project.
   - Scroll down and click **Go**.
   - This creates the `task_tracker` database, both tables, and the sample data.
     *(The script also creates the database itself, so you do not need to create
     it manually first.)*

3. **Configure the database credentials.**
   - Open `config/db.php` in a text editor.
   - The variables are clearly commented at the top. On a default XAMPP install,
     the defaults already work:
     ```php
     $DB_HOST = 'localhost';
     $DB_NAME = 'task_tracker';
     $DB_USER = 'root';
     $DB_PASS = '';        // empty for default XAMPP
     ```
   - Change `$DB_USER` / `$DB_PASS` only if your MySQL uses different credentials.

4. **Deploy into htdocs and open the app.**
   - Copy/move this whole folder into XAMPP's `htdocs` directory and name it
     `task-tracker` (e.g. `C:\xampp\htdocs\task-tracker`).
   - Visit **<http://localhost/task-tracker>** in your browser.

### Alternative: PHP built-in server (no Apache)

If you have PHP and MySQL installed directly:

```bash
# 1. Import the schema
mysql -u root -p < database/schema.sql

# 2. From the project root, start the server
php -S localhost:8000

# 3. Open http://localhost:8000
```

---

## Demo Login

| Field    | Value              |
|----------|--------------------|
| Email    | `demo@example.com` |
| Password | `Demo@1234`        |

The demo account comes preloaded with 5 sample tasks. You can also register a
brand-new account from the **Register** page.

---

## Deployment (Free Live Hosting)

You can host the app for free on a PHP + MySQL provider such as
**[InfinityFree](https://infinityfree.net)** (or 000webhost, AwardSpace, etc.):

1. **Create a free hosting account** at InfinityFree and create a new site.
2. **Create a MySQL database** from the control panel (vPanel → *MySQL Databases*).
   Note the generated **database name, username, host, and password**.
3. **Import the schema:** open **phpMyAdmin** from the panel, select your new
   database in the left sidebar, use the **Import** tab, and upload
   **`database/schema_hosting.sql`**. This hosting-ready file omits the
   `CREATE DATABASE` / `USE` lines (free hosts like InfinityFree forbid them),
   so it imports straight into the database you created in step 2.
4. **Edit `config/db.php`** with the host-provided `$DB_HOST`, `$DB_NAME`,
   `$DB_USER`, and `$DB_PASS`.
5. **Upload all project files** to the `htdocs` (public) folder via the host's
   **File Manager** or **FTP** (e.g. FileZilla).
6. Visit your assigned domain to confirm everything works, then update the
   **Live Website Link** below.

---

## Links

- **Live Website Link:** [Live Website Link]
- **GitHub Repo Link:** https://github.com/faridislam-me/task-tracker

---

## Group Members

- **Group ID:** [your group ID]

| #  | Name        | Student ID |
|----|-------------|------------|
| 1  | [member 1]  | [ID 1]     |
| 2  | [member 2]  | [ID 2]     |
| 3  | [member 3]  | [ID 3]     |
| 4  | [member 4]  | [ID 4]     |

---

## Notes

- Never commit real database passwords — `config/db.php` holds local-only
  development defaults. See `.gitignore`.
- Passwords are never stored in plaintext; only bcrypt hashes are saved.
