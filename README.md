# 🎫 Helpdesk & Ticketing System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-Passing-success?style=for-the-badge&logo=checkmarx&logoColor=white)]()

A modern, responsive, web-based **Internal Helpdesk & Ticketing System** built with **Laravel**, **Tailwind CSS**, and **Alpine.js**.

This platform replaces unstructured support requests (such as instant messages, spreadsheets, or direct emails) with a centralized, auditable, and transparent service management workflow across multiple corporate departments (*IT Support, General Affairs, Human Resources, Finance, Facility Management*).

---

## 📑 Table of Contents

- [Key Features](#-key-features)
- [User Roles & Access Control](#-user-roles--access-control)
- [System Requirements](#-system-requirements)
- [Quick Start Installation](#-quick-start-installation)
- [Pre-configured Demo Accounts](#-pre-configured-demo-accounts)
- [Ticket Lifecycle & SLA Matrix](#-ticket-lifecycle--sla-matrix)
- [Project Architecture](#-project-architecture)
- [Automated Testing](#-automated-testing)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Key Features

- **Comprehensive Ticket Lifecycle**:
  - Dynamic statuses: `Open`, `In Progress`, `Pending`, `Resolved`, and `Closed`.
  - Priority levels: `Low`, `Medium`, `High`, and `Urgent`.
  - Automatic standardized ticket code generator (e.g., `HD-2026-000101`).
- **Automated SLA Tracking & Indicators**:
  - Configurable SLA thresholds for both **First Response Target** and **Resolution Target**.
  - Real-time visual status badges: `Safe`, `Near Deadline`, and `Overdue`.
- **Collaborative Messaging & Private Notes**:
  - **Public Replies**: Seamless conversation between requester and assigned agents.
  - **Confidential Internal Notes**: Staff-only discussion and internal diagnosis hidden from requesters.
- **Audit Trail & Activity Logging**:
  - Chronological history tracking assignments, status updates, priority adjustments, and resolutions.
- **Secure File Attachments**:
  - Support for screenshots, error logs, and PDF documentation with MIME validation and secure access control.
- **Role-Tailored Dashboards**:
  - **Admin**: Global analytics, department volume, SLA compliance, and system activity.
  - **Agent**: Personal task queue (*Assigned to Me*), unassigned tickets, and near-deadline warnings.
  - **User**: Real-time status overview of submitted requests and recent resolution updates.
- **Reporting & Data Export**:
  - Comprehensive filters by date range, department, category, status, and priority.
  - One-click **CSV export** for audits, metrics analysis, and KPI reporting.
- **Master Data Administration**:
  - Full CRUD control for Users, Roles, Departments, Categories, and SLA response/resolution hours.

---

## 👥 User Roles & Access Control

| Role | Permissions & Responsibilities |
|---|---|
| **Administrator** | Full system governance, user management, department/category setups, SLA settings, and visibility into all tickets. |
| **Supervisor** | Departmental oversight, assigning tickets to agents, modifying priority levels, and monitoring SLA compliance. |
| **Agent / Support Staff** | Handling assigned tickets, posting public replies & internal notes, updating statuses, and resolving issues. |
| **User / Requester** | Submitting new support tickets, monitoring ticket progress, communicating with agents, and downloading public attachments. |

---

## 💻 System Requirements

Before running the application, ensure the following tools are installed on your system:

- **PHP** >= 8.3 (Required extensions: `pdo`, `sqlite3` or `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM** >= 9.x
- **Database**: SQLite (default, zero configuration required) or MySQL 8.0+ / MariaDB 10.4+ / PostgreSQL

---

## 🚀 Quick Start Installation

Follow these steps to clone, configure, and run the project locally:

### 1. Clone the Repository

```bash
git clone https://github.com/tubaguskencana/helpdesk.git
cd helpdesk
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Setup Environment Configuration

Copy the example environment configuration:

```bash
# Windows PowerShell / Command Prompt
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Generate the unique application encryption key:

```bash
php artisan key:generate
```

### 5. Database Setup & Seeding

The application comes pre-configured with **SQLite** for instant setup without requiring an external database server.

#### Option A: Using SQLite (Recommended for Local Dev)

Ensure the SQLite database file exists:

```bash
# Windows PowerShell
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }

# Linux / macOS
touch database/database.sqlite
```

Run database migrations along with the pre-populated demo seeders:

```bash
php artisan migrate --seed
```

#### Option B: Using MySQL / MariaDB

1. Adjust your `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=helpdesk_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```
2. Create the `helpdesk_system` database in MySQL.
3. Execute migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

### 6. Create Storage Symlink

Required to make uploaded ticket attachments securely downloadable:

```bash
php artisan storage:link
```

### 7. Compile Assets

Build the frontend assets using Vite:

```bash
# Compile optimized production bundle
npm run build

# Or launch hot-reloading development server
npm run dev
```

### 8. Start Local Development Server

Run the Laravel Artisan server:

```bash
php artisan serve
```

Access the application in your browser at:
👉 **[http://localhost:8000](http://localhost:8000)**

### 9. (Optional) Start WhatsApp Gateway Daemon (`gwa-api`)

For real-time WhatsApp ticket notifications, start the included Baileys WhatsApp service:

```bash
# In a separate terminal
cd gwa-api
npm install
npm start
```

1. The gateway listens on `http://localhost:3000`.
2. Open the Helpdesk admin panel at **Admin -> WhatsApp Integration** (`/admin/notifications/whatsapp`) to scan the QR code and link your WhatsApp number.

---

## 🔐 Pre-configured Demo Accounts

Once you run `php artisan migrate --seed`, you can log in immediately using any of these seeded accounts:

> **Default password for all accounts:** `password`

| Role | Name | Email | Department | Purpose / Scope |
|---|---|---|---|---|
| **Admin** | Administrator System | `admin@helpdesk.test` | System / IT | Full administrative control |
| **Supervisor** | Alex Pratama | `supervisor@helpdesk.test` | IT Support | Team management & ticket dispatch |
| **Agent IT** | Budi Santoso | `agent.it@helpdesk.test` | IT Support | Senior support technician |
| **Agent IT** | Citra Lestari | `citra.it@helpdesk.test` | IT Support | Network & systems specialist |
| **Agent GA** | Doni Wijaya | `agent.ga@helpdesk.test` | General Affairs | Facilities & logistics coordinator |
| **User (Finance)** | Sarah Jenkins | `user@helpdesk.test` | Finance & Accounting | Requester (Financial Analyst) |
| **User (HR)** | Kevin Hartanto | `kevin@helpdesk.test` | Human Resources | Requester (People Operations) |
| **User (Finance)** | Maya Putri | `maya@helpdesk.test` | Finance & Accounting | Requester (Accounts Payable) |

---

## 🔄 Ticket Lifecycle & SLA Matrix

### Ticket Workflow Overview:

```text
[Requester / User]
       │  (1. Submit New Ticket)
       ▼
 [Status: Open]
       │
       ├───────────────────────────────────────────────┐
       ▼                                               ▼
[Supervisor / Admin]                           [Agent Pick Up]
 (2. Assign to Agent)                          (3. Update Status)
       │                                               │
       └───────────────────────┬───────────────────────┘
                               ▼
                    [Status: In Progress]
                               │
                 ┌─────────────┴─────────────┐
                 ▼                           ▼
          [Public Reply]              [Internal Notes]
       (Visible to Requester)       (Staff-Only Visibility)
                 │                           │
                 └─────────────┬─────────────┘
                               ▼
                    [Status: Resolved]
                               │
                               ▼
                     [Status: Closed]
```

### Default SLA Targets:

| Priority | First Response Target | Resolution Target |
|---|---|---|
| **Urgent** | 1 Hour | 8 Hours |
| **High** | 4 Hours | 24 Hours |
| **Medium** | 8 Hours | 48 Hours |
| **Low** | 24 Hours | 72 Hours |

---

## 📁 Project Architecture

```text
helpdesk-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/               # User, Department, Category, SLA Controllers
│   │   │   ├── AuthController.php   # Login, Session, Profile Management
│   │   │   ├── DashboardController.php
│   │   │   ├── ReportController.php # Performance analytics & CSV exports
│   │   │   ├── TicketController.php # Ticket lifecycle and assignments
│   │   │   └── TicketReplyController.php
│   │   └── Middleware/
│   │       ├── EnsureAdmin.php      # Administrator route guard
│   │       └── EnsureStaff.php      # Staff (Agent/Supervisor/Admin) route guard
│   ├── Models/                      # Eloquent models (Ticket, User, SlaSetting, etc.)
│   ├── Policies/                    # Authorization policies (TicketPolicy)
│   └── Services/
│       └── TicketService.php        # Business logic for SLA tracking & state transitions
├── database/
│   ├── migrations/                  # Relational database schemas
│   └── seeders/                     # Master data and sample ticket seeders
├── resources/
│   ├── css/app.css                  # Tailwind CSS configuration
│   ├── js/app.js                    # Alpine.js initialization
│   └── views/                       # Blade components and view layouts
├── routes/
│   └── web.php                      # Application routing definitions
└── tests/
    └── Feature/
        └── HelpdeskSystemTest.php   # Automated feature and integration tests
```

---

## 🧪 Automated Testing

The project includes an end-to-end suite of automated tests using **PHPUnit** verifying authentication, RBAC authorization, ticket creation, SLA calculations, and internal notes confidentiality:

```bash
# Run all test suites
php artisan test

# Run the core helpdesk feature tests
php artisan test --filter=HelpdeskSystemTest
```

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to your branch (`git push origin feature/amazing-feature`)
5. Open a **Pull Request**

---

## 📄 License

This project is open-sourced under the [MIT License](LICENSE).
