# Compliance Smart HRMS — Complete Project Documentation

## Table of Contents
1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [System Architecture](#3-system-architecture)
4. [Roles & Access Control](#4-roles--access-control)
5. [Authentication & Registration](#5-authentication--registration)
6. [Attendance System](#6-attendance-system)
7. [Leave Management](#7-leave-management)
8. [Task System](#8-task-system)
9. [Performance Engine](#9-performance-engine)
10. [Rating System](#10-rating-system)
11. [Reward & Points System](#11-reward--points-system)
12. [Leaderboard](#12-leaderboard)
13. [AI Coach](#13-ai-coach)
14. [Payroll System](#14-payroll-system)
15. [Contracts](#15-contracts)
16. [Complaints & Grievances](#16-complaints--grievances)
17. [Internal Messaging](#17-internal-messaging)
18. [Alerts & Notifications](#18-alerts--notifications)
19. [Automated Cron Jobs](#19-automated-cron-jobs)
20. [Offline-First MongoDB Sync](#20-offline-first-mongodb-sync)
21. [Security](#21-security)
22. [Database Collections](#22-database-collections)
23. [API Endpoints](#23-api-endpoints)
24. [Common Commands](#24-common-commands)
25. [Known Behaviours & Rules](#25-known-behaviours--rules)

---

## 1. Project Overview

**Compliance Smart HRMS** is a full-stack, enterprise-grade Human Resource Management System
built on Laravel 12 and MongoDB Atlas. It handles the complete employee lifecycle — from
registration and attendance to payroll, performance, and legal compliance.

- **App Name:** Smart HRMS
- **Framework:** Laravel 12 (PHP 8.2)
- **Database:** MongoDB Atlas (cloud) + Local MongoDB (offline-first dev)
- **UI:** Argon Dashboard 3, Bootstrap 5, Blade Templates
- **Mail:** Gmail SMTP
- **Scheduling:** Laravel Scheduler (7 automated cron jobs)

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Database | MongoDB Atlas + `mongodb/laravel-mongodb` package |
| Frontend | Blade + Bootstrap 5 + Argon Dashboard 3 |
| Charts | Chart.js |
| Auth | Laravel Breeze + OTP Email Verification |
| Mail | Gmail SMTP (`smtp.gmail.com:587`) |
| Queue | Database driver (MongoDB) |
| Cache | File driver |
| Session | File driver |
| Scheduling | Laravel Artisan Scheduler |
| Dev DB | Local MongoDB (`mongodb://127.0.0.1:27017`) |

---

## 3. System Architecture

```
Browser Request
     │
     ▼
web.php Routes (role middleware applied)
     │
     ├── Admin Routes  (/admin/*)   → Admin\Controllers
     ├── HR Routes     (/hr/*)      → HR\Controllers
     └── Employee Routes (/employee/*) → Employee\Controllers
                              │
                              ▼
                        Service Layer
              ┌───────────────────────────────┐
              │  ScoringService               │  ← Performance math
              │  RatingService                │  ← Anti-cheat ratings
              │  AiCoachService               │  ← Insights generation
              │  RewardService                │  ← Points & badges
              └───────────────────────────────┘
                              │
                              ▼
                    MongoDB (15 Collections)
```

---

## 4. Roles & Access Control

Three roles enforced via `RoleMiddleware`:

### Admin (`role:admin`)
- Full employee CRUD (create, view, edit, delete)
- Approve or reject new employee registrations
- Generate and manage payrolls
- Rate any employee
- Manage contracts, leaves, complaints
- View all dashboards and reports

### HR Manager (`role:hr,admin`)
- View and manage employees (no delete)
- Approve or reject leaves
- Process payrolls
- Rate employees
- Handle grievances/complaints
- Check-in/check-out (own attendance tracked)
- View HR dashboard with own attendance chart

### Employee (`role:employee`)
- Personal dashboard with live performance score
- Daily check-in and check-out
- Apply for leaves
- View own payslips
- File complaints (anonymous option)
- View own task list
- See leaderboard and rewards

---

## 5. Authentication & Registration

### Registration Flow
1. User fills registration form (name, email, password, phone)
2. System sends **OTP to email** via Gmail SMTP
3. User enters OTP on verification page (expires in 10 minutes)
4. Account is created with `status = pending`
5. **Admin must approve** the account before login is allowed
6. On approval, user is notified and can log in

### Login
- Standard Laravel Breeze login
- Failed logins are rate-limited
- Session stored in `file` driver (works offline)

### Password Reset
- Standard Laravel email link reset
- Requires internet (Gmail SMTP)

### Offline Email Tip
Set `MAIL_MAILER=log` in `.env` to write emails to `storage/logs/laravel.log` instead of sending via Gmail when offline.

---

## 6. Attendance System

### How Check-In Works
1. Employee clicks **Check In** on their dashboard
2. `AttendanceController@checkIn` creates an `Attendance` record with:
   - `employee_id` — linked to the employee
   - `date` — today's date (`Y-m-d`)
   - `check_in` — current time (`H:i`)
   - `status` — `present`
3. Only one check-in per day is allowed

### How Check-Out Works
1. Employee clicks **Check Out**
2. Controller finds today's attendance record and sets:
   - `check_out` — current time
   - `total_hours` — calculated as `check_out - check_in` in decimal hours

### Attendance Rules in Scoring
| Rule | Detail |
|---|---|
| Minimum hours to count | `4.0 hours` |
| Maximum hours credited per day | `9.0 hours` (capped) |
| Expected working days per month | `22 days` |
| Late login penalty | After `09:15` = `-2 points` per day |
| Approved leave days | NOT penalised in score |
| Weekends | Skipped in seeding and scoring |

### Attendance & Rewards
- `present` → `+10 points`
- `half_day` → `+5 points`
- `absent` → `-5 points`
- Perfect weekly attendance → `+20 bonus points`

### Dashboard Counters
- **Total Employees** = count of users with `role = employee` only
- **Present Today** = attendance records for today filtered to employee-role IDs only
- (HR and Admin attendance does NOT count in these numbers)

---

## 7. Leave Management

### Employee Flow
1. Employee goes to Leave → Apply
2. Fills: leave type, start date, end date, reason
3. Status is set to `pending`

### HR/Admin Approval Flow
1. HR or Admin views Leaves → sees all pending requests
2. Clicks Approve or Reject with optional remark
3. Status updates to `approved` or `rejected`
4. Employee is notified via an Alert

### Leave Impact on Performance
- **Approved** leaves are subtracted from the expected working days so the employee is not penalised
- **Unapproved absences** reduce attendance score normally
- Leave days counted using weekday diff (weekends excluded)

### Leave Types
- Sick Leave
- Casual Leave
- Annual Leave
- Any custom type entered

---

## 8. Task System

### How Tasks Are Assigned
Tasks are **auto-assigned every Friday at 11:00 PM** by the `hr:auto-assign-tasks` cron job.
The `AutoAssignTasks` command:
1. Gets all active employees
2. Assigns 2–4 random tasks per employee from a predefined task pool
3. Sets a deadline of 7 days from assignment
4. Records `assigned_by` as the system

### Task Statuses
| Status | Meaning |
|---|---|
| `pending` | Assigned, not yet done |
| `completed` | Marked done before or on deadline |
| `missed` | Not completed and deadline passed (auto-set by `hr:seed-attendance-tasks` or cron) |

### Task Impact on Score
```
Score = (completed_on_time + 0.5 × completed_late) / total_tasks
```
- On-time completion = full credit
- Late completion = 50% credit
- Missed deadline = `-5 points` penalty per missed task
- If no tasks assigned = 0.0 component score (not 1.0)

### Task Auto-Marking
The `hr:seed-attendance-tasks` command checks all pending tasks and auto-marks as `missed` 
if `deadline` has passed and `status` is still `pending`.

---

## 9. Performance Engine

The core is `ScoringService` with 4 weighted components:

### Score Formula
```
Base Score = (
    attendance_component  × 0.35 +
    rating_component      × 0.30 +
    task_component        × 0.20 +
    consistency_component × 0.15
) × 100
```

Then:
```
Final Score = (Base Score × teamwork_multiplier) - penalties
```

### Component Details

#### Attendance (35%)
- Normalised: `verified_days / effective_expected_days`
- Only records with `total_hours >= 4.0` and `check_out` set count
- Leave-adjusted expected days
- Capped at 1.0

#### Rating (30%)
- Requires minimum **2 independent evaluators**
- If 3+ raters: **trimmed mean** (drop highest + lowest before averaging)
- **Outlier rejection**: ratings deviating > 2.0 from trimmed mean are removed
- Normalised: `final_avg / 5.0`
- If < 2 evaluators: returns `0.0` (not penalised, just neutral)

#### Task (20%)
- See Task System section above
- `0.0` if no tasks exist for the month

#### Consistency (15%)
- Measures attendance streak (max consecutive weekdays present)
- Penalises erratic attendance using coefficient of variation
- Formula: `(streak_score × 0.6) + (regularity_bonus × 0.4)`

### Penalties
| Penalty | Amount |
|---|---|
| Late login (after 09:15) | -2 points each |
| Missed task deadline | -5 points each |

### Teamwork Multiplier
If `rating_component > 0.8` (i.e., avg peer rating > 4.0 out of 5):
- Multiplier = `1.05 + ((rating - 0.8) × 0.25)` — max **1.10×**

### Performance Tiers
| Score Range | Tier |
|---|---|
| 90 – 100 | Level 5 |
| 80 – 89 | Level 4 |
| 65 – 79 | Level 3 |
| 50 – 64 | Level 2 |
| 25 – 49 | Level 1 |
| 0 – 24 | Unranked |

---

## 10. Rating System

### Who Can Rate
- Admin and HR can rate any employee from the employee detail page
- Ratings are submitted via the `/admin/employees/{id}/rate` or `/hr/employees/{id}/rate` route

### Rating Categories (each scored 1–5)
1. Work Quality
2. Punctuality
3. Teamwork
4. Task Completion
5. Discipline

### Anti-Cheat Rules
| Rule | Detail |
|---|---|
| Self-rating blocked | Evaluator ID cannot match employee's user ID |
| 7-day cooldown | Each evaluator can rate the same employee only once per 7 days |
| Bias detection | If new rating deviates > 2.5 from historical avg → flagged as suspicious |
| Category clamping | All values clamped to `[1, 5]` regardless of input |
| Evaluator anonymity | Employee is only notified of their average, not who rated them |

### After Rating
- Employee receives an Alert: `"You received a new performance rating! Average: X/5"`
- If the weakest category score is < 3.5, employee gets an improvement suggestion Alert

---

## 11. Reward & Points System

### Points Earning
| Event | Points |
|---|---|
| Present (attendance) | +10 |
| Half Day | +5 |
| Absent | -5 |
| Perfect weekly attendance | +20 bonus |
| Monthly top tier (Level 5) | Awarded via cron |

### Badges
Badges are assigned by `hr:evaluate-monthly-performance` cron at month end:
- **Gold** — Level 5 performance
- **Silver** — Level 4 performance
- **Bronze** — Level 3 performance

### Voucher Redemption
Employees can redeem points for vouchers in the Rewards Center:
- Route: `/rewards`
- `POST /rewards/redeem` — deducts points and issues a voucher
- `POST /rewards/use` — marks voucher as used

### Rules
- HR and Admin do NOT earn reward points (checked in `RewardService`)
- Points are stored in `employees.points` field
- Negative points floor at 0 (no debt)

---

## 12. Leaderboard

### How It Works
- Route: `/leaderboard` (all authenticated users)
- Public route: `/leaderboard/public` (no login needed)
- Data comes from `performance_records` collection
- Cached for **5 minutes** (TTL) in `RewardController@leaderboard`

### Update Frequency
- **Live score** shown on employee's own dashboard is computed on-the-fly
- **Leaderboard** shows snapshot from `performance_records`
- Snapshots updated by `hr:sync-live-scores` which runs **every hour**
- Monthly final score frozen by `hr:evaluate-monthly-performance` on 1st of month

### To Force Update Now
```bash
php artisan hr:sync-live-scores
```

---

## 13. AI Coach

Powered by `AiCoachService`. Shown on the Employee Dashboard.

### Triggers and Messages
| Trigger | Message |
|---|---|
| Attendance < 70% | "Boost Your Attendance Score" warning |
| Attendance > 95% | "Stellar Attendance" success |
| Task score < 60% | "Task Deadlines Need Attention" danger |
| Rating < 60% | "Improve Peer Ratings" warning |
| Rating > 80% | "Teamwork Multiplier Active" success |
| Consistency < 50% | "Erratic Schedule Detected" warning |
| Late login penalty | "Late Logins Penalizing Score" danger |
| Missed deadline penalty | "Severe Deadline Penalty" danger |
| All metrics excellent | "Peak Performance — no advice needed" |

- Max 3 insights shown at once
- Cards use border-only style (dark mode compatible)

---

## 14. Payroll System

### Auto-Generation
`payroll:generate` cron runs on **1st of every month at 01:00 AM**:
1. Fetches all employees
2. Calculates: `basic_salary + (overtime_hours × dept_rate) - 10% tax`
3. Creates `Payroll` records with `status = pending`

### Workflow
| Status | Action |
|---|---|
| `pending` | Admin/HR reviews |
| `approved` | Admin/HR approves — employee notified |
| `paid` | Marks salary as disbursed |

### Payslip Download
- Admin: `GET /admin/payrolls/{id}/download` → PDF
- HR: `GET /hr/payrolls/{id}/download` → PDF
- Admin download all: `GET /admin/payrolls/download-all`

### Employee View
- Employee sees only their own payrolls: `GET /employee/payrolls`
- No download from employee side (read-only)

### Payroll Fields
`employee_id`, `month`, `basic_salary`, `overtime_hours`, `overtime_rate`,
`gross_salary`, `tax_amount`, `net_salary`, `status`, `payment_date`

---

## 15. Contracts

### Auto-Generation
When an employee is approved, the system auto-creates a 6-month contract.

### Contract Fields
`employee_id`, `start_date`, `end_date`, `type`, `status` (`active`/`expired`/`terminated`)

### Expiry Check
`hr:check-contracts` cron runs **daily at 08:00 AM**:
- Finds contracts expiring within 30 days
- Creates an Alert for Admin with a link to the contracts page

### Management
- Admin/HR can create, edit contracts via `/admin/contracts` or `/hr/contracts`
- No delete (archive pattern)

---

## 16. Complaints & Grievances

### Employee Flow
1. Employee goes to Complaints → Create
2. Fills: title, description
3. Option: **Submit Anonymously** (name hidden from HR, shown to Admin)
4. Status = `pending`

### Admin/HR Handling
1. View complaints at `/admin/complaints` or `/hr/complaints`
2. Click **Respond** button → modal appears
3. Choose: Resolved or Rejected
4. Write official response
5. Status updates — employee notified

### Anonymous Logic
- If `is_anonymous = true`:
  - **HR sees:** "Anonymous" (name hidden)
  - **Admin sees:** "Anon" badge + actual name (admin always knows)

### Date Fix
All complaints must have `created_at` ≤ today. Any future-dated complaints 
are fixed by `php artisan hr:fix-future-dates`.

---

## 17. Internal Messaging

### Features
- Send messages to any user (Admin, HR, Employee)
- Reply to messages (threaded)
- Sent folder
- Unread indicator

### Routes
| Route | Purpose |
|---|---|
| `GET /messages` | Inbox |
| `GET /messages/sent` | Sent folder |
| `GET /messages/create` | Compose new message |
| `POST /messages` | Send message |
| `GET /messages/{id}` | Read message |
| `POST /messages/{id}/reply` | Reply |

---

## 18. Alerts & Notifications

All in-app notifications stored in `alerts` collection.

### Alert Types
| Type | Color |
|---|---|
| `success` | Green |
| `info` | Blue |
| `warning` | Yellow |
| `danger` | Red |

### What Triggers Alerts
- New rating received
- Rating improvement suggestion
- Leave approved or rejected
- Payroll approved or paid
- Contract expiring in 30 days
- High pending leave volume (admin)
- High performer detected (admin)
- Low attendance employee (admin)

### Routes
- `GET /alerts/{id}/read` — mark one as read
- `POST /alerts/mark-all-read` — mark all read

---

## 19. Automated Cron Jobs

Run via `php artisan schedule:work` in development.
On a real server: `* * * * * php artisan schedule:run >> /dev/null 2>&1`

| Command | Schedule | Purpose |
|---|---|---|
| `hr:sync-live-scores` | Every hour | Recomputes all employee live scores and updates `performance_records` |
| `hr:evaluate-monthly-performance` | 1st of month 00:00 | Freezes final scores, assigns tiers, awards badges and points |
| `payroll:generate` | 1st of month 01:00 | Auto-generates monthly payroll for all employees |
| `hr:auto-assign-tasks` | Every Friday 23:00 | Assigns 2–4 random tasks per employee with 7-day deadline |
| `hr:check-contracts` | Daily 08:00 | Alerts admin about contracts expiring within 30 days |
| `hr:check-compliance` | On demand | Validates labour law compliance records |
| `hr:ensure-mongo-indexes` | On demand | Creates compound indexes for query optimisation |

### Manually Run Any Cron
```bash
php artisan hr:sync-live-scores
php artisan hr:evaluate-monthly-performance
php artisan payroll:generate
php artisan hr:auto-assign-tasks
php artisan hr:check-contracts
php artisan hr:seed-attendance-tasks   # seeds attendance + checks tasks
php artisan hr:fix-future-dates        # fixes records with future dates
```

---

## 20. Offline-First MongoDB Sync

The project uses a **dual-database setup** so it works without internet.

### .env Configuration
```env
# App uses this (local, works offline)
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=labour_compliance

# Only the sync command uses this
MONGODB_URI_ATLAS=mongodb+srv://...@cluster0.h49uadf.mongodb.net/...
MONGODB_DATABASE_ATLAS=labour_compliance
```

### Sync Commands
```bash
# Preview what would happen (safe)
php artisan mongo:sync-atlas --dry-run

# Apply sync (auto-backs up first)
php artisan mongo:sync-atlas

# Pull Atlas data to local (refresh local)
php artisan mongo:pull-atlas --fresh

# Backup local to JSON files
php artisan mongo:backup
```

### Sync Rules (never destructive)
1. Local doc not in Atlas → INSERT into Atlas
2. Local newer than Atlas → UPDATE Atlas
3. Atlas newer than local → SKIP (no overwrite)
4. Both changed since last sync → CONFLICT (flagged, not overwritten)
5. Atlas doc not in local → left alone (never deleted)
6. Network failure → stops safely, logs state
7. Every sync action written to `sync_logs` collection

### Sync Metadata on Every Document
```json
{
  "sync_status": "pending | synced | conflict | failed",
  "local_updated_at": "...",
  "atlas_updated_at": "...",
  "synced_at": "...",
  "sync_version": 1,
  "last_sync_error": null
}
```

### MongoDB Compass Connections
- **Local:** `mongodb://127.0.0.1:27017`
- **Atlas:** paste the `MONGODB_URI_ATLAS` value

---

## 21. Security

| Measure | Detail |
|---|---|
| CSRF | `@csrf` on all POST/PUT/DELETE forms |
| Role Middleware | `RoleMiddleware` enforced on every route group |
| OTP Verification | Email OTP for new registrations |
| Mass Assignment | All models use `$fillable` |
| Input Clamping | Rating values clamped to `[1,5]` |
| XSS Prevention | Blade `{{ }}` escaping + `@json` directive |
| Self-Rating Block | Cannot rate yourself |
| Bias Detection | Suspicious ratings logged and flagged |
| Activity Logging | `LogsActivity` trait on key models |
| Exception Handler | Graceful MongoDB disconnection handling |
| .env Protection | Credentials never in code, in `.env` only |
| .gitignore | `.env`, `.idea`, `vendor`, `node_modules` excluded |

---

## 22. Database Collections

| Collection | Description |
|---|---|
| `users` | Auth users (admin, hr, employee) |
| `employees` | Employee profiles linked to users |
| `departments` | Department records |
| `attendances` | Daily check-in/check-out records |
| `leaves` | Leave requests and approval status |
| `payrolls` | Monthly salary records |
| `contracts` | Employment contracts |
| `complaints` | Grievances filed by employees |
| `tasks` | Assigned tasks with deadlines |
| `ratings` | Peer/HR performance ratings |
| `performance_records` | Monthly score snapshots per employee |
| `monthly_rewards` | Reward events per month |
| `alerts` | In-app notifications |
| `messages` | Internal messages and replies |
| `activity_logs` | Audit trail of model changes |
| `sync_logs` | MongoDB sync audit trail (local only) |

---

## 23. API Endpoints

| Method | URL | Purpose |
|---|---|---|
| POST | `/api/employees/{id}/rate` | Submit rating (JSON) |
| GET | `/api/my-performance` | Get own performance history (JSON) |
| POST | `/admin/employees/{id}/rate` | Rate employee (web form) |
| POST | `/hr/employees/{id}/rate` | HR rates employee (web form) |

---

## 24. Common Commands

```bash
# Development server
php artisan serve

# Run scheduler (acts as cron in dev)
php artisan schedule:work

# Force leaderboard update
php artisan hr:sync-live-scores

# Seed today's attendance for all employees
# + full history for HR + check tasks
php artisan hr:seed-attendance-tasks

# Fix any future-dated records
php artisan hr:fix-future-dates

# Backup local MongoDB to JSON
php artisan mongo:backup

# Pull real Atlas data to local
php artisan mongo:pull-atlas --fresh

# Push local changes to Atlas
php artisan mongo:sync-atlas --dry-run
php artisan mongo:sync-atlas

# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Create MongoDB indexes
php artisan hr:ensure-mongo-indexes
```

---

## 25. Known Behaviours & Rules

| Behaviour | Explanation |
|---|---|
| Dashboard score ≠ Leaderboard score | Dashboard = live compute; Leaderboard = cached snapshot updated hourly |
| New employee score = 0 | No ratings, tasks, or attendance yet — scores are strictly 0, not defaulted |
| HR/Admin not on leaderboard | Leaderboard filters to `role = employee` only |
| Present Today ≤ Total Employees | Fixed: both counters use identical `role = employee` filter |
| Ratings need 2+ evaluators | Score component = 0 until at least 2 people rate the employee |
| Approved leaves don't hurt score | Leave days are subtracted from expected working days |
| OTP login fails offline | Gmail SMTP needs internet; set `MAIL_MAILER=log` for offline dev |
| Sync doesn't delete Atlas data | Sync is INSERT/UPDATE only — Atlas records are never deleted |
| `.idea` folder | PhpStorm IDE config — not needed for the project, already in `.gitignore` |
| `sync_logs` not synced to Atlas | Intentionally excluded (local audit trail only) |
