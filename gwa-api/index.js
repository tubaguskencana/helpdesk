// Fixed index.js using dynamic import for Baileys (compatible with CommonJS)

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

dotenv_1.default.config();

const app = (0, express_1.default)();
const appx = (0, express_1.default)();
const port = process.env.PORT;

const wenomana = new wenomana_1.Wenomana(app, appx);

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

        const sendMessageWTyping = (msg, jid, time_delay) => __awaiter(void 0, void 0, void 0, function* () {
            yield sock.presenceSubscribe(jid);
            yield baileys.delay(time_delay / 4);
            yield sock.sendPresenceUpdate("composing", jid);
            yield baileys.delay(time_delay);
            yield sock.sendPresenceUpdate("paused", jid);
            yield sock.sendMessage(jid, msg);
        });

        wenomana.sendMessage = sendMessageWTyping;

        sock.ev.process((events) => __awaiter(void 0, void 0, void 0, function* () {
            if (events['connection.update']) {
                const update = events['connection.update'];
                const { connection, lastDisconnect } = update;

                switch (connection) {
                    case "close":
                        wenomana.ready = false;
                        wenomana.qr = undefined;

                        if (attempt < 5)
                            setTimeout(() => startSock(attempt + 1), 1000);
                        else if (attempt < 20)
                            setTimeout(() => startSock(attempt + 1), 60000);
                        else {
                            sock.logout();
                            console.log("Attempt always failed, retrying Stop and Logout! EXIT");
                            process.exit();
                        }
                        break;

                    case "open":
                        wenomana.ready = true;
                        wenomana.logout = sock.logout;
                        break;

                    default:
                        wenomana.qr = update.qr;
                }
            }

            if (events["creds.update"]) {
                yield saveCreds();
            }
        }));

        return sock;
    }
});

process_1.default.on('uncaughtException', (err) => {
    const now = new Date();
    const errorMessage = `${now.toISOString()} - ${JSON.stringify(err)}\n`;
    fs_1.default.appendFile('error.log', errorMessage, (appendErr) => {
        if (appendErr)
            console.error('Error while writing to error.log:', appendErr);
        process_1.default.exit(1);
    });
});


wenomana.start();
startSock(0);

