# TaskCraft — Collaborative Project & Task Management Dashboard

TaskCraft is a modern, collaborative project and task management dashboard built with **Laravel 11** and styled with a custom **Vanilla CSS Glassmorphism Dark Mode**. 

This repository was designed specifically to showcase clean backend architecture, performance optimization, and robust security scoping for a Laravel Developer internship position.

---

## 🚀 Key Features

* **Multi-Tenant Workspaces**: Users can create and collaborate within separate workspaces. Custom middleware scopes routes to prevent unauthorized workspace access.
* **Workspace Roles & Permissions**: Role-based access control (Owner, Member, Viewer) configured on a many-to-many relationship using pivot tables.
* **Interactive Kanban Board**: Drag-and-drop task workflow updates built using Vanilla Javascript and Laravel REST endpoints.
* **Task Management**: Fully detailed task view including assignee updates, inline priority alterations, and due dates.
* **Time Logging**: Track individual team contributions on tasks, calculated automatically and displayed dynamically.
* **Live Activity Feed**: Workspace-wide audit log powered by a polymorphic relation structure logging real-time collaborative updates.

---

## 🛠️ Advanced Laravel Practices Showcased

1. **Performance Tuning (Eager Loading)**: Used `with()` and `withCount()` query eager loading to prevent $N+1$ database query loops, ensuring optimal loading speeds even with deep nested relationships.
2. **Polymorphic Relationships**: Implemented a unified `ActivityLog` table that dynamically links to multiple model types (`Project`, `Task`, `TimeLog`) using Laravel's polymorphic morph mappings.
3. **Form Request Validation**: Extracted all route inputs validation into dedicated Form Request classes to keep controllers lean and adhere to the Single Responsibility Principle.
4. **Secure Route Scoping**: Implemented custom middleware (`EnsureUserInWorkspace`) to restrict resource visualization and manipulation strictly to workspace members.
5. **Zero-Configuration Setup**: Utilizes an SQLite database out-of-the-box, allowing immediate execution without requiring local MySQL configuration.

---

## 💻 Tech Stack

* **Backend**: PHP 8.2+ & Laravel 11.x (Eloquent ORM)
* **Frontend**: HTML5, Vanilla CSS3 (Glassmorphism layout system), Vanilla JS (Drag & Drop, Ajax)
* **Database**: SQLite (Zero configuration needed)

---

## 🏁 Quick Start & Local Setup

### Prerequisites
Ensure you have **PHP 8.2+** and **Composer** installed.

### Installation Steps
1. **Clone the repository**:
   ```bash
   git clone https://github.com/YOUR_USERNAME/taskcraft.git
   cd taskcraft
   ```
2. **Install dependencies**:
   ```bash
   composer install
   ```
3. **Setup environment variables**:
   ```bash
   copy .env.example .env
   ```
4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```
5. **Run migrations and seed mock database**:
   ```bash
   php artisan migrate:fresh --seed
   ```
6. **Start the local server**:
   ```bash
   php artisan serve
   ```
7. Visit **`http://127.0.0.1:8000`** in your browser.

---

## 🔑 Demo Credentials

To experience the pre-populated dashboard, log in with any of the following seeded user accounts:

| User | Email | Password | Role |
|---|---|---|---|
| **Demo User** | `demo@taskcraft.dev` | `password` | Owner |
| **Alice Johnson** | `alice@taskcraft.dev` | `password` | Member |
| **Bob Smith** | `bob@taskcraft.dev` | `password` | Member |
| **Carol Lee** | `carol@taskcraft.dev` | `password` | Viewer |
