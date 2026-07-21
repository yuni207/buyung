/**
 * bt-printer.js — Sistem Bluetooth Thermal Printer
 * ──────────────────────────────────────────────────
 * Letakkan file ini di: public/js/bt-printer.js
 * Include di layout SEBELUM @yield('content'):
 *   <script src="{{ asset('js/bt-printer.js') }}"></script>
 *
 * Fitur:
 *  - Pair sekali, auto-reconnect di semua halaman berikutnya
 *  - Status badge live (⚫ Belum / 🟡 Menghubungkan / 🟢 Terhubung)
 *  - Mendukung printer ESC/POS thermal 58mm / 80mm
 *  - Format struk: header toko, item, subtotal, total, bayar, kembalian
 */

(function () {
    'use strict';

    /* ══════════════════════════════════════════════
       KONFIGURASI
    ══════════════════════════════════════════════ */
    var STORE_KEY        = 'bt_printer_device_name';   // localStorage key nama device
    var CHAR_WIDTH_58    = 32;                         // karakter per baris 58mm
    var CHAR_WIDTH_80    = 48;                         // karakter per baris 80mm
    var PRINTER_WIDTH    = CHAR_WIDTH_58;              // ubah ke CHAR_WIDTH_80 jika perlu

    /* ── Service & Characteristic UUID ESC/POS ── */
    var ESC_POS_SERVICE  = '000018f0-0000-1000-8000-00805f9b34fb';
    var ESC_POS_CHAR     = '00002af1-0000-1000-8000-00805f9b34fb';

    /* ══════════════════════════════════════════════
       STATE
    ══════════════════════════════════════════════ */
    var _device    = null;   // BluetoothDevice
    var _char      = null;   // BluetoothRemoteGATTCharacteristic
    var _connected = false;

    /* ══════════════════════════════════════════════
       BADGE HELPER
    ══════════════════════════════════════════════ */
    function updateBadge(state) {
        // state: 'disconnected' | 'connecting' | 'connected'
        var badge      = document.getElementById('printer-status-badge');
        var btnPair    = document.getElementById('btn-pair-printer');
        var btnUnpair  = document.getElementById('btn-unpair-printer');

        if (!badge) return;

        if (state === 'connected') {
            var name = (_device && _device.name) ? _device.name : localStorage.getItem(STORE_KEY) || 'Printer';
            badge.className   = 'badge badge-success mr-2';
            badge.style.cssText = 'font-size:13px;padding:6px 12px;';
            badge.textContent = '🟢 ' + name;
            if (btnPair)   btnPair.style.display   = 'none';
            if (btnUnpair) btnUnpair.style.display = '';
        } else if (state === 'connecting') {
            badge.className   = 'badge badge-warning mr-2';
            badge.style.cssText = 'font-size:13px;padding:6px 12px;';
            badge.textContent = '🟡 Menghubungkan...';
            if (btnPair)   btnPair.style.display   = 'none';
            if (btnUnpair) btnUnpair.style.display = 'none';
        } else {
            badge.className   = 'badge badge-secondary mr-2';
            badge.style.cssText = 'font-size:13px;padding:6px 12px;';
            badge.textContent = '⚫ Belum Terhubung';
            if (btnPair)   btnPair.style.display   = '';
            if (btnUnpair) btnUnpair.style.display = 'none';
        }
    }

    /* ══════════════════════════════════════════════
       KONEKSI GATT
    ══════════════════════════════════════════════ */
    async function connectGatt(device) {
        updateBadge('connecting');
        try {
            var server  = await device.gatt.connect();
            var service = await server.getPrimaryService(ESC_POS_SERVICE);
            _char       = await service.getCharacteristic(ESC_POS_CHAR);
            _device     = device;
            _connected  = true;

            /* Simpan nama device agar bisa ditampilkan di halaman lain */
            if (device.name) localStorage.setItem(STORE_KEY, device.name);

            /* Handle disconnect otomatis dari sisi printer */
            device.addEventListener('gattserverdisconnected', function () {
                _connected = false;
                _char      = null;
                updateBadge('disconnected');
                console.info('[BT-Printer] Printer terputus. Auto-reconnect akan dicoba saat cetak berikutnya.');
            });

            updateBadge('connected');
            return true;
        } catch (err) {
            _connected = false;
            _char      = null;
            updateBadge('disconnected');
            console.error('[BT-Printer] connectGatt error:', err);
            return false;
        }
    }

    /* ══════════════════════════════════════════════
       AUTO-RECONNECT (pakai requestDevice dg filter nama)
       Dicoba saat halaman load jika ada nama tersimpan
    ══════════════════════════════════════════════ */
    async function autoReconnect() {
        var savedName = localStorage.getItem(STORE_KEY);
        if (!savedName) return;   // belum pernah pair

        if (!navigator.bluetooth) return;

        /* Tampilkan "menghubungkan" segera agar UX responsif */
        updateBadge('connecting');

        try {
            /* getDevices() tersedia di Chrome ≥ 85 — tidak memerlukan gesture pengguna */
            if (navigator.bluetooth.getDevices) {
                var devices = await navigator.bluetooth.getDevices();
                var found   = devices.find(function (d) { return d.name === savedName; });
                if (found) {
                    var ok = await connectGatt(found);
                    if (ok) return;
                }
            }
            /* Fallback: tampilkan badge nama tapi tandai disconnected */
            var badge = document.getElementById('printer-status-badge');
            if (badge) {
                badge.className   = 'badge badge-secondary mr-2';
                badge.style.cssText = 'font-size:13px;padding:6px 12px;';
                badge.textContent = '⚫ ' + savedName + ' (Tap hubungkan)';
                var btnPair = document.getElementById('btn-pair-printer');
                if (btnPair) btnPair.style.display = '';
                var btnUnpair = document.getElementById('btn-unpair-printer');
                if (btnUnpair) btnUnpair.style.display = 'none';
            }
        } catch (e) {
            updateBadge('disconnected');
            console.warn('[BT-Printer] autoReconnect failed:', e);
        }
    }

    /* ══════════════════════════════════════════════
       PAIR MANUAL (perlu gesture pengguna)
    ══════════════════════════════════════════════ */
    window.pairPrinter = async function () {
        if (!navigator.bluetooth) {
            alert('Browser tidak mendukung Web Bluetooth.\nGunakan Google Chrome atau Microsoft Edge.');
            return false;
        }
        try {
            updateBadge('connecting');
            var device = await navigator.bluetooth.requestDevice({
                filters  : [{ services: [ESC_POS_SERVICE] }],
                optionalServices: [ESC_POS_SERVICE],
            });
            return await connectGatt(device);
        } catch (err) {
            updateBadge('disconnected');
            if (err.name !== 'NotFoundError') {   // pengguna cancel = NotFoundError, abaikan
                console.error('[BT-Printer] pairPrinter error:', err);
            }
            return false;
        }
    };

    /* ══════════════════════════════════════════════
       PUTUSKAN KONEKSI
    ══════════════════════════════════════════════ */
    window.unpairPrinter = function () {
        if (_device && _device.gatt.connected) {
            _device.gatt.disconnect();
        }
        _device    = null;
        _char      = null;
        _connected = false;
        localStorage.removeItem(STORE_KEY);
        updateBadge('disconnected');
    };

    /* ══════════════════════════════════════════════
       ENSURE CONNECTED
       Pastikan koneksi ada; jika tidak, reconnect otomatis
       Return false jika belum pernah pair sama sekali
    ══════════════════════════════════════════════ */
    async function ensureConnected() {
        /* Sudah terhubung */
        if (_connected && _char && _device && _device.gatt.connected) return true;

        /* Ada device tapi koneksi GATT terputus → reconnect */
        if (_device && !_device.gatt.connected) {
            return await connectGatt(_device);
        }

        /* Coba lewat getDevices() */
        var savedName = localStorage.getItem(STORE_KEY);
        if (!savedName) return false;   // belum pernah pair

        if (navigator.bluetooth.getDevices) {
            try {
                var devices = await navigator.bluetooth.getDevices();
                var found   = devices.find(function (d) { return d.name === savedName; });
                if (found) return await connectGatt(found);
            } catch (e) {
                console.warn('[BT-Printer] ensureConnected getDevices error:', e);
            }
        }

        return false;
    }

    /* ══════════════════════════════════════════════
       ESC/POS BUILDER
    ══════════════════════════════════════════════ */
    var ESC = 0x1B, GS = 0x1D;

    function cmd() {
        var bytes = [];
        for (var i = 0; i < arguments.length; i++) bytes.push(arguments[i]);
        return bytes;
    }

    var ESC_INIT       = cmd(ESC, 0x40);
    var ESC_ALIGN_L    = cmd(ESC, 0x61, 0x00);
    var ESC_ALIGN_C    = cmd(ESC, 0x61, 0x01);
    var ESC_ALIGN_R    = cmd(ESC, 0x61, 0x02);
    var ESC_BOLD_ON    = cmd(ESC, 0x45, 0x01);
    var ESC_BOLD_OFF   = cmd(ESC, 0x45, 0x00);
    var ESC_DOUBLE_ON  = cmd(GS,  0x21, 0x11);
    var ESC_DOUBLE_OFF = cmd(GS,  0x21, 0x00);
    var ESC_CUT        = cmd(GS,  0x56, 0x42, 0x00);
    var LF             = [0x0A];

    function strToBytes(str) {
        var enc   = new TextEncoder();
        return Array.from(enc.encode(str));
    }

    function line(text) { return strToBytes(text + '\n'); }

    function divider() {
        return line('-'.repeat(PRINTER_WIDTH));
    }

    /**
     * Buat baris dua kolom: kiri dan kanan rata
     * Contoh: "Nasi Goreng          Rp 15.000"
     */
    function twoCol(left, right) {
        var maxLeft = PRINTER_WIDTH - right.length - 1;
        if (left.length > maxLeft) left = left.substring(0, maxLeft - 1) + '~';
        var spaces = PRINTER_WIDTH - left.length - right.length;
        if (spaces < 1) spaces = 1;
        return line(left + ' '.repeat(spaces) + right);
    }

    function rp(n) {
        return 'Rp' + parseInt(n).toLocaleString('id-ID');
    }

    /**
     * Build buffer ESC/POS dari data transaksi
     * @param {Object} d
     *   { kode, tanggal, kasir, metode, subtotal, potongan, total, bayar, kembalian,
     *     items: [{nama, qty, harga, subtotal}] }
     */
    function buildEscPos(d) {
        var buf = [];

        function push(arr) { for (var i = 0; i < arr.length; i++) buf.push(arr[i]); }

        push(ESC_INIT);

        /* ── Header Toko ── */
        push(ESC_ALIGN_C);
        push(ESC_DOUBLE_ON);
        push(ESC_BOLD_ON);
        push(line('Toko Buyung'));
        push(ESC_DOUBLE_OFF);
        push(ESC_BOLD_OFF);
        push(line(d.tanggal));
        push(line('Kode: ' + d.kode));
        push(divider());

        /* ── Item ── */
        push(ESC_ALIGN_L);
        d.items.forEach(function (item) {
            /* Nama barang di baris pertama */
            push(line(item.nama));
            /* Qty x Harga = Subtotal */
            var detail = '  ' + item.qty + ' x ' + rp(item.harga);
            push(twoCol(detail, rp(item.subtotal)));
        });

        push(divider());

        /* ── Ringkasan ── */
        push(twoCol('Subtotal', rp(d.subtotal)));
        if (d.potongan > 0) {
            push(twoCol('Potongan', '-' + rp(d.potongan)));
        }

        push(ESC_BOLD_ON);
        push(twoCol('TOTAL', rp(d.total)));
        push(ESC_BOLD_OFF);
        push(twoCol('Bayar', rp(d.bayar)));

        push(ESC_BOLD_ON);
        push(twoCol('Kembalian', rp(d.kembalian)));
        push(ESC_BOLD_OFF);
        push(divider());

        /* ── Footer ── */
        push(ESC_ALIGN_C);
        push(line('Metode: ' + (d.metode || '-')));
        push(line('Kasir : ' + (d.kasir  || '-')));
        push(LF);
        push(line('Terima kasih telah berbelanja!'));
        push(LF);
        push(LF);
        push(LF);

        /* ── Cut ── */
        push(ESC_CUT);

        return new Uint8Array(buf);
    }

    /* ══════════════════════════════════════════════
       KIRIM KE PRINTER
       Tulis buffer dalam chunk 512 byte
    ══════════════════════════════════════════════ */
    async function writeChunks(data) {
        var CHUNK = 512;
        for (var offset = 0; offset < data.length; offset += CHUNK) {
            var chunk = data.slice(offset, offset + CHUNK);
            await _char.writeValue(chunk);
            /* Jeda kecil agar printer tidak tersedak */
            await new Promise(function (r) { setTimeout(r, 50); });
        }
    }

    /**
     * kirimKeprinter(data, statusFn)
     *  - data     : objek transaksi (lihat buildEscPos)
     *  - statusFn : function(html) untuk menampilkan pesan di halaman
     *  Return: true jika berhasil cetak, false jika printer belum pair
     */
    window.kirimKeprinter = async function (data, statusFn) {
        statusFn = statusFn || function () {};

        var connected = await ensureConnected();
        if (!connected) return false;   // caller akan minta pair manual

        statusFn('<div class="alert alert-info mt-2">🖨️ Mengirim data ke printer...</div>');

        try {
            var buffer = buildEscPos(data);
            await writeChunks(buffer);
            updateBadge('connected');
            statusFn('<div class="alert alert-success mt-2">✅ Berhasil dicetak!</div>');
            return true;
        } catch (err) {
            _connected = false;
            updateBadge('disconnected');
            console.error('[BT-Printer] kirimKeprinter error:', err);

            /* Coba reconnect sekali lagi */
            if (_device) {
                statusFn('<div class="alert alert-warning mt-2">🔄 Koneksi terputus, mencoba ulang...</div>');
                var retry = await connectGatt(_device);
                if (retry) {
                    try {
                        var buffer2 = buildEscPos(data);
                        await writeChunks(buffer2);
                        statusFn('<div class="alert alert-success mt-2">✅ Berhasil dicetak (reconnect)!</div>');
                        return true;
                    } catch (e2) {
                        console.error('[BT-Printer] retry print error:', e2);
                    }
                }
            }

            statusFn(
                '<div class="alert alert-danger mt-2">' +
                '❌ Gagal mencetak: ' + err.message + '<br>' +
                '<button onclick="pairPrinter().then(function(ok){ if(ok) window.kirimKeprinter(window._lastTransaksiData, function(h){document.getElementById(\'bt-status\') && (document.getElementById(\'bt-status\').innerHTML=h);}); })" ' +
                'class="btn btn-primary btn-sm mt-2">🔄 Hubungkan Ulang & Cetak</button>' +
                '</div>'
            );
            return true;   // return true agar caller tidak minta pair lagi (sudah pernah pair)
        }
    };

    /* ══════════════════════════════════════════════
       AUTO-RECONNECT SAAT DOM SIAP
    ══════════════════════════════════════════════ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoReconnect);
    } else {
        autoReconnect();
    }

})();
