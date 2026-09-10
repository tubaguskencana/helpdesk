# WhatsApp Gateway Service (gwa-api)

A Node.js & Baileys-based WhatsApp gateway service for the Helpdesk & Ticketing System.

## Features
- **Baileys Multi-File Auth**: Persistent session pairing via QR code in `baileys_auth_info`.
- **Dual Server Architecture**:
  - `http://localhost:3000`: Web UI, QR code pairing, health status (`/status`, `/getStatus`), and session controls.
  - `http://localhost:3001`: Direct message sending (`/sendMessage`).
- **Seamless Helpdesk Integration**: Exposes REST endpoints (`/status`, `/session/start`, `/session/disconnect`, `/message/send`) consumed by the Laravel `WhatsAppService`.

## Getting Started

### Prerequisites
- Node.js (v18 or higher recommended)
- npm

### Installation
From the root of the project or inside `gwa-api`:
```bash
cd gwa-api
npm install
```

### Running the Gateway Service
```bash
npm start
```

Once running:
1. The service listens on `http://localhost:3000`.
2. Open the Helpdesk admin panel at `/admin/notifications/whatsapp` or `http://localhost:3000`.
3. Scan the QR code using WhatsApp on your mobile device (Linked Devices).
4. Once linked, the Helpdesk system will automatically show **Connected** and dispatch ticket notifications via WhatsApp.
