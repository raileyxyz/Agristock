# AgriStock

**Smart Agriculture Inventory Management System** — a web-based inventory system for an agricultural business (farm supply store or farm) to track seeds, fertilizers, pesticides, equipment, and harvests, with built-in low-stock alerts, expiry monitoring, and reports.

> AgriStock is built for a **single business** (not multi-tenant).

## Features

**Inventory**

- Batch-based stock tracking with expiry dates
- **Stock In** with auto-generated batch numbers
- **Stock Out** with automatic **FEFO** (first-expiry-first-out) for expiry-tracked products and **FIFO** for the rest
- **Transfer** between storage locations
- **Stock Adjustment** that records the system quantity at the time of the adjustment
- Unified **Inventory History** (Stock In, Stock Out, Transfer, Adjustment)
- **Low Stock Monitoring** per product (reorder point and minimum stock)

**Products and suppliers**

- Products with auto-generated SKUs, categories, and units of measurement
- Suppliers linked to the categories they supply

**Reports and dashboard**

- Dashboard with stock statistics and charts
- Stock, Movement, and Expiry reports
- Inventory value shown on the dashboard (Admin and Manager only)
- Weekly and monthly report digests

**Notifications**

- In-app notification bell. Clicking a notification opens the page it refers to (for example, the expired batch)
- Optional email for important events (critical stock, expired products)
- Per-user notification preferences

**Users and security**

- Three roles: Admin, Manager, Staff
- Invite-based onboarding: the admin adds a user, and the user sets their own password from an emailed link
- Email verification (users cannot make changes until their email is verified)
- Archived and deleted accounts cannot log in or reset their password
- Creating an Admin account requires the acting admin's password
- Account deletion requests that an admin reviews

**Extras**

- AI chatbot assistant (OpenRouter) that answers questions about your inventory based on the user's role
- Dark mode

## Roles

| Capability                                           | Admin | Manager | Staff |
| ---------------------------------------------------- | :---: | :-----: | :---: |
| Dashboard                                            |  ✅   |   ✅    |  ✅   |
| View products, inventory, suppliers                  |  ✅   |   ✅    |  ✅   |
| Stock In / Stock Out                                 |  ✅   |   ✅    |  ✅   |
| Stock report, Expiry report                          |  ✅   |   ✅    |  ✅   |
| Create / edit products, add suppliers                |  ✅   |   ✅    |  ❌   |
| Edit stock, Stock Adjustment                         |  ✅   |   ✅    |  ❌   |
| Movement report and inventory value on the dashboard |  ✅   |   ✅    |  ❌   |
| User management and roles                            |  ✅   |   ❌    |  ❌   |

Permissions are defined in `config/abilities.php`, and user management rules are in `app/Policies/UserPolicy.php`.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+ (developed on 8.4), MySQL 8
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Chart.js, Lucide icons, Vite
- **Auth:** Laravel Breeze (customized)
- **Testing:** Pest
- **AI chatbot:** OpenRouter API

## Architecture

- Thin controllers that delegate to a service class per module (`app/Services`)
- Form Requests for validation, Policies and Gates for authorization
- Events and listeners for notifications (`app/Events`, `app/Listeners`)
- Scheduled commands for expiry checks and report digests (`app/Console/Commands`, `routes/console.php`)

## Getting Started

### Requirements

PHP 8.2+, Composer, Node.js and npm, MySQL 8

### Setup

```bash
git clone https://github.com/raileyxyz/Agristock.git
cd Agristock

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `agristock`, then check the `DB_*` and `APP_URL` values in `.env`:

```bash
php artisan migrate --seed
php artisan storage:link
```

### Run the app

If you use [Laravel Herd](https://herd.laravel.com), put the project in a Herd-linked folder and open `http://<folder-name>.test` (set `APP_URL` to match). Otherwise:

```bash
php artisan serve        # http://localhost:8000
```

In separate terminals:

```bash
npm run dev              # Vite (use `npm run build` for production assets)
php artisan queue:work   # sends alert emails; required for queued emails
php artisan schedule:work  # runs the daily/weekly/monthly scheduled commands (development)
```

Or run the server, queue, logs, and Vite together with `composer dev` (this includes `php artisan serve`).

### Default admin account

The seeder creates one Admin account:

| Email             | Password   |
| ----------------- | ---------- |
| `admin@gmail.com` | `admin123` |

**Change this password immediately**, and never keep the default credentials on a real deployment.

### Email

By default, emails are written to `storage/logs/laravel.log` (`MAIL_MAILER=log`). To send real email, set up SMTP in `.env` (see the commented Gmail and Mailtrap examples in `.env.example`). Alert emails are queued, so keep `php artisan queue:work` running.

### AI chatbot

Add your key to `.env` to enable the chatbot:

```env
OPENROUTER_API_KEY=your-key
OPENROUTER_MODEL=openrouter/free
```

Get a key at <https://openrouter.ai/keys>.

## Scheduled Tasks

| Command                    | Schedule               | Purpose                                     |
| -------------------------- | ---------------------- | ------------------------------------------- |
| `inventory:check-expiring` | Daily                  | Notifies about expiring and expired batches |
| `reports:weekly-digest`    | Mondays 00:00          | Weekly report notification                  |
| `reports:monthly-digest`   | 1st of the month 00:00 | Monthly report notification                 |

In production, run `php artisan schedule:run` every minute from cron, and run the queue worker under a process manager such as Supervisor.

## Testing

```bash
php artisan test
```

Tests use an in-memory SQLite database, configured in `phpunit.xml`.

## License

This project was built for educational purposes.
