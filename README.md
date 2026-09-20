<p style="text-align: center;">
  <img src="public/assethub_logo_color.png" alt="AssetHub Logo" width="220" />
</p>

<h1 style="text-align: center;">AssetHub</h1>

<p style="text-align: center;">
  Central hub for IT assets - devices, SIM cards, servers, domains, and employees.
</p>

[Features](#features) • [Tech Stack](#tech-stack) • [Getting Started](#getting-started) • [Permissions](#permissions) • [Testing](#testing) • [License](#license)

> **Note:** Made for **SaurabhGroup IT Team**.

---

## Features

- **Companies** - multi-company scoping for all assets
- **Employees** - directory with current devices & SIM cards, assignment history
- **Devices**
  - Device types (laptop, desktop, monitor, etc.)
  - Device lifecycle + assign / return to employees
- **SIM Cards**
  - Track number, provider, status per company
  - Assign / return to employees with history
- **Servers**
  - Track name, type, IP, port (auto default port by type)
  - **Credentials vault** - store server credentials, reveal on demand (permission-gated)
  - **SSH keys** - store public/private keys, reveal on demand (permission-gated)
- **Domains** - track company domains, expiry, registrar
- **Users, Roles & Permissions** - powered by `spatie/laravel-permission`, enforced per route
- **Audit Logs** - automatic model auditing (`Auditable` trait), view / prune / delete
- **Dashboard + Auth** - login, registration, settings, Inertia SPA experience

## Tech Stack

- **Backend:** Laravel 12, PHP ^8.4, Laravel Octane (FrankenPHP)
- **Frontend:** Inertia.js v2 + React 19 + TypeScript, Tailwind CSS v4, Vite 6, Radix UI, Lucide
- **AuthZ:** `spatie/laravel-permission` ^8.3
- **Tooling:** Pest 3, Pint, Telescope, Boost, Ziggy

## Requirements

- PHP ^8.4 + `ext-pdo`
- Composer
- Node.js + pnpm
- SQLite (default) or MySQL/Postgres

## Getting Started

```bash
# 1. Install PHP + JS deps
composer install
pnpm install

# 2. Env
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite default)
touch database/database.sqlite
php artisan migrate --seed

# 4. Dev (server + queue + logs + vite)
composer run dev
# or individually:
# php artisan serve
# pnpm run dev

# 5. Build for production
pnpm run build
```

App runs at `http://localhost:8000` (via `php artisan serve`).

## Permissions

Routes are gated with `permission:*` middleware (see `routes/web.php`):

| Area | Permissions |
|------|-------------|
| roles | `roles.view`, `roles.create`, `roles.update` |
| users | `users.view`, `users.create`, `users.delete` |
| companies | `companies.view`, `companies.create` |
| domains | `domains.view`, `domains.create`, `domains.update` |
| employees | `employees.view`, `employees.create`, `employees.update` |
| devices | `device-types.view/create`, `devices.view/create` |
| sim-cards | `sim-cards.view`, `sim-cards.create` |
| servers | `servers.view`, `servers.create`, `credentials.view/create/delete`, `ssh-keys.view/create/delete` |
| audit-logs | `audit-logs.view`, `audit-logs.delete` |

Seed roles/permissions first if you have a seeder, otherwise assign via Tinker:

```bash
php artisan tinker --execute 'App\Models\User::first()->assignRole("admin");'
```

## Project Structure

```
app/
  Http/Controllers/   # Company, Employee, Device, SimCard, Server, Domain, …
  Models/             # Company, Employee, Device, DeviceAssignment, SimCard,
                      # SimAssignment, Server, ServerCredential, SshKey, Domain, …
  Enums/              # ServerType, …
routes/
  web.php             # auth-gated resource routes
  auth.php / settings.php
resources/js/pages/   # dashboard, companies, employees, devices, sim-cards,
                      # servers, domains, users, roles, audit-logs, …
database/
  migrations/ factories/ seeders/
tests/                # Pest feature + unit tests
public/
  assethub_logo_color.png
```

## Testing

```bash
php artisan test --compact
# single file:
php artisan test --compact tests/Feature/Http/Controllers/ServerControllerTest.php
# or:
vendor/bin/pest
```

Code style:

```bash
vendor/bin/pint --dirty
```

## License

MIT - see [LICENSE](LICENSE).

> Made for **SaurabhGroup IT Team**.
