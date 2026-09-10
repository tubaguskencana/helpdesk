# Helpdesk & Ticketing System

A web-based helpdesk and ticketing system built with Laravel and Tailwind CSS.

The system is intended to help companies manage internal requests and support issues in a more structured way. Instead of handling requests through WhatsApp, email, or direct messages, users can create tickets and track their progress until the issue is resolved.

---

## Overview

This project will provide a centralized platform for managing support requests across different departments.

Typical use cases include:

- IT Support
- Hardware and Software Issues
- Network Issues
- Account Access Requests
- General Affairs Requests
- Facility or Maintenance Requests
- Internal Employee Support

Each request will be recorded as a ticket and can be assigned, tracked, updated, and resolved through the system.

---

## Tech Stack

### Backend

- Laravel
- PHP 8.3+
- MySQL / MariaDB

### Frontend

- Laravel Blade
- Tailwind CSS
- Alpine.js (where needed)

### Additional Laravel Features

- Laravel Authentication
- Laravel Policies
- Laravel Notifications
- Laravel Queues
- File Storage

Livewire can be added later for features that require more dynamic interaction, but the initial version should remain mostly Blade-based to keep the application simple and maintainable.

---

# User Roles

The system will have several user roles with different levels of access.

## User / Requester

Regular users who create support requests.

Users can:

- Create tickets
- View their own tickets
- Add replies or additional information
- Upload attachments
- Track ticket status
- Receive notifications
- View ticket history

---

## Agent / Support Staff

Support staff responsible for handling tickets.

Agents can:

- View assigned tickets
- Update ticket status
- Reply to users
- Add internal notes
- Update ticket priority
- Resolve tickets

---

## Supervisor

Supervisors are responsible for monitoring tickets and managing assignments.

Supervisors can:

- View tickets within their department
- Assign tickets to agents
- Change ticket priority
- Monitor ticket status
- Monitor SLA performance
- Reopen tickets when necessary

---

## Administrator

Administrators have full access to the system.

Administrators can manage:

- Users
- Roles and permissions
- Departments
- Categories
- SLA settings
- System configuration
- All tickets

---

# Core Features

## Authentication

Basic authentication features:

- Login
- Logout
- Forgot Password
- Reset Password
- Change Password
- User Profile

Possible future integrations:

- Google Login
- Microsoft Login
- Single Sign-On
- Active Directory

---

# Ticket Management

Ticket management is the main feature of the application.

Users can create a ticket with the following information:

- Subject
- Department
- Category
- Description
- Priority
- Attachment

Example:

```text
Subject: Printer Finance Tidak Bisa Digunakan

Department: IT Support
Category: Hardware
Priority: Medium
```

Every ticket should have a unique ticket number.

Example:

```text
HD-2026-000123
```

---

## Ticket Status

The initial version will use the following statuses:

| Status | Description |
|---|---|
| Open | Ticket has been created |
| In Progress | Ticket is currently being handled |
| Pending | Waiting for additional information or action |
| Resolved | Issue has been resolved |
| Closed | Ticket is completed and closed |

Optional statuses for future versions:

- Cancelled
- Reopened

---

## Ticket Priority

Available priorities:

- Low
- Medium
- High
- Urgent

The requester can select an initial priority when creating a ticket.

Agents, supervisors, and administrators may update the priority when necessary.

---

# Ticket Detail

The ticket detail page will be the main workspace for handling a ticket.

It should display:

- Ticket Number
- Subject
- Requester
- Department
- Category
- Priority
- Status
- Assigned Agent
- Created Date
- Last Updated
- SLA Deadline

---

## Conversation

All ticket communication should be displayed chronologically.

Example activity:

```text
User created the ticket

Agent replied to the ticket

Status changed from Open to In Progress

User added additional information

Agent resolved the issue
```

The conversation should clearly separate:

- User messages
- Agent replies
- Internal notes
- System activities

---

# Public Reply and Internal Notes

## Public Reply

Public replies can be seen by the requester.

Used for:

- Providing updates
- Asking for additional information
- Giving instructions
- Confirming that an issue has been resolved

---

## Internal Notes

Internal notes are only visible to:

- Agents
- Supervisors
- Administrators

Example:

```text
Printer sudah dicek. Kemungkinan cartridge perlu diganti.
Menunggu approval untuk replacement.
```

The requester should never be able to see internal notes.

---

# Ticket Assignment

Tickets can be assigned manually to support agents.

Initial workflow:

```text
User Creates Ticket
        ↓
Ticket Status: Open
        ↓
Supervisor / Admin Reviews Ticket
        ↓
Assign to Agent
        ↓
Agent Handles Ticket
```

For the first version, manual assignment is recommended.

Automatic assignment can be considered later based on:

- Department
- Category
- Agent workload
- Agent availability

---

# Departments and Categories

The system should support multiple departments.

Example departments:

- IT Support
- Human Resources
- Finance
- General Affairs
- Facility Management

Each department can have its own categories.

Example:

```text
IT Support
├── Hardware
├── Software
├── Network
├── Email
└── Account Access
```

---

# Dashboard

Dashboard content should depend on the user's role.

## User Dashboard

Display:

- My Open Tickets
- Tickets In Progress
- Resolved Tickets
- Recent Activity

---

## Agent Dashboard

Display:

- Assigned to Me
- Unassigned Tickets
- High Priority Tickets
- Tickets Near SLA Deadline
- Recent Activity

---

## Administrator Dashboard

Display summary information such as:

- Total Tickets
- Open Tickets
- Tickets In Progress
- Resolved Today
- Overdue Tickets

Possible charts:

- Tickets by Category
- Tickets by Department
- Ticket Trends
- Resolution Time
- Agent Workload

---

# Ticket List

The ticket list should use a table layout rather than displaying every ticket as a card.

Suggested columns:

| Ticket | Subject | Requester | Priority | Status | Assigned To | Updated |
|---|---|---|---|---|---|---|

Available filters:

- Search
- Status
- Priority
- Department
- Category
- Assigned Agent
- Date Range

Example:

```text
Tickets                                              + New Ticket

Search tickets...        Status ▼        Priority ▼

----------------------------------------------------------------

#HD-000123   Printer Error        High      In Progress

#HD-000124   Reset Password       Medium    Open

#HD-000125   WiFi Tidak Bisa      Urgent    Open

----------------------------------------------------------------
```

---

# Attachments

Users and agents should be able to upload files to tickets.

Supported examples:

- Images
- PDF files
- Documents
- Screenshots

Attachment information should include:

- Original filename
- File path
- File type
- File size
- Uploaded by
- Uploaded date

File validation should be handled on both the frontend and backend.

---

# Activity Log

Every important ticket action should be recorded.

Examples:

```text
Ticket created

Status changed from Open to In Progress

Priority changed from Medium to High

Ticket assigned to John Doe

Internal note added

Ticket resolved
```

This provides a clear audit trail and makes it easier to understand the history of a ticket.

---

# Notifications

The system should notify users when important actions occur.

## Requester Notifications

- Ticket successfully created
- Agent replied
- Ticket status changed
- Ticket resolved
- Ticket closed

## Agent Notifications

- New ticket assigned
- User replied to a ticket
- Ticket is approaching SLA deadline
- Ticket has exceeded SLA

Initial notification channels:

- In-App Notification
- Email

Possible future integrations:

- WhatsApp
- Telegram
- Slack
- Microsoft Teams

---

# SLA Management

SLA management can be implemented after the core ticket system is stable.

Example SLA configuration:

| Priority | First Response | Resolution Target |
|---|---:|---:|
| Low | 24 Hours | 72 Hours |
| Medium | 8 Hours | 48 Hours |
| High | 4 Hours | 24 Hours |
| Urgent | 1 Hour | 8 Hours |

The system should display SLA status visually.

```text
🟢 Safe

🟡 Near Deadline

🔴 Overdue
```

---

# Knowledge Base

A knowledge base can be added to help users solve common problems before creating a ticket.

Example categories:

- Account & Login
- Email
- Network
- Printer
- Hardware
- Software

Example articles:

```text
How to Reset Your Password

How to Connect to Office WiFi

How to Setup Company Email

How to Troubleshoot an Offline Printer
```

In a future version, relevant knowledge base articles can be suggested while the user is creating a ticket.

---

# Database Structure

## users

```text
id
name
email
password

department_id

created_at
updated_at
```

Roles and permissions can be managed using a dedicated package or custom implementation.

---

## departments

```text
id
name
description
is_active

created_at
updated_at
```

---

## categories

```text
id
department_id

name
description
is_active

created_at
updated_at
```

---

## tickets

```text
id

ticket_number

user_id
department_id
category_id
assigned_to

subject
description

priority
status

opened_at
resolved_at
closed_at

sla_due_at

created_at
updated_at
```

---

## ticket_replies

```text
id

ticket_id
user_id

message
is_internal

created_at
updated_at
```

---

## ticket_attachments

```text
id

ticket_id
reply_id

file_name
file_path
file_type
file_size

created_at
updated_at
```

---

## ticket_activities

```text
id

ticket_id
user_id

activity_type
description

old_value
new_value

created_at
```

---

# Database Relationships

```text
User
 ├── hasMany Tickets
 ├── hasMany Ticket Replies
 └── belongsTo Department

Department
 ├── hasMany Users
 ├── hasMany Categories
 └── hasMany Tickets

Category
 ├── belongsTo Department
 └── hasMany Tickets

Ticket
 ├── belongsTo User
 ├── belongsTo Department
 ├── belongsTo Category
 ├── belongsTo Assigned Agent
 ├── hasMany Replies
 ├── hasMany Attachments
 └── hasMany Activities

Ticket Reply
 ├── belongsTo Ticket
 └── belongsTo User
```

---

# Suggested Project Structure

The application should mostly follow Laravel's default structure.

```text
app/
├── Actions/
│   └── Tickets/
│
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── TicketController.php
│   │   ├── TicketReplyController.php
│   │   │
│   │   └── Admin/
│   │       ├── UserController.php
│   │       ├── DepartmentController.php
│   │       └── CategoryController.php
│   │
│   └── Requests/
│
├── Models/
│   ├── User.php
│   ├── Ticket.php
│   ├── TicketReply.php
│   ├── Department.php
│   └── Category.php
│
├── Notifications/
│
├── Policies/
│
└── Services/
    └── TicketService.php
```

The goal is to keep the application structure simple.

There is no need to introduce unnecessary layers such as repositories, interfaces, DTOs, or managers unless the project actually requires them.

---

# Authorization

Laravel Policies should be used for authorization.

Example ticket permissions:

```php
view()

create()

update()

assign()

resolve()

close()

reopen()
```

Basic access rules:

- Users can only view their own tickets
- Agents can view tickets assigned to them or allowed departments
- Supervisors can view tickets within their department
- Administrators can view all tickets

---

# UI Direction

The UI should feel like an internal business application.

Not overly decorative and not designed like a marketing landing page.

## General Style

- Clean
- Minimal
- Professional
- Functional
- Easy to scan

Avoid:

- Excessive gradients
- Too many floating cards
- Heavy shadows everywhere
- Oversized rounded corners
- Too many colors

The focus should be on usability and information hierarchy.

---

# Application Layout

## Desktop

```text
┌──────────────┬──────────────────────────────────────┐
│              │ Topbar                               │
│ Sidebar      ├──────────────────────────────────────┤
│              │                                      │
│ Dashboard    │                                      │
│ Tickets      │             Content                  │
│ Knowledge    │                                      │
│ Reports      │                                      │
│ Settings     │                                      │
│              │                                      │
└──────────────┴──────────────────────────────────────┘
```

---

# Sidebar Navigation

```text
Dashboard

Tickets
├── All Tickets
├── My Tickets
└── Unassigned

Knowledge Base

Reports

Administration
├── Users
├── Departments
├── Categories
└── SLA Settings
```

Navigation items should be shown based on user permissions.

---

# Ticket Detail Layout

Desktop layout:

```text
┌────────────────────────────────────────┬─────────────────────┐
│                                        │                     │
│ Conversation / Activity                │ Ticket Information  │
│                                        │                     │
│ User Message                           │ Status              │
│                                        │ Priority            │
│ Agent Reply                            │ Category            │
│                                        │ Department          │
│ Internal Note                          │ Assigned Agent      │
│                                        │ SLA Deadline        │
│ Reply Form                             │                     │
│                                        │                     │
└────────────────────────────────────────┴─────────────────────┘
```

On smaller screens, ticket information should move below the conversation.

---

# Development Plan

## Phase 1 — Project Setup

Estimated: **1–2 days**

Tasks:

- Initialize Laravel project
- Configure database
- Setup Tailwind CSS
- Setup authentication
- Create base layout
- Create sidebar and navigation
- Configure user roles

---

## Phase 2 — Master Data

Estimated: **2–3 days**

Tasks:

- Department CRUD
- Category CRUD
- User management
- Role and permission setup

---

## Phase 3 — Ticket Management

Estimated: **5–7 days**

Tasks:

- Create ticket
- Generate ticket number
- Ticket list
- Ticket detail
- Status management
- Priority management
- Ticket assignment

This phase contains the core functionality of the application.

---

## Phase 4 — Communication and Attachments

Estimated: **3–4 days**

Tasks:

- Public replies
- Internal notes
- Activity timeline
- File uploads
- Attachment handling

---

## Phase 5 — Dashboard and Reporting

Estimated: **3–5 days**

Tasks:

- Dashboard statistics
- Ticket summary
- Basic charts
- Agent workload
- Ticket reports
- Export CSV / Excel

---

## Phase 6 — Notifications and SLA

Estimated: **3–5 days**

Tasks:

- In-app notifications
- Email notifications
- SLA configuration
- SLA deadlines
- Overdue indicators

---

## Phase 7 — QA and Deployment

Estimated: **3–5 days**

Tasks:

- Feature testing
- Permission testing
- Responsive testing
- Bug fixing
- Performance checks
- Production deployment

---

# Estimated Timeline

| Phase | Estimated Duration |
|---|---:|
| Project Setup | 1–2 Days |
| Master Data | 2–3 Days |
| Ticket Management | 5–7 Days |
| Communication & Attachments | 3–4 Days |
| Dashboard & Reporting | 3–5 Days |
| Notifications & SLA | 3–5 Days |
| QA & Deployment | 3–5 Days |

### Estimated MVP Development

Approximately **3–5 weeks for one developer**, depending on the final requirements and revision process.

A smaller MVP can be completed faster if advanced features are postponed.

---

# MVP Scope

The first version should focus on the features needed for daily use.

## Included

- Authentication
- User roles
- Department management
- Category management
- Create ticket
- Ticket list
- Ticket detail
- Ticket replies
- Internal notes
- Ticket status
- Ticket priority
- Ticket assignment
- Attachments
- Activity log
- Basic dashboard

---

## Future Improvements

These features can be added after the core system is stable.

- WhatsApp integration
- Telegram integration
- Slack integration
- Microsoft Teams integration
- Automatic ticket assignment
- Advanced SLA rules
- Customer satisfaction survey
- Knowledge base suggestions
- Advanced analytics
- Asset management
- Email-to-ticket integration
- AI-assisted ticket categorization

---

# Development Approach

The recommended approach for this project is:

> Laravel monolith + Blade + Tailwind CSS.

The application does not need to start as a SPA or microservice architecture.

Keeping the first version simple will make development, deployment, and maintenance easier.

The main priorities should be:

- Clear ticket workflow
- Proper authorization
- Good usability
- Reliable activity logging
- Easy ticket tracking
- Maintainable codebase

Advanced architecture can always be introduced later if the system grows and the requirements justify it.

---

# Project Status

🚧 **Planning / Initial Development**

The implementation will start with the MVP scope and expand based on actual usage and requirements.