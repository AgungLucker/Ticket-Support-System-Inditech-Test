# Ticket Support System

A role-based support ticket management system built with Laravel 11 as a project-based internship test at Inditech. Customers can submit tickets, Agents handle the customer's ticket, Supervisors coordinate the teams of agents, and Admins manage everything from users to SLA rules. The system enforces strict authorization boundaries, tracks the full ticket lifecycle with auditable activity logs, and delivers async notifications through Laravel Queues.

## Project Overview

Ticket Support System is a support desk application for managing customer support requests from creation to resolution. The system is organized around four roles with clearly separated responsibilities: Customers open tickets and follow conversations, Agents work on tickets assigned to them, Supervisors monitor their team's workload and escalations, and Administrators have full operational control over the entire system including users, teams, categories, priorities, SLA rules, and all tickets.

Authorization is enforced at the policy layer rather than hidden behind UI conditionals, status transitions are centrally validated so the workflow cannot be bypassed by crafted requests, and all significant actions on a ticket are recorded in an activity log.

## Features

### Role-Based Access Control

Authorization is implemented through Laravel Policies and Gates, enforced on every controller action.

**Customer** creates and views only their own tickets, posts public comments, and can reopen a resolved or closed ticket. **Agent** works only on tickets assigned to them, updates status within allowed transitions, and can write internal notes invisible to the customer. **Supervisor** manages tickets scoped to agents in their team, can reassign tickets, and monitors team-level statistics on the dashboard. **Admin** has full system access and bypasses ticket-level policy checks via the `before()` hook in `TicketPolicy`.

### Ticket Status Workflow

Eight statuses with controlled transitions managed by `TicketStatusService`:

```
Open → Assigned → In Progress → Resolved → Closed
              ↓                       ↑
          Escalated              Reopened
                     Waiting for Customer
```

The service is the central place that determines which transitions are valid and which roles can trigger them, keeping the logic out of individual controllers. Timestamps (`resolved_at`, `closed_at`) are set and cleared automatically when the status changes.

### SLA Rules and Overdue Detection

SLA due dates are calculated automatically when a ticket is created, based on the priority's configured resolution and response time. The `TicketService` looks up the SLA rule for the ticket's priority and sets `due_at` and `response_due_at` accordingly.

The `tickets:check-overdue` Artisan command flags overdue tickets and notifies Supervisors and Admins. It is designed to run on a schedule. Overdue tickets are shown on all role dashboards.

### Comments and Internal Notes

The comment system distinguishes between public comments (visible to all parties including the Customer) and internal notes (visible only to Agents, Supervisors, and Admins). The separation is enforced at the query level in both the web views and the REST API, not just by hiding elements in the UI.

### Attachments

Files can be attached to tickets and comments using a polymorphic relationship. Uploaded files are stored on the public disk and file access is controlled by `AttachmentPolicy`, which checks whether the user has permission to view the associated ticket before serving the file.

Allowed types: jpg, jpeg, png, pdf, doc, docx, xls, xlsx. Maximum size: 2 MB per file. Stored fields include original filename, generated filename, MIME type, file size, and uploader ID.

### Activity Log

Every significant action on a ticket is recorded: creation, status changes, assignment and reassignment, comments and internal notes, attachment uploads, and SLA overdue events. Logs store the actor, action, old value, new value, and timestamp. The `ActivityLogger` static helper centralizes all log writes so the format stays consistent across the codebase.

### Notifications

Sent via email and stored in the database (visible in the notification bell in the topbar):

| Event | Recipients |
|---|---|
| Ticket created | All Admins |
| Ticket assigned | Assigned Agent |
| New comment | Ticket creator and assigned agent |
| Ticket resolved | Ticket creator |
| Ticket escalated | Supervisors and Admins |
| SLA overdue | Supervisors and Admins |

`TicketCreated` is queued via `ShouldQueue`. Queue driver: database.

### REST API

Authenticated via Laravel Sanctum bearer tokens. Token expiry: 24 hours. Rate limit: 60 requests per minute per user or IP.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/login` | Obtain bearer token |
| DELETE | `/api/logout` | Revoke token |
| GET | `/api/tickets` | List tickets (role-scoped) |
| POST | `/api/tickets` | Create ticket |
| GET | `/api/tickets/{ticket}` | View ticket detail |
| PATCH | `/api/tickets/{ticket}/status` | Update status |
| POST | `/api/tickets/{ticket}/assign` | Assign to agent |
| POST | `/api/tickets/{ticket}/comments` | Add comment or internal note |

## Installation

### 1. Clone and install dependencies

```bash
git clone https://github.com/AgungLucker/Ticket-Support-System-Inditech-Test.git
cd ticket-support-system

composer install
npm install
```

### 2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure the database

**Option A: SQLite (quick local setup)**

No additional setup needed. SQLite is preconfigured in `.env.example`.

**Option B: MySQL or MariaDB**

Create a database, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_support
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Configure mail (for email notifications)

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@ticketsystem.com"
MAIL_FROM_NAME="Ticket Support System"
```

Without a configured mail driver, email notifications will fail silently. The in-app notification bell works regardless.

### 5. Run migrations and seed demo data

```bash
php artisan migrate:fresh --seed
```

### 6. Link public storage

```bash
php artisan storage:link
```

Required for ticket and comment attachments stored on the public disk.

### 7. Build frontend assets

```bash
npm run build
```

For active development with hot reload:

```bash
npm run dev
```

### 8. Start the development server

```bash
php artisan serve
```

Application is available at `http://127.0.0.1:8000`.

## Running Background Workers

The system uses Laravel Queues for async email notifications. Run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

The SLA overdue checker is designed to run on a schedule. To configure it, add the Laravel scheduler to your system crontab:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

To run it manually for local verification:

```bash
php artisan tickets:check-overdue
```

## Running Tests

```bash
php artisan test
```

102 tests covering authentication, ticket CRUD, role-based access control, status workflow transitions, SLA logic, file validation, internal notes protection, assignment rules, and API endpoints.

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@admin.com | password |
| Supervisor | supervisor@admin.com | password |
| Agent | agent@admin.com | password |
| Customer | customer@demo.com | password |

Additional numbered users per role are also seeded (e.g. `admin1@ticket.com` / `admin1234`, `supervisor1@ticket.com` / `supervisor1234`) for broader testing. Factory-seeded users use password `password`.

## Known Limitations

1. **CSV export is synchronous.** For large ticket volumes this runs in the request cycle and may time out. Ideally it should be queued.
2. **No optimistic locking on status updates.** If two users update the same ticket at the same time, the last update overwrites the previous one.
3. **API does not support file uploads.** Attachments can only be added through the web UI.
4. **Team uniqueness is UI-enforced only.** The one-supervisor-per-team constraint is validated in the form layer but not enforced with a database unique constraint.

## Developer Confession

**What was the hardest part?**

Implementing the SLA mechanism. There are two due dates to track, resolution and response, each calculated from different reference points, and overdue detection had to work correctly across all role dashboards without loading entire ticket collections into PHP.

**What shortcuts were taken?**

`ActivityLogger` is a static helper instead of an injectable service, which is convenient but harder to mock in tests. The overdue command processes all overdue tickets in a single pass instead of batching them into separate jobs.

**What would be fixed with more time?**
Build a restore UI for soft-deleted master data, optimistic locking on status updates, and end-to-end tests with Laravel Dusk.

**The most cursed code that still works?**

`DashboardController` dispatching to four role-specific methods, each calling `DashboardService` with slightly different parameters depending on the role. The code works and already tested, but it is a lot of near-identical code paths doing almost the same thing.
