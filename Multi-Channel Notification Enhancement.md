# Multi-Channel Notification Enhancement

Enhancement untuk Helpdesk & Ticketing System dengan menambahkan sistem notifikasi melalui beberapa channel:

1. **Web Notification**
2. **Web Push Notification**
3. **WhatsApp Notification**

Fitur ini ditambahkan ke sistem helpdesk yang sudah berjalan. Tidak ada perubahan besar pada core ticketing workflow.

Tujuannya adalah supaya user tetap mendapatkan informasi ketika terjadi perubahan pada ticket, meskipun mereka sedang tidak membuka aplikasi.

---

# Overview

Saat ini sistem sudah memiliki notifikasi di dalam aplikasi.

Feature enhancement ini akan menambahkan dua channel tambahan:

```text
                    ┌─────────────────────┐
                    │   Helpdesk System   │
                    └──────────┬──────────┘
                               │
                  Notification Event
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
       Web Notification   Web Push        WhatsApp
          (Bell)         Notification     Notification
```

Ketiga channel berjalan berdasarkan event yang sama.

Contoh:

```text
Ticket Assigned
      │
      ├── Web Notification
      ├── Web Push
      └── WhatsApp
```

Namun setiap channel memiliki behavior yang berbeda.

---

# Notification Channels

## 1. Web Notification

Web Notification merupakan notification yang muncul di dalam aplikasi.

Notification ditampilkan melalui icon bell pada profile/topbar.

Contoh:

```text
🔔 5
```

Jika terdapat notification yang belum dibaca, counter akan bertambah.

### Behavior

- Notification disimpan di database
- Memiliki status read/unread
- Counter hanya menghitung notification yang belum dibaca
- Klik notification akan mengarah ke halaman yang relevan
- Setelah notification dibuka, status berubah menjadi read

Contoh:

```text
🔔 3

Ticket HD-2026-000123 telah di-assign kepada Anda
Ticket HD-2026-000124 mendapatkan reply baru
Ticket HD-2026-000125 telah di-resolve
```

Ketika user klik notification:

```text
→ /tickets/HD-2026-000123
```

Notification tersebut kemudian dianggap sudah dibaca.

---

# 2. Web Push Notification

Web Push digunakan untuk memberikan notification meskipun user sedang tidak aktif melihat halaman aplikasi.

Contoh:

```text
Helpdesk

Ticket HD-2026-000123
mendapatkan reply baru.
```

Web Push tidak perlu memiliki read/unread state di database.

Status read pada browser cukup ditangani oleh behavior notification browser.

Jika notification diklik, user diarahkan ke URL ticket terkait.

---

# 3. WhatsApp Notification

WhatsApp digunakan sebagai channel notification tambahan.

User akan mendapatkan notification melalui WhatsApp apabila:

1. WhatsApp integration sudah aktif
2. User memiliki nomor telepon
3. Event tersebut memang dikonfigurasi untuk mengirim WhatsApp notification

Jika user tidak memiliki nomor telepon:

```text
No Phone Number
      ↓
Skip WhatsApp
      ↓
Continue Web Notification
```

Tidak boleh menyebabkan notification event gagal hanya karena user tidak memiliki nomor WhatsApp.

---

# WhatsApp Integration

## General Flow

Administrator dapat mengaktifkan WhatsApp notification dari halaman administration.

Contoh:

```text
Administration
    │
    └── WhatsApp
          │
          ├── Connection Status
          ├── QR Code
          ├── Scan QR
          └── Disconnect
```

Administrator akan melakukan scan QR menggunakan WhatsApp.

Setelah proses authentication berhasil:

```text
Disconnected
     ↓
QR Code
     ↓
Administrator scans QR
     ↓
Connecting
     ↓
Connected
     ↓
WhatsApp Notification Enabled
```

---

# WhatsApp Connection

WhatsApp integration sebaiknya dibuat sebagai service terpisah dari ticketing logic.

Recommended architecture:

```text
Laravel Application
        │
        │
        ▼
Notification Service
        │
        ├── Web Notification
        ├── Web Push Service
        │
        └── WhatsApp Service
                  │
                  ▼
             WhatsApp Client
```

Laravel tidak sebaiknya menangani seluruh WhatsApp session secara langsung.

Untuk pendekatan seperti Baileys, gunakan service/process terpisah yang bertanggung jawab terhadap:

- WhatsApp connection
- QR generation
- Session
- Authentication state
- Reconnection
- Message sending
- Connection status

Laravel berkomunikasi dengan service tersebut melalui API/internal communication.

---

# Recommended WhatsApp Architecture

```text
┌─────────────────────┐
│      Laravel        │
│    Helpdesk App     │
└──────────┬──────────┘
           │
           │ HTTP / Internal API
           ▼
┌─────────────────────┐
│ WhatsApp Service    │
│                     │
│ - Baileys Client    │
│ - Session Manager   │
│ - QR Handler        │
│ - Message Sender    │
│ - Reconnection      │
└──────────┬──────────┘
           │
           ▼
       WhatsApp
```

Dengan architecture ini, jika WhatsApp service mengalami masalah, core helpdesk tetap dapat digunakan.

---

# WhatsApp Connection Status

Administrator harus dapat melihat status koneksi.

Recommended states:

```text
DISCONNECTED
CONNECTING
QR_REQUIRED
CONNECTED
RECONNECTING
ERROR
```

Contoh UI:

```text
WhatsApp Integration

Status:
● Connected

Account:
+62xxxxxxxxxx

Connected Since:
10 September 2026

[ Disconnect ]
```

Jika belum terhubung:

```text
WhatsApp Integration

Status:
○ Disconnected

[ Connect WhatsApp ]
```

Setelah connect:

```text
Scan QR Code

[ QR CODE ]

Open WhatsApp on your phone
→ Linked Devices
→ Link a Device
→ Scan this QR code
```

---

# WhatsApp Session

Session WhatsApp harus disimpan secara persistent.

Jangan menyimpan session hanya di memory process.

Contoh:

```text
storage/
└── whatsapp/
    └── auth/
```

atau menggunakan persistent volume apabila service dijalankan menggunakan Docker.

Session harus tetap tersedia setelah:

- Application restart
- WhatsApp service restart
- Server restart

Jika session expired atau logout:

```text
CONNECTED
    ↓
SESSION INVALID
    ↓
DISCONNECTED
    ↓
QR_REQUIRED
```

Administrator kemudian melakukan scan ulang.

---

# User Phone Number

User profile ditambahkan field:

```text
phone
```

Contoh:

```text
Name:
John Doe

Email:
john@company.com

Phone:
+628123456789
```

Phone number bersifat optional.

---

# Phone Number Rules

Jika phone number kosong:

```text
phone = null
```

Maka:

```text
WhatsApp Notification
        ↓
SKIP
```

Tetapi notification lain tetap berjalan.

Contoh:

```text
Ticket Assigned

Web Notification  → YES
Web Push           → YES
WhatsApp           → SKIP
```

---

# Phone Number Normalization

Nomor telepon harus dinormalisasi sebelum dikirim ke WhatsApp.

Contoh input:

```text
081234567890
```

Disimpan atau diproses menjadi format international:

```text
6281234567890
```

Format yang direkomendasikan:

```text
country code + phone number
```

Untuk Indonesia:

```text
08xxxxxxxxxx
        ↓
628xxxxxxxxxx
```

Sebaiknya normalisasi dilakukan pada backend dan tidak bergantung pada input user.

---

# Notification Events

Notification system harus menggunakan event yang sama untuk ketiga channel.

Contoh event yang perlu didukung:

## Ticket Created

```text
Ticket berhasil dibuat
```

Recipient:

- Requester
- Support team / assigned recipient jika diperlukan

---

## Ticket Assigned

```text
Ticket di-assign ke agent
```

Recipient:

- Assigned Agent

---

## Ticket Reply

```text
Ada reply baru pada ticket
```

Recipient:

- Requester
- Agent yang relevan

---

## Ticket Status Changed

Contoh:

```text
Open
↓
In Progress
```

atau:

```text
In Progress
↓
Resolved
```

Recipient:

- Requester
- Relevant Agent

---

## Ticket Priority Changed

Contoh:

```text
Medium
↓
High
```

Recipient:

- Assigned Agent
- Supervisor jika diperlukan

---

## Ticket Resolved

Recipient:

- Requester

---

## Ticket Closed

Recipient:

- Requester

---

## Ticket Reopened

Recipient:

- Requester
- Assigned Agent

---

# Notification Matrix

Setiap notification event harus memiliki konfigurasi channel.

Contoh:

| Event | Web | Web Push | WhatsApp |
|---|---:|---:|---:|
| Ticket Created | Yes | Yes | Yes |
| Ticket Assigned | Yes | Yes | Yes |
| New Reply | Yes | Yes | Yes |
| Status Changed | Yes | Yes | Yes |
| Priority Changed | Yes | Yes | Yes |
| Ticket Resolved | Yes | Yes | Yes |
| Ticket Closed | Yes | Yes | Yes |
| Ticket Reopened | Yes | Yes | Yes |

Namun WhatsApp hanya dikirim jika:

```text
WhatsApp Connected
AND
Recipient has phone number
AND
Event allows WhatsApp
```

---

# Notification Decision Flow

```text
Notification Event
        │
        ▼
Determine Recipient
        │
        ├──────────────────────┐
        │                      │
        ▼                      ▼
Create Web Notification    Web Push
        │                      │
        │                      └── Send if subscribed
        │
        ▼
Check WhatsApp
        │
        ├── Not Connected → Skip
        │
        ├── No Phone      → Skip
        │
        └── Connected + Phone
                    │
                    ▼
             Queue WhatsApp
                    │
                    ▼
               Send Message
```

WhatsApp failure tidak boleh membatalkan web notification.

---

# Notification Preferences

Sebaiknya notification settings dibuat configurable.

Contoh:

```text
Notification Settings

Ticket Assigned

[x] Web Notification
[x] Web Push
[x] WhatsApp

New Reply

[x] Web Notification
[x] Web Push
[x] WhatsApp

Ticket Resolved

[x] Web Notification
[x] Web Push
[x] WhatsApp
```

Untuk MVP, setting global per event sudah cukup.

User-level preferences dapat ditambahkan kemudian.

---

# Web Notification Data Model

Gunakan notification table yang sudah sesuai dengan Laravel Notification atau struktur notification existing.

Recommended information:

```text
id
user_id
type
title
message
url
read_at
created_at
updated_at
```

Contoh:

```text
type:
ticket_assigned

title:
Ticket Assigned

message:
Ticket HD-2026-000123 telah di-assign kepada Anda.

url:
/tickets/HD-2026-000123

read_at:
NULL
```

---

# Web Notification Read State

Web notification merupakan satu-satunya channel yang wajib memiliki read state.

### Unread

```text
read_at = NULL
```

### Read

```text
read_at = 2026-09-10 15:30:00
```

Counter:

```text
COUNT(read_at IS NULL)
```

Contoh:

```text
🔔 7
```

Setelah user membuka 1 notification:

```text
🔔 6
```

Jika semua notification dibaca:

```text
🔔
```

atau:

```text
🔔 0
```

tergantung desain UI existing.

---

# Notification Redirect

Setiap web notification harus memiliki destination URL.

Contoh:

```text
Ticket Assigned
        ↓
/tickets/HD-2026-000123
```

Contoh lain:

```text
New Reply
        ↓
/tickets/HD-2026-000123#reply-456
```

Dengan demikian notification tidak hanya memberi informasi tetapi juga menjadi shortcut ke context yang relevan.

---

# Web Push

Web Push membutuhkan browser subscription.

Data minimal:

```text
user_id
endpoint
public_key
auth_token
created_at
updated_at
```

Satu user dapat memiliki lebih dari satu subscription karena bisa menggunakan:

- Desktop
- Laptop
- Browser berbeda

Contoh:

```text
User
 ├── Chrome Desktop
 ├── Edge Laptop
 └── Mobile Browser
```

---

# Web Push Flow

```text
User Login
    ↓
Browser requests notification permission
    ↓
User allows notification
    ↓
Create Push Subscription
    ↓
Store subscription
    ↓
Notification Event
    ↓
Send Web Push
```

Jika user menolak permission:

```text
Web Push
    ↓
Skip
```

Web notification tetap berjalan.

---

# WhatsApp Message Format

WhatsApp message harus dibuat singkat.

Jangan mengirim seluruh isi ticket.

Contoh:

```text
*Helpdesk Notification*

Ticket: HD-2026-000123
Subject: Printer Finance Tidak Bisa Digunakan

Status: In Progress
Priority: High

Ada update baru pada ticket Anda.

Lihat ticket:
https://helpdesk.company.com/tickets/HD-2026-000123
```

---

# WhatsApp Message Rules

Message harus:

- Singkat
- Mudah dibaca
- Memiliki ticket number
- Memiliki context singkat
- Memiliki link ke aplikasi
- Tidak mengirim data internal/sensitive
- Tidak mengirim internal notes

Internal note tidak boleh masuk ke WhatsApp requester.

---

# WhatsApp Link

Setiap WhatsApp notification yang berkaitan dengan ticket harus menyertakan link.

Contoh:

```text
Lihat ticket:
https://helpdesk.company.com/tickets/HD-2026-000123
```

Link harus mengarah ke halaman yang relevan.

Jika sistem menggunakan authentication, user tetap harus login sebelum melihat detail ticket.

---

# Queue System

WhatsApp dan Web Push sebaiknya dikirim menggunakan queue.

Jangan melakukan pengiriman WhatsApp secara synchronous di request utama.

Bad:

```text
User changes ticket status
        ↓
Laravel
        ↓
Send WhatsApp
        ↓
Wait
        ↓
Database update
        ↓
Response
```

Recommended:

```text
User changes ticket status
        ↓
Database update
        ↓
Create notifications
        ↓
Dispatch Jobs
        ↓
Return response
        │
        ├── Web Push Job
        └── WhatsApp Job
```

Dengan demikian user tidak perlu menunggu WhatsApp selesai dikirim.

---

# WhatsApp Job

Contoh job:

```text
SendWhatsAppNotification
```

Payload:

```text
user_id
phone
notification_type
message
ticket_id
```

Job bertugas:

1. Validate recipient
2. Check WhatsApp connection
3. Check phone number
4. Generate message
5. Send message
6. Handle error
7. Retry if necessary

---

# Retry Strategy

WhatsApp message mungkin gagal karena:

- Connection lost
- WhatsApp service restart
- Temporary network issue
- Recipient unavailable
- Session expired

Job harus memiliki retry mechanism.

Example:

```text
Attempt 1
    ↓
Failed
    ↓
Wait
    ↓
Attempt 2
    ↓
Failed
    ↓
Wait
    ↓
Attempt 3
    ↓
Failed
    ↓
Mark as failed
```

Failure WhatsApp tidak boleh mengubah status ticket menjadi failed.

---

# WhatsApp Message Log

Walaupun WhatsApp notification tidak membutuhkan read/unread flag, sebaiknya sistem tetap menyimpan log pengiriman.

Contoh table:

```text
whatsapp_messages
```

Fields:

```text
id

user_id
ticket_id

phone
message
notification_type

status

sent_at
failed_at

error_message

created_at
updated_at
```

Status:

```text
pending
sent
failed
```

Ini bukan read/unread state.

Fungsinya untuk audit dan troubleshooting.

---

# Why Keep WhatsApp Logs?

Contoh ketika user mengatakan:

> "Saya tidak menerima WhatsApp."

Administrator dapat melihat:

```text
Ticket: HD-2026-000123

WhatsApp:
Status: Sent
Sent At: 15:32
Phone: 628123456789
```

atau:

```text
Status: Failed
Error: WhatsApp session disconnected
```

Hal ini akan sangat membantu saat troubleshooting.

---

# WhatsApp Administration Page

Tambahkan menu:

```text
Administration
└── Notifications
    └── WhatsApp
```

Halaman:

```text
WhatsApp Integration

Connection Status
────────────────────────

● Connected

Account
────────────────────────
+62xxxxxxxxxx

Session
────────────────────────
Connected since:
10 September 2026

Notifications
────────────────────────

[x] Enable WhatsApp Notifications

[ Disconnect WhatsApp ]
```

Jika disconnected:

```text
WhatsApp Integration

○ Disconnected

[ Connect WhatsApp ]
```

---

# QR Code Flow

Ketika administrator menekan:

```text
Connect WhatsApp
```

Laravel meminta WhatsApp service membuat session baru.

Flow:

```text
Admin
 ↓
Connect WhatsApp
 ↓
Laravel
 ↓
WhatsApp Service
 ↓
Generate QR
 ↓
Display QR
 ↓
Admin scans QR
 ↓
WhatsApp authenticates
 ↓
Connection status = CONNECTED
```

UI sebaiknya melakukan polling atau menggunakan WebSocket/SSE untuk mengetahui perubahan status.

Tidak perlu refresh browser secara manual.

---

# Security Considerations

WhatsApp session merupakan credential yang sangat penting.

Session/authentication files:

- Tidak boleh disimpan di public directory
- Tidak boleh masuk Git repository
- Tidak boleh masuk log
- Tidak boleh ditampilkan melalui browser
- Harus memiliki permission filesystem yang sesuai

Tambahkan ke `.gitignore`:

```text
/whatsapp-session/
/storage/whatsapp/
```

atau lokasi persistent storage yang digunakan oleh WhatsApp service.

---

# Environment Configuration

Contoh konfigurasi:

```env
WHATSAPP_ENABLED=false

WHATSAPP_SERVICE_URL=http://whatsapp-service:3000
WHATSAPP_SERVICE_TOKEN=change-this-token

WHATSAPP_SESSION_PATH=/data/whatsapp-session
```

Laravel hanya menyimpan konfigurasi connection ke WhatsApp service.

Session authentication tetap dikelola oleh WhatsApp service.

---

# Suggested Laravel Structure

Tambahkan beberapa component tanpa mengubah struktur existing terlalu banyak.

```text
app/
├── Jobs/
│   ├── SendWebPushNotification.php
│   └── SendWhatsAppNotification.php
│
├── Notifications/
│   ├── TicketAssignedNotification.php
│   ├── TicketReplyNotification.php
│   ├── TicketStatusChangedNotification.php
│   └── TicketResolvedNotification.php
│
├── Services/
│   ├── NotificationService.php
│   └── WhatsAppService.php
│
└── Http/
    └── Controllers/
        └── Admin/
            └── WhatsAppController.php
```

---

# Notification Service

Semua notification event sebaiknya melewati satu service.

Contoh konsep:

```text
NotificationService
        │
        ├── notifyWeb()
        │
        ├── notifyPush()
        │
        └── notifyWhatsApp()
```

Ticket controller tidak perlu mengetahui bagaimana WhatsApp dikirim.

Contoh:

```text
TicketController
      ↓
NotificationService
      ↓
┌─────┼─────────┐
│     │         │
Web  Push    WhatsApp
```

Dengan demikian jika WhatsApp provider diganti di kemudian hari, ticketing logic tidak perlu banyak berubah.

---

# Recommended Event Architecture

Untuk jangka panjang, lebih baik notification dipicu melalui event.

Contoh:

```text
TicketAssigned
TicketReplied
TicketStatusChanged
TicketResolved
TicketClosed
TicketReopened
```

Kemudian listener:

```text
TicketAssigned
      │
      ▼
SendTicketNotification
      │
      ├── Web
      ├── Web Push
      └── WhatsApp
```

Ini membuat notification logic tidak tersebar di banyak controller.

---

# Notification Preferences

Global configuration:

```text
notification_settings
```

Contoh:

```text
event
web_enabled
push_enabled
whatsapp_enabled
```

Example:

```text
ticket_assigned
true
true
true
```

Jika administrator mematikan WhatsApp:

```text
ticket_assigned
true
true
false
```

Maka:

```text
Web       → Send
Web Push  → Send
WhatsApp  → Skip
```

---

# Implementation Phases

## Phase 1 — Notification Refactor

Estimated: **1–2 days**

Sebelum menambahkan WhatsApp, rapikan notification system existing.

Tasks:

- Centralize notification events
- Define notification types
- Define notification payload
- Define destination URL
- Ensure web notification read/unread works correctly
- Separate notification logic from ticket controllers

---

## Phase 2 — Web Notification Enhancement

Estimated: **1–2 days**

Tasks:

- Notification bell
- Unread counter
- Notification dropdown
- Read state
- Mark as read
- Mark all as read
- Redirect to relevant page

---

## Phase 3 — Web Push

Estimated: **2–4 days**

Tasks:

- Browser permission
- Push subscription
- Subscription storage
- Push service
- Push notification job
- Click-to-open URL
- Remove invalid subscriptions

---

## Phase 4 — WhatsApp Service

Estimated: **3–5 days**

Tasks:

- Create WhatsApp service
- Baileys integration
- Session management
- QR generation
- QR authentication
- Connection status
- Reconnection handling
- Send message API

---

## Phase 5 — Laravel WhatsApp Integration

Estimated: **2–4 days**

Tasks:

- WhatsAppService
- API authentication
- SendWhatsAppNotification Job
- Retry handling
- Error handling
- Phone number validation
- Phone number normalization
- WhatsApp message log

---

## Phase 6 — Administration UI

Estimated: **2–3 days**

Tasks:

- WhatsApp settings page
- Connection status
- QR display
- Connect button
- Disconnect button
- Enable/disable WhatsApp notification
- Connection error display

---

## Phase 7 — Notification Event Integration

Estimated: **2–3 days**

Integrate WhatsApp + Web Push into existing ticket events:

- Ticket Created
- Ticket Assigned
- Ticket Reply
- Ticket Status Changed
- Ticket Priority Changed
- Ticket Resolved
- Ticket Closed
- Ticket Reopened

---

## Phase 8 — Testing

Estimated: **2–3 days**

Test:

### Web

- Notification appears
- Unread count works
- Read state works
- Redirect works
- Mark all as read works

### Web Push

- Permission
- Subscription
- Notification delivery
- Click action
- Multiple devices
- Invalid subscription

### WhatsApp

- QR authentication
- Reconnection
- Server restart
- Session persistence
- Send message
- User without phone number
- Invalid phone number
- WhatsApp disconnected
- WhatsApp service unavailable
- Message retry

---

# Total Estimated Development Time

| Phase | Estimate |
|---|---:|
| Notification Refactor | 1–2 Days |
| Web Notification | 1–2 Days |
| Web Push | 2–4 Days |
| WhatsApp Service | 3–5 Days |
| Laravel Integration | 2–4 Days |
| Admin UI | 2–3 Days |
| Event Integration | 2–3 Days |
| Testing | 2–3 Days |

Estimated total:

**15–26 working days**

The actual duration depends heavily on the existing notification architecture and how the WhatsApp service is deployed.

---

# Final Architecture

The resulting system should look like this:

```text
                         HELPDesk
                            │
                            │
                     Ticket Event
                            │
                            ▼
                 ┌────────────────────┐
                 │ Notification       │
                 │ Service            │
                 └─────────┬──────────┘
                           │
          ┌────────────────┼────────────────┐
          │                │                │
          ▼                ▼                ▼
     Web Notification   Web Push       WhatsApp
          │                │                │
          ▼                ▼                ▼
       Database         Browser       WhatsApp Service
          │                                 │
          │                                 ▼
          │                             Baileys
          │                                 │
          │                                 ▼
          │                             WhatsApp
          │
          ▼
      🔔 Bell
      Unread Count
```

---

# Notification Behavior Summary

| Channel | Stored | Read/Unread | Counter | Click Redirect |
|---|---|---|---|---|
| Web Notification | Yes | Yes | Yes | Yes |
| Web Push | Subscription only | No | No | Yes |
| WhatsApp | Message Log | No | No | Link in message |

---

# Important Rules

### Rule 1 — Web Notification is the primary in-app notification

Every important notification should create a web notification.

```text
Event
 ↓
Web Notification
```

---

### Rule 2 — Web Push is optional

Web Push only works when the browser has an active subscription.

```text
No subscription
      ↓
Skip Push
```

Web notification tetap dibuat.

---

### Rule 3 — WhatsApp is optional

WhatsApp hanya dikirim jika:

```text
WhatsApp Connected
        AND
User has phone number
        AND
WhatsApp notification enabled
```

Jika salah satu tidak terpenuhi:

```text
Skip WhatsApp
```

Tidak boleh menyebabkan proses ticket gagal.

---

### Rule 4 — WhatsApp tidak memiliki read/unread state

WhatsApp notification hanya membutuhkan delivery log.

```text
pending
sent
failed
```

Tidak perlu:

```text
read
unread
```

---

### Rule 5 — Web Notification memiliki unread counter

Counter hanya berasal dari:

```text
read_at IS NULL
```

---

### Rule 6 — Every notification should have a destination

Web Push dan WhatsApp harus memiliki URL yang relevan.

Contoh:

```text
Ticket Assigned
        ↓
/tickets/HD-2026-000123
```

WhatsApp:

```text
Lihat ticket:
https://helpdesk.company.com/tickets/HD-2026-000123
```

---

# MVP

Untuk initial implementation, fokus pada:

- [ ] Notification event architecture
- [ ] Web notification unread counter
- [ ] Web notification read state
- [ ] Notification redirect
- [ ] Web Push subscription
- [ ] Web Push notification
- [ ] WhatsApp service
- [ ] Baileys connection
- [ ] QR Code authentication
- [ ] WhatsApp session persistence
- [ ] Connection status
- [ ] Admin connect/disconnect
- [ ] User phone number
- [ ] Phone number normalization
- [ ] WhatsApp notification job
- [ ] WhatsApp message log
- [ ] Retry mechanism
- [ ] Ticket event integration
- [ ] WhatsApp link to ticket
- [ ] Skip WhatsApp when phone number is empty
- [ ] Skip WhatsApp when connection is unavailable

---

# Future Improvements

Setelah sistem stabil, beberapa fitur bisa ditambahkan:

- Per-user notification preferences
- Per-department notification rules
- WhatsApp message templates
- Notification scheduling
- Quiet hours
- Multiple WhatsApp accounts
- Multiple WhatsApp numbers
- Notification analytics
- Failed notification dashboard
- Delivery monitoring
- Automatic reconnection
- Notification rate limiting
- Message queue monitoring

---

# Notes

WhatsApp integration should be treated as an external dependency.

The helpdesk application must remain fully functional even when WhatsApp is:

- Disconnected
- Offline
- Restarting
- Temporarily unavailable
- Unable to send a specific message

The core ticket workflow must never depend on successful WhatsApp delivery.

The preferred flow is:

```text
Ticket Action
     ↓
Database Transaction
     ↓
Create Web Notification
     ↓
Dispatch Push / WhatsApp Jobs
     ↓
Return Response
```

This keeps the ticketing system responsive and prevents external notification failures from affecting the main application.