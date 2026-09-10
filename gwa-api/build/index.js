"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
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
const baileys_1 = __importStar(require("@whiskeysockets/baileys"));
const express_1 = __importDefault(require("express"));
const dotenv_1 = __importDefault(require("dotenv"));
const wenomana_1 = require("./wenomana");
dotenv_1.default.config();
const app = (0, express_1.default)();
const appx = (0, express_1.default)();
const port = process.env.PORT;
// const logger = MAIN_.child({ })
// logger.level = 'info'
const wenomana = new wenomana_1.Wenomana(app, appx
// , {
// base : process.env.WENOMANA_BASEPOINT,
// end : process.env.WENOMANA_ENDPOINT
// }
);
const useStore = false; //!process.argv.includes('--no-store')
const doReplies = false; //!process.argv.includes('--no-reply')
// external map to store retry counts of messages when decryption/encryption fails
// keep this out of the socket itself, so as to prevent a message decryption/encryption loop across socket restarts
// const msgRetryCounterMap: MessageRetryMap = { }
// the store maintains the data of the WA connection in memory
// can be written out to a file & read from it
// const store = useStore ? makeInMemoryStore({ logger }) : undefined
// store?.readFromFile('./baileys_store_multi.json')
// // save every 10s
// setInterval(() => {
// 	store?.writeToFile('./baileys_store_multi.json')
// }, 10_000)
// start a whatsapp connection
const startSock = (attempt) => __awaiter(void 0, void 0, void 0, function* () {
    if (wenomana) {
        const { state, saveCreds } = yield (0, baileys_1.useMultiFileAuthState)('baileys_auth_info');
        // fetch latest version of WA Web
        const { version, isLatest } = yield (0, baileys_1.fetchLatestBaileysVersion)();
        console.log(`using WA v${version.join('.')}, isLatest: ${isLatest}`);
        const sock = (0, baileys_1.default)({
            version,
            // logger,
            printQRInTerminal: true,
            auth: state,
            //  {
            // 	// creds: state.creds,
            // 	/** caching makes the store faster to send/recv messages */
            // 	// keys: makeCacheableSignalKeyStore(state.keys, logger),
            // },
            // msgRetryCounterMap,
            generateHighQualityLinkPreview: true,
            // ignore all broadcast messages -- to receive the same
            // comment the line below out
            // shouldIgnoreJid: jid => isJidBroadcast(jid),
            // implement to handle retries
            // getMessage: async key => {
            // if(store) {
            // 	const msg = await store.loadMessage(key.remoteJid!, key.id!)
            // 	return msg?.message || undefined
            // }
            // only if store is present
            // 	return {
            // 		conversation: 'hello'
            // 	}
            // }
        });
        // store?.bind(sock.ev)
        const sendMessageWTyping = (msg, jid, time_delay) => __awaiter(void 0, void 0, void 0, function* () {
            yield sock.presenceSubscribe(jid);
            yield (0, baileys_1.delay)(time_delay / 4);
            yield sock.sendPresenceUpdate('composing', jid);
            yield (0, baileys_1.delay)(time_delay);
            yield sock.sendPresenceUpdate('paused', jid);
            yield sock.sendMessage(jid, msg);
        });
        wenomana.sendMessage = sendMessageWTyping;
        // the process function lets you process all events that just occurred
        // efficiently in a batch
        sock.ev.process(
        // events is a map for event name => event data
        (events) => __awaiter(void 0, void 0, void 0, function* () {
            // something about the connection changed
            // maybe it closed, or we received all offline message or connection opened
            if (events['connection.update']) {
                const update = events['connection.update'];
                const { connection, lastDisconnect } = update;
                switch (connection) {
                    case 'close':
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
                    case 'open':
                        wenomana.ready = true;
                        wenomana.logout = sock.logout;
                        break;
                    default:
                        wenomana.qr = update.qr;
                }
            }
            // credentials updated -- save them
            if (events['creds.update']) {
                yield saveCreds();
            }
            // if(events.call) {
            // 	console.log('recv call event', events.call)
            // }
            // history received
            // if (events['messaging-history.set']) {
            // 	const { chats, contacts, messages, isLatest } = events['messaging-history.set']
            // 	console.log(`recv ${chats.length} chats, ${contacts.length} contacts, ${messages.length} msgs (is latest: ${isLatest})`)
            // }
            // // received a new message
            // if (events['messages.upsert']) {
            // 	const upsert = events['messages.upsert']
            // 	console.log('recv messages ', JSON.stringify(upsert, undefined, 2))
            // 	if (upsert.type === 'notify') {
            // 		for (const msg of upsert.messages) {
            // 			if (!msg.key.fromMe) {
            // 				console.log('replying to', msg.key.remoteJid)
            // 				await sock!.readMessages([msg.key])
            // 				await sendMessageWTyping({ text: "Anggot Marronggot menulis : " + msg.message.conversation }, msg.key.remoteJid!)
            // 			}
            // 		}
            // 	}
            // }
            // // messages updated like status delivered, message deleted etc.
            // if (events['messages.update']) {
            // 	console.log('messages.update', events['messages.update'])
            // }
            // if (events['message-receipt.update']) {
            // 	console.log('messages-receipt.update', events['message-receipt.update'])
            // }
            // if (events['messages.reaction']) {
            // 	console.log('messages.reaction', events['messages.reaction'])
            // }
            // if (events['presence.update']) {
            // 	console.log('presence.update', events['presence.update'])
            // }
            // if (events['chats.update']) {
            // 	console.log('chats.update', events['chats.update'])
            // }
            // if (events['contacts.update']) {
            // 	for (const contact of events['contacts.update']) {
            // 		if (typeof contact.imgUrl !== 'undefined') {
            // 			const newUrl = contact.imgUrl === null
            // 				? null
            // 				: await sock!.profilePictureUrl(contact.id!).catch(() => null)
            // 			console.log(
            // 				`contact ${contact.id} has a new profile pic: ${newUrl}`,
            // 			)
            // 		}
            // 	}
            // }
            // if (events['chats.delete']) {
            // 	console.log('chats deleted ', events['chats.delete'])
            // }
        }));
        return sock;
    }
});
wenomana.start();
startSock(0);
