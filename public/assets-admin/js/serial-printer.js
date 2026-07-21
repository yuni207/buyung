/**
 * serial-printer.js
 * Printer ESC/POS via Web Serial API (kabel USB/Serial)
 * Drop-in pengganti bt-printer.js
 *
 * API publik yang diekspos ke window:
 *   window.connectSerialPrinter()   – pilih port & sambungkan
 *   window.disconnectSerialPrinter() – putuskan koneksi
 *   window.kirimKeprinter(data, statusCallback) – cetak struk, return true/false
 *   window.isSerialPrinterConnected() – cek status
 *
 * Storage key: 'serial_printer_port_info' (localStorage) – menyimpan hint port
 * untuk menampilkan status saja; port fisik harus dipilih ulang tiap halaman baru
 * sesuai batasan keamanan Web Serial API.
 */

(function () {
    'use strict';

    // ── State ──────────────────────────────────────────────────────────────
    var _port       = null;   // SerialPort object
    var _writer     = null;   // WritableStreamDefaultWriter
    var _connected  = false;

    // Baud rate umum untuk printer thermal ESC/POS
    var BAUD_RATE = 9600;

    // ── ESC/POS Helpers ────────────────────────────────────────────────────
    var ESC = 0x1B;
    var GS  = 0x1D;

    function cmd() {
        return Array.from(arguments);
    }

    var INIT        = cmd(ESC, 0x40);                 // Initialize printer
    var ALIGN_LEFT  = cmd(ESC, 0x61, 0x00);
    var ALIGN_CENTER= cmd(ESC, 0x61, 0x01);
    var BOLD_ON     = cmd(ESC, 0x45, 0x01);
    var BOLD_OFF    = cmd(ESC, 0x45, 0x00);
    var DSIZE_ON    = cmd(GS,  0x21, 0x11);           // double width+height
    var DSIZE_OFF   = cmd(GS,  0x21, 0x00);
    var CUT         = cmd(GS,  0x56, 0x42, 0x00);    // partial cut
    var LF          = [0x0A];

    // Encode string ke Uint8Array (Latin-1 / ISO-8859-1 fallback)
    function encode(str) {
        var out = [];
        for (var i = 0; i < str.length; i++) {
            var c = str.charCodeAt(i);
            // Karakter khusus Indonesia yang umum
            var map = {
                'Rp': [0x52, 0x70],
            };
            // Ganti karakter > 127 ke padanan ASCII sederhana
            if (c > 127) {
                var replacements = {
                    0x2019: 0x27, // right single quote → '
                    0x201C: 0x22, // left double quote  → "
                    0x201D: 0x22, // right double quote → "
                    0x2013: 0x2D, // en dash → -
                    0x2014: 0x2D, // em dash → -
                };
                out.push(replacements[c] || 0x3F); // ? jika tidak dikenali
            } else {
                out.push(c);
            }
        }
        return new Uint8Array(out);
    }

    function line(str) {
        return Array.from(encode(str)).concat(LF);
    }

    // Pad string kanan/kiri untuk kolom 32-char (lebar kertas 58mm ~ 32 char)
    var COLS = 32;

    function padRight(str, len) {
        str = String(str);
        while (str.length < len) str += ' ';
        return str.slice(0, len);
    }

    function padLeft(str, len) {
        str = String(str);
        while (str.length < len) str = ' ' + str;
        return str.slice(-len);
    }

    function twoCol(left, right) {
        var rightLen = right.length;
        var leftLen  = COLS - rightLen;
        return padRight(left, leftLen) + right;
    }

    function separator() {
        var s = '';
        for (var i = 0; i < COLS; i++) s += '-';
        return line(s);
    }

    // ── Build ESC/POS bytes dari data transaksi ────────────────────────────
    function buildReceipt(d) {
        var bytes = [];

        function push(arr) {
            for (var i = 0; i < arr.length; i++) bytes.push(arr[i]);
        }

        var rp = function (n) {
            return 'Rp' + parseInt(n || 0).toLocaleString('id-ID');
        };

        // Init
        push(INIT);

        // Header – Nama toko
        push(ALIGN_CENTER);
        push(BOLD_ON);
        push(DSIZE_ON);
        push(line('Toko Buyung'));
        push(DSIZE_OFF);
        push(BOLD_OFF);

        push(line(d.tanggal || ''));
        push(line(d.kode    || ''));

        push(ALIGN_LEFT);
        push(separator());

        // Items
        (d.items || []).forEach(function (item) {
            // Nama barang (maks 32 char, bungkus jika perlu)
            var nama = String(item.nama || '').slice(0, COLS);
            push(line(nama));
            // Qty x Harga = Subtotal
            var detail = '  ' + item.qty + ' x ' + rp(item.harga);
            var sub    = rp(item.subtotal);
            push(line(twoCol(detail, sub)));
        });

        push(separator());

        // Ringkasan
        if ((d.subtotal || 0) !== (d.total || 0)) {
            push(line(twoCol('Subtotal', rp(d.subtotal))));
        }
        if ((d.potongan || 0) > 0) {
            push(line(twoCol('Potongan', '-' + rp(d.potongan))));
        }

        push(BOLD_ON);
        push(line(twoCol('TOTAL', rp(d.total))));
        push(BOLD_OFF);

        push(line(twoCol('Bayar', rp(d.bayar))));
        push(line(twoCol('Kembalian', rp(d.kembalian))));

        push(separator());

        // Footer
        push(ALIGN_LEFT);
        push(line('Kasir  : ' + (d.kasir  || '-')));
        push(line('Metode : ' + (d.metode || '-')));

        push(LF);
        push(ALIGN_CENTER);
        push(line('Terima kasih!'));
        push(LF);
        push(LF);
        push(LF);

        // Cut kertas
        push(CUT);

        return new Uint8Array(bytes);
    }

    // ── Perbarui badge status di halaman ──────────────────────────────────
    function updateBadge() {
        var badge      = document.getElementById('printer-status-badge');
        var btnConnect = document.getElementById('btn-connect-printer');
        var btnDisconn = document.getElementById('btn-disconnect-printer');

        if (_connected) {
            if (badge) {
                badge.className   = 'badge badge-success mr-2';
                badge.style.fontSize  = '13px';
                badge.style.padding   = '6px 12px';
                badge.textContent = '🟢 Printer Terhubung';
            }
            if (btnConnect) btnConnect.style.display = 'none';
            if (btnDisconn) btnDisconn.style.display = '';
        } else {
            if (badge) {
                badge.className   = 'badge badge-secondary mr-2';
                badge.style.fontSize  = '13px';
                badge.style.padding   = '6px 12px';
                badge.textContent = '⚫ Printer Tidak Terhubung';
            }
            if (btnConnect) btnConnect.style.display = '';
            if (btnDisconn) btnDisconn.style.display = 'none';
        }
    }

    // ── Simpan info port ke localStorage (hanya untuk display) ────────────
    function savePortInfo(port) {
        try {
            var info = port.getInfo ? port.getInfo() : {};
            localStorage.setItem('serial_printer_port_info', JSON.stringify({
                vendorId : info.usbVendorId  || null,
                productId: info.usbProductId || null,
                savedAt  : new Date().toISOString(),
            }));
        } catch (e) { /* ignore */ }
    }

    // ── connectSerialPrinter ───────────────────────────────────────────────
    window.connectSerialPrinter = async function () {
        if (!navigator.serial) {
            alert('Browser tidak mendukung Web Serial API.\nGunakan Google Chrome atau Microsoft Edge versi terbaru.');
            return false;
        }

        try {
            // Buka dialog pilih port (user gesture wajib ada)
            _port = await navigator.serial.requestPort();
            await _port.open({ baudRate: BAUD_RATE });

            _writer    = _port.writable.getWriter();
            _connected = true;

            savePortInfo(_port);
            updateBadge();

            // Kirim INIT agar printer siap
            await _writer.write(new Uint8Array(INIT));

            console.log('[serial-printer] Terhubung ke printer serial.');
            return true;
        } catch (err) {
            console.warn('[serial-printer] Gagal connect:', err);
            _connected = false;
            _port      = null;
            _writer    = null;
            updateBadge();
            return false;
        }
    };

    // ── disconnectSerialPrinter ───────────────────────────────────────────
    window.disconnectSerialPrinter = async function () {
        try {
            if (_writer) { _writer.releaseLock(); _writer = null; }
            if (_port  ) { await _port.close();   _port   = null; }
        } catch (e) { /* ignore */ }
        _connected = false;
        localStorage.removeItem('serial_printer_port_info');
        updateBadge();
        console.log('[serial-printer] Koneksi diputus.');
    };

    // ── isSerialPrinterConnected ──────────────────────────────────────────
    window.isSerialPrinterConnected = function () {
        return _connected;
    };

    /**
     * kirimKeprinter(data, statusCallback)
     *   Kompatibel dengan API bt-printer.js lama.
     *   Return true  → cetak berhasil
     *   Return false → belum terhubung (caller harus minta user connect dulu)
     */
    window.kirimKeprinter = async function (data, statusCallback) {
        var cb = statusCallback || function () {};

        if (!_connected || !_writer) {
            return false;
        }

        try {
            cb('<div class="alert alert-info">🖨️ Mengirim data ke printer...</div>');

            var bytes = buildReceipt(data);
            await _writer.write(bytes);

            cb('<div class="alert alert-success">✅ Struk berhasil dicetak!</div>');
            return true;
        } catch (err) {
            console.error('[serial-printer] Gagal cetak:', err);

            // Port mungkin terputus
            _connected = false;
            _writer    = null;
            _port      = null;
            updateBadge();

            cb('<div class="alert alert-danger">❌ Gagal cetak: ' + err.message +
               '<br><button onclick="connectSerialPrinter()" class="btn btn-primary btn-sm mt-2">🔌 Hubungkan Ulang</button></div>');
            return false;
        }
    };

    // ── Auto-update badge saat DOM siap ───────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateBadge);
    } else {
        updateBadge();
    }

    // ── Cek apakah browser support ────────────────────────────────────────
    if (!navigator.serial) {
        console.warn('[serial-printer] Web Serial API tidak didukung oleh browser ini.');
    }

})();
