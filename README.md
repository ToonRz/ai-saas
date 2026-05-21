# AI SaaS

A beginner-friendly AI SaaS web application that provides an **AI-powered text assistant** — summarize, rewrite, brainstorm, and more — with authentication, usage limits, subscription plans, and an admin panel.

## Tech stack

| Layer    | Technology        |
|----------|-------------------|
| Frontend | HTML, CSS, JavaScript, Bootstrap 5 |
| Backend  | PHP 8+ with PDO   |
| Database | MySQL 5.7+ / 8+   |
| AI API   | OpenAI (cURL)     |

## Project structure

```
ai-saas/
├── .env.example          # Environment template (copy to .env)
├── .gitignore
├── schema.sql            # MySQL database schema + seed data
├── README.md
│
├── index.php             # Landing page
├── login.php             # Login
├── register.php          # Register
├── logout.php            # Logout
├── dashboard.php         # User dashboard + AI interface
├── upgrade.php           # Subscription plans (mock payment)
├── admin.php             # Admin panel
│
├── api/
│   └── ai_request.php    # OpenAI API endpoint (JSON)
│
├── config/
│   ├── app.php           # .env loader
│   └── db.php            # PDO connection
│
├── includes/
│   ├── auth.php          # Sessions, usage helpers
│   ├── header.php        # Layout header + nav
│   └── footer.php        # Layout footer
│
└── assets/
    ├── css/style.css
    └── js/app.js         # AI form AJAX
```

## Features

1. **Authentication** — Register, login, logout with `password_hash()` and PHP sessions
2. **Dashboard** — Today's usage, subscription plan, all-time request count
3. **AI assistant** — Textarea prompt → OpenAI API → formatted response + history
4. **Usage control** — Daily limits per plan (Free: 20, Pro: 100, Premium: 500)
5. **Mock payments** — Upgrade between Free / Pro / Premium instantly
6. **Admin panel** — List users, view usage logs, ban/unban users

## Database tables

- `users` — accounts, roles, ban status, subscription
- `subscriptions` — Free, Pro, Premium plans
- `usage_logs` — per-request and upgrade events
- `ai_history` — saved prompts and responses

## Run locally

### Prerequisites

- PHP 8.0+ with extensions: `pdo_mysql`, `curl`, `json`
- MySQL 5.7+ or MariaDB 10.3+
- Optional: [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or PHP built-in server

### 1. Clone and configure environment

```bash
cd ai-saas
copy .env.example .env
```

Edit `.env` with your database credentials and OpenAI API key:

```env
DB_HOST=127.0.0.1
DB_NAME=ai_saas
DB_USER=root
DB_PASS=your_mysql_password

OPENAI_API_KEY=sk-your-real-key-here
OPENAI_MODEL=gpt-3.5-turbo
```

> **Demo mode:** If `OPENAI_API_KEY` is empty or still the placeholder, the app returns a mock response so you can test without billing.

### 2. Create the database

```bash
mysql -u root -p < schema.sql
```

Or import `schema.sql` via phpMyAdmin / MySQL Workbench.

### 3. Start the web server

**Option A — PHP built-in server (quickest)**

```bash
php -S localhost:8000
```

Open [http://localhost:8000](http://localhost:8000)

**Option B — XAMPP / Laragon**

Copy the project into `htdocs/ai-saas` (XAMPP) or Laragon's `www` folder, then open:

`http://localhost/ai-saas/`

### 4. Default accounts

| Role  | Email             | Password  |
|-------|-------------------|-----------|
| Admin | admin@example.com | password  |

Register a new user from the **Register** page to test the Free plan (20 requests/day).

## Security notes

- **SQL injection** — All queries use PDO prepared statements
- **Passwords** — Stored with `password_hash()` / verified with `password_verify()`
- **API keys** — Loaded from `.env` only (never commit `.env`)
- **Input validation** — Email format, password length, prompt length limits
- **Sessions** — Regenerated on login; banned users are logged out immediately

## Subscription plans

| Plan    | Daily AI requests | Price (mock) |
|---------|-------------------|--------------|
| Free    | 20                | $0.00        |
| Pro     | 100               | $9.99        |
| Premium | 500               | $29.99       |

Upgrade from **Plans** in the navigation bar. Payment is simulated — no Stripe integration yet.

## API endpoint

`POST /api/ai_request.php`

**Headers:** `Content-Type: application/json` (session cookie required)

**Body:**

```json
{ "prompt": "Summarize this article..." }
```

**Success response:**

```json
{
  "success": true,
  "response": "...",
  "usage": { "today": 1, "limit": 20, "remaining": 19 }
}
```

## Next steps (ideas)

- Replace mock payments with Stripe
- Add email verification and password reset
- Paginate AI history
- Rate-limit by IP for extra protection

## License

MIT — use freely for learning and projects.
