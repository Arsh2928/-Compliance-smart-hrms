# 🏢 Labour Law Compliance & Smart HRMS

Welcome to the **Labour Law Compliance & Smart HRMS**! This project is a comprehensive, production-ready enterprise dashboard built on Laravel and MongoDB. It originally started as a standard Human Resource Management System (HRMS) focused on labor compliance and has since been upgraded with an advanced **Smart Reward & Performance Gamification System**.

This document outlines every major feature, how they function, and how they interact with the rest of the ecosystem.

---

## 👥 1. Role-Based Access Control (RBAC)
The system is divided into three distinct roles, each with specialized routing and access logic:

* **System Admin (`admin`)**: Has global, unrestricted access to the entire platform. Admins can create employees, assign roles, define salaries, and oversee all operations.
* **Human Resources (`hr`)**: Manages the day-to-day operations. HRs can review leave requests, resolve grievances, process monthly payrolls, and evaluate employee performance (ratings). *Note: HRs are explicitly excluded from the Gamification/Reward system to maintain fairness.*
* **Employee (`employee`)**: The standard worker. Employees use the dashboard to check-in/out, submit leave requests, file complaints, and view their payrolls. They also actively participate in the Reward Leaderboard.

> **How it works internally:** The application uses Laravel Middleware (`role:admin`, `role:hr,admin`) to lock down routes. A centralized `/dashboard` redirector automatically routes users to their specific dashboard based on their role.

---

## 📅 2. Attendance Tracking System
A robust module designed to monitor employee work hours strictly.

* **Check-In / Check-Out:** Employees log their daily attendance. The system captures timestamps and calculates total working hours.
* **Dashboard Integration:** The Admin/HR dashboard renders a live `Chart.js` graph ("Weekly Attendance Trends") comparing daily present employees against leave requests.
* **Smart Reward Hook:** When an attendance record is created, it automatically triggers the `RewardService` to calculate gamification points (e.g., +10 for Present, -5 for Absent).

---

## 🏖️ 3. Leave Management System
Handles time-off requests while maintaining company policy limits.

* **Employee Submission:** Employees request leaves (Sick, Casual, Annual) specifying date ranges and reasons.
* **HR Review:** HR receives notifications for pending leaves. They can Approve or Reject them.
* **Interactions:** Leave data flows directly into the Attendance chart. If an employee is on an approved leave, the Reward System knows *not* to deduct absence points for that day.

---

## 💸 4. Payroll Processing System
Simplifies monthly financial compensation.

* **Generation:** HRs can generate monthly payrolls for employees based on their base salary.
* **Future Expansion Hook:** The system is structured so that in the future, unpaid leaves and "Bonus Rewards" (from the gamification system) can automatically alter the final net pay.

---

## ⚖️ 5. Grievances & Complaints System
A dedicated module to ensure a safe and compliant working environment.

* **Filing Complaints:** Employees can raise tickets regarding workplace issues.
* **Resolution Workflow:** HRs track these as "Open Complaints", investigate, and mark them as resolved. 
* **Compliance Tracking:** The dashboard features a "Compliance Overview" progress bar that strictly monitors the ratio of open vs. resolved complaints, ensuring the company remains legally compliant with labor laws.

---

## 🏆 6. Smart Reward & Gamification System (The Core Upgrade)
This is the flagship AI-driven logic module designed to boost productivity, fairness, and motivation without manual HR policing. It introduces a point-based economy.

### A. Point Accumulation & Logic
Employees passively earn points based on their behavior:
* **+10 Points:** Full working day.
* **+5 Points:** Half day.
* **-5 Points:** Unexcused absence.
* **+20 Bonus Points:** Perfect weekly attendance (Triggered automatically via chron/service).

### B. Performance Scoring Engine
The system doesn't just rank people by attendance; it uses a weighted mathematical formula managed by the `RewardService`:
> **Performance Score = (Attendance Points × 0.6) + (Manager Rating × 20)**
* **Manager Ratings:** HRs can periodically rate an employee out of 5 stars. This feeds directly into the performance formula to balance out attendance with actual work quality.

### C. Badge Progression
As employees accumulate `total_points`, the system auto-assigns Badges:
* 🥉 **Bronze Badge** (200+ Points)
* 🥈 **Silver Badge** (400+ Points)
* 🥇 **Gold Badge** (700+ Points)

### D. The Leaderboard & Dashboard Integration
* **Top Performers:** The Admin/HR Home Dashboard injects a live "Top Performers This Month" row, immediately showing the top 3 employees with highest Performance Scores, custom gradient avatars, and their highest Badge.
* **Exclusion Logic:** HRs and Admins are strictly filtered out of the leaderboard to ensure workers only compete against peers.

### E. Reward Redemption Center
* Employees can visit the `/rewards` portal to spend their hard-earned `total_points`.
* **Redemption:** Points can be exchanged for real perks (e.g., $50 Amazon Gift Card = 500pts). When redeemed, the points are instantly deducted from their wallet.

### F. AI Insights & Smart Detection
The `RewardService` continuously runs passive checks:
* **Burnout / Delinquency Detection:** If an employee has less than 50 points after 10+ shifts, the system auto-notifies HR to schedule a check-in.
* **Top Talent Detection:** If a score breaches 300 rapidly, the system alerts HR that the employee is eligible for a bonus.

---

## 🔔 7. Global Alert & Notification System
A unified messaging center that connects all modules together.

* **Creation:** Uses the `Alert` model to dispatch localized, database-driven notifications to users.
* **Use Cases:**
  * *"You earned 10 points today! 🎉"* (From Reward System)
  * *"High volume of pending leaves (8). Please review."* (To HR, from Dashboard Controller)
  * *"Your leave request has been approved."* (To Employee, from Leave Controller)
* **UI:** Displayed via a dropdown bell icon in the Topbar, allowing users to mark them as read.

---

## 🎨 8. UI / UX Design System
The frontend completely dropped Tailwind CSS in favor of a heavily customized, premium **Vanilla CSS / Bootstrap 5** hybrid architecture (`app.css`).

* **Dynamic Layout:** Uses CSS Grid (`.container-row`, `.stats-grid`) to ensure a perfectly balanced, 2-column or 4-column layout that gracefully collapses on mobile.
* **Glassmorphism & Fixed Sidebar:** Features a modern floating sidebar that is `position: fixed` on desktop, alongside a frosted-glass topbar.
* **Component Standardization:** Every element is encapsulated in a `.dashboard-card` with uniform 20px padding, 16px border-radius, and subtle hover animations, completely avoiding stretched or misaligned UIs.

---

### Tech Stack Overview
* **Backend:** Laravel 11.x (PHP 8.2+)
* **Database:** MongoDB (via `mongodb/laravel-mongodb`)
* **Frontend:** Blade Templating, Custom Vanilla CSS, Bootstrap 5 (Grid/Flex utilities only)
* **Interactivity:** Chart.js (Analytics), SweetAlert2 (Modals)
* **Architecture Concept:** MVC (Model-View-Controller) with Service Classes (`RewardService`) for heavy logic isolation.
