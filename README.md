# Login System

A secure PHP 8 login and registration system with a full CRUD dashboard, built with **Laravel 13**, **Tailwind CSS**, and **Svelte 5**.

---

## Features

### Authentication
- User registration and login using **session-based authentication** (Laravel Breeze)
- Secure password hashing via `password_hash()` (bcrypt through Laravel's `Hash::make()`)
- Password verification via `password_verify()` (Laravel's `Hash::check()`)
- Session encryption, HttpOnly cookies, and SameSite=Lax protection
- Logout with full session invalidation

### CRUD Dashboard
- **Create** new user profile records
- **Read** and display records in a searchable table
- **Update** existing records with pre-filled forms
- **Delete** records with a **Svelte-powered confirmation modal**

### Security
| Measure | Implementation |
|---|---|
| SQL Injection | PDO prepared statements with named parameters (`getPdo()` + `prepare()`) |
| XSS | Blade `{{ }}` auto-escaping + `e()` helper on raw PDO results |
| CSRF | Laravel's built-in `@csrf` token on every form |
| Session | Encrypted sessions stored in DB, HttpOnly + SameSite cookies |
| Authorization | All CRUD routes gated by `auth` middleware; ownership checked on every query |

### Client-Side Validation
Alpine.js validates forms before submission:
- Required field checks (First Name, Last Name, Email)
- Email format regex check
- Phone number format check

### Role-Based Access Control (Bonus)
- Two roles: `user` (default) and `admin`
- Admin panel at `/admin` — view all users and all records across the system
- Admins can promote/demote other users
- Protected by `AdminMiddleware`

### Error Handling
- All PDO operations wrapped in `try/catch (\PDOException)`
- Errors logged server-side via `Log::error()` — no stack traces exposed to users
- User-friendly flash messages for success, error, and validation failures

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Tailwind CSS 3, Alpine.js 3, Svelte 5 |
| Database | MySQL 8 |
| Build Tool | Vite 7 |
| Auth Scaffold | Laravel Breeze (Blade stack) |

---

## Setup Instructions

### Prerequisites
- PHP 8.2+ with extensions: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `zip`, `curl`
- Composer
- Node.js 18+ and npm
- MySQL 8+

### 1. Clone the repository
```bash
git clone https://github.com/jossrazo/login-system.git
cd login-system
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Import the database schema
**Option A — Laravel migrations (recommended):**
```bash
php artisan migrate
```

**Option B — SQL file:**
```bash
mysql -u your_username -p your_database < schema.sql
```

### 5. Run the application
```bash
# Terminal 1 — PHP backend
php artisan serve

# Terminal 2 — Vite frontend
npm run dev
```

Open **http://127.0.0.1:8000** in your browser.

### 6. Create an admin user (optional)
After registering a user, promote them to admin via MySQL:
```sql
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```
Then navigate to `/admin` after logging in.

---

## Project Structure

```
app/
  Http/
    Controllers/
      AdminController.php      ← Admin panel (RBAC, view all records/users)
      RecordController.php     ← CRUD with explicit PDO prepared statements
      ProfileController.php    ← User profile management (Breeze)
    Middleware/
      AdminMiddleware.php      ← Restricts /admin routes to role='admin'
  Models/
    User.php                   ← role field, isAdmin() helper
    Record.php                 ← Profile record model

resources/
  js/
    app.js                     ← Svelte 5 component mounting logic
    components/
      DeleteConfirm.svelte     ← Animated delete confirmation modal
  views/
    admin/index.blade.php      ← Admin panel view
    records/
      index.blade.php          ← CRUD table with search
      create.blade.php         ← Create form with Alpine.js validation
      edit.blade.php           ← Edit form with Alpine.js validation

database/
  migrations/                  ← All table definitions
schema.sql                     ← Exported SQL schema for reference
```


