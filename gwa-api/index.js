// Fixed index.js using dynamic import for Baileys with QRCode data URI support

"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });

const express_1 = __importDefault(require("express"));
const dotenv_1 = __importDefault(require("dotenv"));
const wenomana_1 = require("./wenomana");
const process_1 = __importDefault(require("process"));
const fs_1 = __importDefault(require("fs"));
const path_1 = __importDefault(require("path"));
const qrcode_1 = __importDefault(require("qrcode"));

dotenv_1.default.config();

const app = (0, express_1.default)();
const appx = (0, express_1.default)();

const wenomana = new wenomana_1.Wenomana(app, appx);

function clearAuthInfo() {
    try {
        const authDir = path_1.default.join(__dirname, 'baileys_auth_info');
        if (fs_1.default.existsSync(authDir)) {
            const files = fs_1.default.readdirSync(authDir);
            for (const f of files) {
                try {
                    fs_1.default.unlinkSync(path_1.default.join(authDir, f));
                } catch (e) {}
            }
            console.log("Stale auth info cleared successfully.");
        }
    } catch (err) {
        console.error("Error clearing auth info:", err);
    }
}

let currentSock = null;

const startSock = (attempt) => __awaiter(void 0, void 0, void 0, function* () {
    const baileys = yield import("@whiskeysockets/baileys");

    if (wenomana) {
        const { state, saveCreds } = yield baileys.useMultiFileAuthState("baileys_auth_info");

        const { version, isLatest } = yield baileys.fetchLatestBaileysVersion();
        console.log(`using WA v${version.join('.')}, isLatest: ${isLatest}`);

        const sock = baileys.default({
            version,
            printQRInTerminal: true,
            auth: state,
            generateHighQualityLinkPreview: true,
        });

        currentSock = sock;

        const sendMessageWTyping = (msg, jid, time_delay) => __awaiter(void 0, void 0, void 0, function* () {
            yield sock.presenceSubscribe(jid);
            yield baileys.delay(time_delay / 4);
            yield sock.sendPresenceUpdate("composing", jid);
            yield baileys.delay(time_delay);
            yield sock.sendPresenceUpdate("paused", jid);
            return yield sock.sendMessage(jid, msg);
        });

        wenomana.sendMessage = sendMessageWTyping;

        sock.ev.process((events) => __awaiter(void 0, void 0, void 0, function* () {
            if (events['connection.update']) {
                const update = events['connection.update'];
                const { connection, lastDisconnect, qr } = update;

                // Whenever a QR is emitted by Baileys, generate Data URI image immediately
                if (qr) {
                    console.log("New QR Code string received from Baileys. Converting to PNG Data URI...");
                    wenomana.rawQr = qr;
                    try {
                        const dataUrl = yield qrcode_1.default.toDataURL(qr, { width: 320, margin: 2 });
                        wenomana.qr = dataUrl;
                        console.log("QR Code PNG Data URI generated successfully.");
                    } catch (qrErr) {
                        console.error("Failed to generate QR Data URI, falling back to raw QR:", qrErr);
                        wenomana.qr = qr;
                    }
                }

                if (connection === 'close') {
                    wenomana.ready = false;
                    const statusCode = lastDisconnect?.error?.output?.statusCode;
                    console.log(`Baileys connection closed. Status code: ${statusCode}`);

                    // 401: Logged Out or invalid credentials -> clear stale auth info so QR can generate next time
                    if (statusCode === 401 || statusCode === 403 || statusCode === 428) {
                        console.log("Session logged out or stale credentials. Resetting auth info...");
                        clearAuthInfo();
                        wenomana.qr = undefined;
                        wenomana.rawQr = undefined;
                    }

                    if (attempt < 10) {
                        setTimeout(() => startSock(attempt + 1), 2000);
                    } else {
                        console.log("Retrying connection after cooldown (15s)...");
                        setTimeout(() => startSock(0), 15000);
                    }
                } else if (connection === 'open') {
                    console.log("WhatsApp connection is OPEN and READY!");
                    wenomana.ready = true;
                    wenomana.qr = undefined;
                    wenomana.rawQr = undefined;
                    const userPhone = sock?.user?.id ? sock.user.id.split(':')[0] : 'Connected';
                    wenomana.account = userPhone;
                    console.log(`Paired account: ${userPhone}`);
                }
            }

            if (events["creds.update"]) {
                yield saveCreds();
            }
        }));

        wenomana.logout = () => {
            try {
                if (sock) sock.logout();
            } catch (e) {}
            clearAuthInfo();
            wenomana.ready = false;
            wenomana.qr = undefined;
            wenomana.account = null;
        };

        return sock;
    }
});

wenomana.restartSession = (clearAuth = true) => {
    console.log("Restarting WhatsApp session. clearAuth:", clearAuth);
    if (clearAuth) {
        clearAuthInfo();
    }
    wenomana.ready = false;
    wenomana.qr = undefined;
    wenomana.account = null;
    try {
        if (currentSock) {
            currentSock.end(new Error("Manual session restart"));
        }
    } catch (e) {}
    setTimeout(() => startSock(0), 1000);
};

process_1.default.on('uncaughtException', (err) => {
    const now = new Date();
    const errorMessage = `${now.toISOString()} - ${JSON.stringify(err)}\n`;
    console.error("Uncaught exception:", err);
    fs_1.default.appendFile('error.log', errorMessage, (appendErr) => {
        if (appendErr)
            console.error('Error while writing to error.log:', appendErr);
    });
});

wenomana.start();
startSock(0);
