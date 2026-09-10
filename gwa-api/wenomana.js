"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Wenomana = void 0;
const express_1 = __importDefault(require("express"));
const path_1 = __importDefault(require("path"));
class Wenomana {
    constructor(express, ex2) {
        this.express = express;
        this.ex2 = ex2;
        this.ready = false;
    }
    start() {
        const _ex = this.express;
        const self = this;
        _ex.use(express_1.default.json());
        _ex.set("port", 3001);
        _ex.listen(_ex.get("port"), '127.0.0.1', () => {
            console.info("Application listening on port http://127.0.0.1:" + _ex.get("port"));
        });
        const _ex2 = this.ex2;
        _ex2.use(express_1.default.json());
        _ex2.set("port", 3000);
        _ex2.listen(_ex2.get("port"), () => {
            console.info("Application listening on port http://127.0.0.1:" + _ex2.get("port"));
        });
        _ex2.get('/', function (req, res) {
            res.sendFile(path_1.default.join(__dirname + (self.ready ? '/index_external.html' : '/auth_external.html')));
        });
        _ex2.get('/qrcode.js', function (req, res) {
            res.sendFile(path_1.default.join(__dirname + '/qrcode.min.js'));
        });
        _ex2.get('/helper_index.min.js', function (req, res) {
            res.sendFile(path_1.default.join(__dirname + '/helper_index.min.js'));
        });
        _ex2.get('/getStatus', function (req, res) {
            if (self.ready) {
                res.send('WA_IS_READY');
            }
            else {
                res.send(self.qr || '');
            }
        });
        _ex2.get('/status', function (req, res) {
            res.json({
                status: self.ready ? 'CONNECTED' : (self.qr ? 'QR_REQUIRED' : 'DISCONNECTED'),
                account: self.account || (self.ready ? 'WhatsApp Gateway' : null),
                qr_code: self.qr || null,
                ready: self.ready
            });
        });
        _ex2.post('/session/start', function (req, res) {
            if (!self.ready && self.restartSession) {
                self.restartSession(true);
            }
            res.json({
                status: self.ready ? 'CONNECTED' : (self.qr ? 'QR_REQUIRED' : 'CONNECTING'),
                qr_code: self.qr || null,
                ready: self.ready,
                message: 'Permintaan sesi WhatsApp diproses. QR Code sedang dimuat.'
            });
        });
        _ex2.post('/session/disconnect', function (req, res) {
            if (self.logout) self.logout();
            self.ready = false;
            self.qr = undefined;
            self.account = null;
            res.json({ status: 'DISCONNECTED' });
        });
        _ex2.post('/message/send', function (req, res) {
            const pnumber = req.body.phone || req.body.pnumber;
            const message = req.body.message;
            if (!pnumber || !message) {
                return res.status(400).json({ error: 'Phone number and message are required' });
            }
            if (!self.ready) {
                return res.status(503).json({ error: 'WhatsApp is not connected yet' });
            }
            self.sendMessage({ text: message }, `${pnumber}@s.whatsapp.net`, 100)
                .then(() => res.json({ success: true, message: 'Message sent' }))
                .catch((err) => res.status(500).json({ error: String(err) }));
        });
        _ex.post('/sendMessage', function (req, res) {
            var _a;
            const body = req.body;
            res.statusCode = 400;
            let err_msg = null;
            try {
                res.statusCode = 406;
                const pnumber = body.pnumber ? body.pnumber : '6285711489121';
                const message = body.message;
                const delay = (_a = body.delay) !== null && _a !== void 0 ? _a : 100;
                if (!pnumber && pnumber.length < 5 || pnumber[0] == '0' || !self.isNumeric(pnumber))
                    res.end(self.throwError(err_msg = `Isi nomor telepon yang valid! Terisi (+${pnumber})`));
                if (message.length < 5)
                    res.end(self.throwError(err_msg = `Pesan tidak boleh kosong!`));
                res.statusCode = 400;
                if (err_msg == null) {
                    // const productsList = new List(
                    // "Here's our list of products at 50% off",
                    // "View all products",
                    // [
                    //     {
                    //     title: "Products list",
                    //     rows: [
                    //         { id: "apple", title: "Apple" },
                    //         { id: "mango", title: "Mango" },
                    //         { id: "banana", title: "Banana" },
                    //     ],
                    //     },
                    // ],
                    // "Please select a product"
                    // );
                    // client.sendMessage(`${pnumber}@c.us`, productsList);
                    console.log(`SEND MESSAGE : +${pnumber}@s.whatsapp.net`);
                    self.sendMessage({ text: message }, `${pnumber}@s.whatsapp.net`, delay).then((value) => {
                        res.statusCode = 200;
                        res.end(`<div><pre class="inline text-black mr-2">${self.getTime()}</pre>Send Message to +${req.body.pnumber}<br><div class="py-1 px-2 bg-chat">${message}</div></div>`);
                        // con.query("SELECT * FROM db_wa_api.user WHERE pnumber = "+con.escape(pnumber), function(err, result){
                        // if (err){
                        //     console.log("Error (86) " + err);
                        // }
                        // console.log("Result : " + result)
                        // let user_id = "null";
                        // if (result.length > 0){
                        //     user_id = `'${result[0].id}'`;
                        // con.query("INSERT INTO db_wa_api.message (user_id, message, send_at) VALUES ("+user_id+", "+con.escape(message)+", NOW());", function (err, result) {
                        //     if (err)
                        //         console.log("Error (108) " + err);
                        // });
                        // } else
                        // con.query("INSERT INTO db_wa_api.user (pnumber, created_at) VALUES ("+con.escape(body.pnumber)+", NOW());", function (err, result) {
                        //     if (err)
                        //         console.log("Error (95) " + err);
                        //     con.query("SELECT * FROM db_wa_api.user WHERE pnumber = "+con.escape(body.pnumber), function(err, result){
                        //         if (err){
                        //             console.log("Error (98) " + err);
                        //         }
                        //         if (result.length > 0)
                        //             user_id = `'${result[0].id}'`;
                        //         con.query("INSERT INTO db_wa_api.message (user_id, message, send_at) VALUES ("+user_id+", "+con.escape(message)+", NOW());", function (err, result) {
                        //             if (err)
                        //                 console.log("Error (108) " + err);
                        //         });
                        //     });
                        // });
                        // })
                        // return updateRequest(body, err_msg);
                    }).catch((reason) => {
                        res.end(self.throwError(err_msg = reason));
                        return; // updateRequest(body, err_msg);
                    });
                }
                else {
                    return; // updateRequest(body, err_msg);
                }
            }
            catch (err) {
                res.end(self.throwError(err_msg = err));
            }
        });
        _ex2.post('/logout', function (req, res) {
            const body = req.body;
            res.statusCode = 400;
            try {
                // self.qr = undefined;
                // self.ready = false;
                if (self.logout)
                    self.logout();
                // client.logout().then(
                //     (value)=>{
                //         res.statusCode = 200;
                //         res.end(`<div><pre class="inline text-black mr-2">${getTime()}</pre>Logout!">${message}</div></div>`);
                //     }
                // ).catch(
                //     (reason)=>{
                //         res.end(throwError(reason));
                //     }
                // );
            }
            catch (err) {
                res.end(self.throwError(err));
            }
        });
    }
    getTime() {
        const now = new Date();
        return now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
    }
    throwError(err) {
        return `<div class="text-red-800"><pre class="inline text-black mr-2">${this.getTime()}</pre>${err}</div>`;
    }
    isNumeric(str) {
        if (typeof str != "string")
            return false;
        return !isNaN(parseFloat(str));
    }
}
exports.Wenomana = Wenomana;
