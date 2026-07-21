@extends('admin.layouts.app', [
'activePage' => 'transaksi',
])
@section('content')
<div class="min-height-200px">

    

    <form action="/admin/transaksi/create" method="POST" id="form-transaksi">
        {{ csrf_field() }}
        <input type="hidden" name="is_hutang" id="inp-is-hutang" value="0">
        <input type="hidden" name="is_print"  id="inp-is-print"  value="0">

        <div class="row">
            {{-- ══ KIRI: Pencarian & Keranjang ══ --}}
            <div class="col-lg-7">
                <div class="pd-20 card-box mb-30">
                    <h2 class="text-primary h2 mb-3"><i class="icon-copy dw dw-add-file-1"></i> Tambah Item</h2>
                    <hr style="margin-top:0">

                    <div class="form-group" style="position:relative;">
                        <label>Cari Barang <small class="text-muted">(Nama atau Barcode)</small></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" id="search-barang" class="form-control"
                                   placeholder="Ketik nama barang atau scan barcode..." autocomplete="off">
                        </div>
                        <div id="search-result" class="list-group shadow-sm"
                             style="position:absolute;z-index:1000;width:100%;display:none;max-height:260px;overflow-y:auto;top:100%;left:0;border-radius:0 0 6px 6px;"></div>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-sm" id="tbl-keranjang">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Barang</th>
                                    <th width="100" class="text-center">Qty</th>
                                    <th width="130" class="text-right">Harga</th>
                                    <th width="120" class="text-right">Subtotal</th>
                                    <th width="40" class="text-center">×</th>
                                </tr>
                            </thead>
                            <tbody id="keranjang-body">
                                <tr id="tr-empty">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fa fa-shopping-cart fa-2x mb-2 d-block"></i>
                                        Keranjang masih kosong
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Total</td>
                                    <td class="text-right font-weight-bold text-primary" id="lbl-subtotal">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══ KANAN: Pembayaran ══ --}}
            <div class="col-lg-5">
                <div class="pd-20 card-box mb-30">
                    <h2 class="text-primary h2 mb-3"><i class="icon-copy dw dw-money-2"></i> Pembayaran</h2>
                    <hr style="margin-top:0">

                    {{-- ── Toggle Hutang ── --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch" style="padding-left:2.5rem;">
                            <input type="checkbox" class="custom-control-input" id="toggle-hutang">
                            <label class="custom-control-label font-weight-bold" for="toggle-hutang"
                                   style="font-size:15px; cursor:pointer;">
                                💳 Catat sebagai Hutang
                            </label>
                        </div>
                        <small class="text-muted ml-4">Aktifkan jika pelanggan belum/kurang bayar</small>
                    </div>

                    {{-- ── Toggle Print ── --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch" style="padding-left:2.5rem;">
                            <input type="checkbox" class="custom-control-input" id="toggle-print">
                            <label class="custom-control-label font-weight-bold" for="toggle-print"
                                   style="font-size:15px; cursor:pointer;">
                                🖨️ Cetak Struk Setelah Simpan
                            </label>
                        </div>
                        <small class="text-muted ml-4">Matikan jika tidak ingin mencetak struk</small>
                    </div>

                    {{-- ── Panel DATA HUTANG ── --}}
                    <div id="panel-hutang" style="display:none;">
                        <div class="alert alert-warning py-2 mb-3" style="font-size:13px;">
                            <i class="fa fa-exclamation-triangle"></i>
                            Transaksi ini akan dicatat sebagai <strong>hutang pelanggan</strong>.
                            Stock barang tetap dikurangi.
                        </div>
                        <div class="form-group">
                            <label>Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" id="inp-nama-pelanggan"
                                   class="form-control" placeholder="Nama pelanggan berhutang">
                        </div>
                        <div class="form-group">
                            <label>No. HP <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xx..." maxlength="12">
                        </div>
                        <div class="form-group">
                            <label>Jatuh Tempo <small class="text-muted">(opsional)</small></label>
                            <input type="date" name="jatuh_tempo" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Uang Muka / DP <small class="text-muted">(opsional, Rp)</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                <input type="text" id="inp-dp" class="form-control" placeholder="0" autocomplete="off">
                            </div>
                            <small class="text-muted">Kosongkan jika tidak ada DP</small>
                        </div>
                        <div class="form-group">
                            <label>Keterangan <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Misal: hutang sembako minggu ini">
                        </div>
                        <hr>
                    </div>

                    <table class="table table-sm table-borderless mb-0">
                        <tr class="border-top">
                            <td class="font-weight-bold" style="font-size:16px;">Total</td>
                            <td class="text-right font-weight-bold text-primary" style="font-size:16px;" id="sum-total">Rp 0</td>
                        </tr>
                    </table>
                    
                    <hr class="mt-0">
                    {{-- ── Panel PEMBAYARAN TUNAI ── --}}
                    <div id="panel-tunai">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select name="id_metode" id="sel-metode" class="form-control">
                                        @foreach($metodes as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Jumlah Bayar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                        <input type="text" id="inp-bayar" name="bayar" class="form-control"
                                               placeholder="0" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kembalian: hanya tampil saat tunai --}}
                    <div id="panel-bayar">
                        <div class="form-group mt-0">
                            <label>Kembalian</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                <input type="text" id="inp-kembalian" class="form-control font-weight-bold"
                                       readonly style="background:#f0fff4;color:#155724;">
                            </div>
                        </div>
                    </div>

                    {{-- Sisa hutang --}}
                    <div id="panel-sisa-hutang" style="display:none;" class="mt-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Dibayar (DP)</td>
                                <td class="text-right font-weight-bold text-success" id="sum-dp">Rp 0</td>
                            </tr>
                            <tr class="border-top">
                                <td class="font-weight-bold text-danger" style="font-size:15px;">Sisa Hutang</td>
                                <td class="text-right font-weight-bold text-danger" style="font-size:15px;" id="sum-sisa">Rp 0</td>
                            </tr>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <a href="/admin/transaksi" class="btn btn-secondary btn-block">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-bayar">
                                <i class="fa fa-check"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="hidden-items"></div>
    </form>
</div>

{{-- ══ MODAL PILIH HARGA ══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-pilih-harga" tabindex="-1" role="dialog" aria-labelledby="modalPilihHargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPilihHargaLabel">
                    <i class="fa fa-tag"></i> Pilih Harga
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted" id="modal-nama-barang" style="font-size:15px; font-weight:600;"></p>
                <div class="row">
                    {{-- Harga Normal --}}
                    <div class="col-6">
                        <div id="card-harga-normal"
                             class="card border-2 text-center py-3 px-2 harga-pilihan"
                             style="cursor:pointer; border-radius:10px; border:2px solid #dee2e6; transition: all .15s;">
                            <div class="mb-1" style="font-size:13px; color:#6c757d; font-weight:600;">
                                <i class="fa fa-tag"></i> Harga Normal
                            </div>
                            <div id="lbl-harga-normal" style="font-size:20px; font-weight:700; color:#343a40;"></div>
                        </div>
                    </div>
                    {{-- Harga Khusus --}}
                    <div class="col-6">
                        <div id="card-harga-khusus"
                             class="card border-2 text-center py-3 px-2 harga-pilihan"
                             style="cursor:pointer; border-radius:10px; border:2px solid #dee2e6; transition: all .15s;">
                            <div class="mb-1" style="font-size:13px; color:#e67e22; font-weight:600;">
                                <i class="fa fa-star"></i> Harga Khusus
                            </div>
                            <div id="lbl-harga-khusus" style="font-size:20px; font-weight:700; color:#e67e22;"></div>
                            <small class="text-success font-weight-bold" id="lbl-selisih"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-konfirmasi-harga">
                    <i class="fa fa-check"></i> Pilih & Tambah
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#panel-hutang { transition: all .2s; }
#toggle-hutang:checked ~ label { color: #e67e22; }
#toggle-print:checked  ~ label { color: #2980b9; }

.harga-pilihan:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
}
.harga-pilihan.selected-normal {
    border-color: #007bff !important;
    background: #e8f4fd;
    box-shadow: 0 0 0 3px rgba(0,123,255,.2);
}
.harga-pilihan.selected-khusus {
    border-color: #e67e22 !important;
    background: #fff8f0;
    box-shadow: 0 0 0 3px rgba(230,126,34,.2);
}
.badge-khusus {
    background: linear-gradient(135deg, #e67e22, #f39c12);
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    vertical-align: middle;
    margin-left: 4px;
}
</style>

<script>
(function () {

    // ══ STATE ══════════════════════════════════════════════
    var keranjang  = [];
    var modeHutang = false;
    var modePrint  = false;

    // Temporary state untuk modal pilih harga
    var modalBarang     = null;   // data barang yang sedang dipilih
    var modalPilihanHarga = 'normal'; // 'normal' | 'khusus'

    // ── UTILS ──────────────────────────────────────────────
    function rupiah(n) { return 'Rp ' + parseInt(n || 0).toLocaleString('id-ID'); }
    function formatInput(el) {
        var v = el.value.replace(/[^0-9]/g, '');
        el.value = v === '' ? '' : parseInt(v).toLocaleString('id-ID');
    }
    function getRawInt(el) { return parseInt((el ? el.value : '0').replace(/[^0-9]/g, '') || '0'); }

    // ══ TOGGLE HUTANG ══════════════════════════════════════
    document.getElementById('toggle-hutang').addEventListener('change', function () {
        modeHutang = this.checked;
        document.getElementById('inp-is-hutang').value = modeHutang ? '1' : '0';

        document.getElementById('panel-hutang').style.display      = modeHutang ? '' : 'none';
        document.getElementById('panel-tunai').style.display       = modeHutang ? 'none' : '';
        document.getElementById('panel-bayar').style.display       = modeHutang ? 'none' : '';
        document.getElementById('panel-sisa-hutang').style.display = modeHutang ? '' : 'none';

        var btn = document.getElementById('btn-bayar');
        btn.innerHTML = modeHutang
            ? '<i class="fa fa-save"></i> Simpan Hutang'
            : '<i class="fa fa-check"></i> Simpan Transaksi';
        btn.className = modeHutang
            ? 'btn btn-warning btn-block'
            : 'btn btn-primary btn-block';

        if (modeHutang) {
            document.getElementById('inp-bayar').value     = '';
            document.getElementById('inp-kembalian').value = '';
        }

        updateTotal();
    });

    // ══ TOGGLE PRINT ═══════════════════════════════════════
    document.getElementById('toggle-print').addEventListener('change', function () {
        modePrint = this.checked;
        document.getElementById('inp-is-print').value = modePrint ? '1' : '0';
    });

    // DP input listener
    document.getElementById('inp-dp').addEventListener('input', function () {
        formatInput(this);
        updateTotal();
    });

    // ══ RENDER KERANJANG ═══════════════════════════════════
    function renderKeranjang() {
        var tbody = document.getElementById('keranjang-body');
        tbody.innerHTML = '';

        if (keranjang.length === 0) {
            tbody.innerHTML = '<tr id="tr-empty"><td colspan="5" class="text-center text-muted py-4"><i class="fa fa-shopping-cart fa-2x mb-2 d-block"></i>Keranjang masih kosong</td></tr>';
        } else {
            keranjang.forEach(function (item, idx) {
                // Label harga yang ditampilkan di kolom "Harga"
                var hargaLabel = rupiah(item.harga);
                if (item.pakai_harga_khusus) {
                    hargaLabel += ' <span class="badge-khusus"><i class="fa fa-star"></i></span>';
                }

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + item.nama +
                        '<br><small class="text-muted">' + rupiah(item.harga_normal) + (item.satuan ? ' / ' + item.satuan : '') + '</small>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<div class="input-group input-group-sm">' +
                            '<div class="input-group-prepend"><button type="button" class="btn btn-outline-secondary btn-qty-minus" data-idx="' + idx + '">-</button></div>' +
                            '<input type="number" class="form-control text-center inp-qty" data-idx="' + idx + '" value="' + item.qty + '" min="1" max="' + item.stock + '" style="width:55px;">' +
                            '<div class="input-group-append"><button type="button" class="btn btn-outline-secondary btn-qty-plus" data-idx="' + idx + '">+</button></div>' +
                        '</div>' +
                        '<small class="text-muted">Stok: ' + item.stock + (item.satuan ? ' ' + item.satuan : '') + '</small>' +
                    '</td>' +
                    '<td class="text-right">' + hargaLabel + '</td>' +
                    '<td class="text-right font-weight-bold">' + rupiah(item.harga * item.qty) + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-hapus" data-idx="' + idx + '"><i class="fa fa-times"></i></button></td>';
                tbody.appendChild(tr);
            });
        }

        updateTotal();
        syncHidden();
    }

    // ══ HITUNG TOTAL ═══════════════════════════════════════
    function updateTotal() {
        var total = keranjang.reduce(function (s, i) { return s + (i.harga * i.qty); }, 0);

        document.getElementById('lbl-subtotal').textContent = rupiah(total);
        document.getElementById('sum-total').textContent    = rupiah(total);

        if (modeHutang) {
            var dp   = Math.min(getRawInt(document.getElementById('inp-dp')), total);
            var sisa = total - dp;
            document.getElementById('sum-dp').textContent   = rupiah(dp);
            document.getElementById('sum-sisa').textContent = rupiah(sisa);
            document.getElementById('inp-bayar').value      = dp > 0 ? dp : '';
        } else {
            var bayar     = getRawInt(document.getElementById('inp-bayar'));
            var kembalian = bayar - total;
            var elK       = document.getElementById('inp-kembalian');
            elK.value            = kembalian >= 0 ? parseInt(kembalian).toLocaleString('id-ID') : '(kurang)';
            elK.style.color      = kembalian >= 0 ? '#155724' : '#721c24';
            elK.style.background = kembalian >= 0 ? '#f0fff4' : '#fff5f5';
        }
    }

    // ══ SYNC HIDDEN ════════════════════════════════════════
    function syncHidden() {
        var cont = document.getElementById('hidden-items');
        cont.innerHTML = '';
        keranjang.forEach(function (item, idx) {
            cont.innerHTML +=
                '<input type="hidden" name="items[' + idx + '][barang_id]" value="' + item.detail_id + '">' +
                '<input type="hidden" name="items[' + idx + '][qty]" value="' + item.qty + '">' +
                '<input type="hidden" name="items[' + idx + '][pakai_harga_khusus]" value="' + (item.pakai_harga_khusus ? '1' : '0') + '">';
        });
    }

    // ══ KERANJANG EVENTS ═══════════════════════════════════
    /**
     * Tambah item ke keranjang.
     * pilihanHarga: 'normal' | 'khusus'
     */
    function tambahItem(barang, pilihanHarga) {
        pilihanHarga = pilihanHarga || 'normal';

        var hargaNormal  = parseInt(barang.harga)         || 0;
        var hargaKhusus  = parseInt(barang.harga_khusus)  || 0;
        var pakaiKhusus  = (pilihanHarga === 'khusus' && hargaKhusus > 0);
        var hargaPakai   = pakaiKhusus ? hargaKhusus : hargaNormal;

        var detailKey = barang.detail_id || barang.id;
        var existing  = keranjang.find(function (i) { return i.detail_id === detailKey; });

        if (existing) {
            // Jika sudah ada di keranjang, update harga jika pilihan berubah
            if (existing.qty < existing.stock) {
                existing.qty++;
            } else {
                alert('Stock "' + barang.nama_barang + '" tidak mencukupi!');
                return;
            }
            // Update pilihan harga jika berubah
            existing.harga              = hargaPakai;
            existing.pakai_harga_khusus = pakaiKhusus;
        } else {
            var namaLabel = barang.nama_barang + (barang.satuan ? ' (' + barang.satuan + ')' : '');
            keranjang.push({
                id                  : barang.id,
                detail_id           : detailKey,
                nama                : namaLabel,
                satuan              : barang.satuan || '',
                harga               : hargaPakai,
                harga_normal        : hargaNormal,
                harga_khusus_val    : hargaKhusus,
                pakai_harga_khusus  : pakaiKhusus,
                qty                 : 1,
                stock               : parseInt(barang.stock)
            });
        }
        renderKeranjang();
    }

    // ── Buka modal pilih harga ──────────────────────────────
    function bukaModalPilihHarga(barang) {
        modalBarang       = barang;
        modalPilihanHarga = 'normal'; // reset default

        var hargaNormal = parseInt(barang.harga)        || 0;
        var hargaKhusus = parseInt(barang.harga_khusus) || 0;

        document.getElementById('modal-nama-barang').textContent =
            '🛒 ' + barang.nama_barang + (barang.satuan ? ' (' + barang.satuan + ')' : '');

        document.getElementById('lbl-harga-normal').textContent = rupiah(hargaNormal);
        document.getElementById('lbl-harga-khusus').textContent = rupiah(hargaKhusus);

        var selisih = hargaNormal - hargaKhusus;
        document.getElementById('lbl-selisih').textContent =
            selisih > 0 ? 'Hemat ' + rupiah(selisih) : '';

        // Reset visual pilihan → default ke "normal"
        setVisualPilihan('normal');

        $('#modal-pilih-harga').modal('show');
    }

    function setVisualPilihan(pilihan) {
        modalPilihanHarga = pilihan;
        var cardNormal = document.getElementById('card-harga-normal');
        var cardKhusus = document.getElementById('card-harga-khusus');

        cardNormal.classList.remove('selected-normal', 'selected-khusus');
        cardKhusus.classList.remove('selected-normal', 'selected-khusus');

        if (pilihan === 'normal') {
            cardNormal.classList.add('selected-normal');
        } else {
            cardKhusus.classList.add('selected-khusus');
        }
    }

    // Klik kartu di modal
    document.getElementById('card-harga-normal').addEventListener('click', function () {
        setVisualPilihan('normal');
    });
    document.getElementById('card-harga-khusus').addEventListener('click', function () {
        setVisualPilihan('khusus');
    });

    // Tombol "Pilih & Tambah" di modal
    document.getElementById('btn-konfirmasi-harga').addEventListener('click', function () {
        if (!modalBarang) return;
        $('#modal-pilih-harga').modal('hide');
        tambahItem(modalBarang, modalPilihanHarga);
    });

    // ── Event keranjang (qty, hapus) ───────────────────────
    document.getElementById('keranjang-body').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var idx = parseInt(btn.getAttribute('data-idx'));
        if (isNaN(idx)) return;
        if (btn.classList.contains('btn-qty-minus')) {
            if (keranjang[idx].qty > 1) keranjang[idx].qty--;
            else keranjang.splice(idx, 1);
            renderKeranjang();
        }
        if (btn.classList.contains('btn-qty-plus')) {
            if (keranjang[idx].qty < keranjang[idx].stock) keranjang[idx].qty++;
            else alert('Melebihi stock tersedia!');
            renderKeranjang();
        }
        if (btn.classList.contains('btn-hapus')) {
            keranjang.splice(idx, 1);
            renderKeranjang();
        }
    });

    document.getElementById('keranjang-body').addEventListener('change', function (e) {
        if (e.target.classList.contains('inp-qty')) {
            var idx = parseInt(e.target.getAttribute('data-idx'));
            var val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) val = 1;
            if (val > keranjang[idx].stock) { val = keranjang[idx].stock; alert('Melebihi stock! Qty dikembalikan ke ' + val); }
            keranjang[idx].qty = val;
            renderKeranjang();
        }
    });

    document.getElementById('inp-bayar').addEventListener('input', function () { formatInput(this); updateTotal(); });

    // ══ SEARCH BARANG ══════════════════════════════════════
    var searchTimeout;
    var searchEl = document.getElementById('search-barang');
    var resultEl = document.getElementById('search-result');

    function tampilkanHasil(data) {
        resultEl.innerHTML = '';
        if (data.length === 0) {
            resultEl.innerHTML = '<a class="list-group-item list-group-item-action disabled text-muted"><i class="fa fa-exclamation-circle"></i> Barang tidak ditemukan</a>';
            resultEl.style.display = 'block';
            return;
        }
        data.forEach(function (b) {
            var hargaKhususAda = b.harga_khusus && parseInt(b.harga_khusus) > 0;

            var a       = document.createElement('a');
            a.href      = '#';
            a.className = 'list-group-item list-group-item-action';
            a.innerHTML =
                '<strong>' + b.nama_barang + '</strong>' +
                (b.barcode ? ' <span class="badge badge-secondary">' + b.barcode + '</span>' : '') +
                // Tampilkan harga normal
                '<span class="float-right text-primary font-weight-bold">Rp ' + parseInt(b.harga).toLocaleString('id-ID') + '</span>' +
                '<br><small class="text-muted"><i class="fa fa-archive"></i> Stok: ' + b.stock + (b.satuan ? ' ' + b.satuan : '') + '</small>' +
                // Tampilkan badge harga khusus jika ada
                (hargaKhususAda
                    ? '<br><small style="color:#e67e22;font-weight:600;"><i class="fa fa-star"></i> Harga Khusus: Rp ' + parseInt(b.harga_khusus).toLocaleString('id-ID') + '</small>'
                    : '');

            a.addEventListener('click', function (e) {
                e.preventDefault();
                searchEl.value = '';
                resultEl.style.display = 'none';

                // Jika ada harga khusus → tampilkan modal pilihan
                if (hargaKhususAda) {
                    bukaModalPilihHarga(b);
                } else {
                    tambahItem(b, 'normal');
                }

                searchEl.focus();
            });
            resultEl.appendChild(a);
        });
        resultEl.style.display = 'block';
    }

    function fetchBarang(q, callback) {
        fetch('/admin/transaksi/search-barang?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(callback)
        .catch(function () {
            resultEl.innerHTML = '<a class="list-group-item disabled text-danger">Gagal memuat data</a>';
            resultEl.style.display = 'block';
        });
    }

    searchEl.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        var q = this.value.trim();
        if (!q) return;

        var firstItem = resultEl.querySelector('a:not(.disabled)');
        if (firstItem && resultEl.style.display !== 'none') {
            firstItem.click();
            return;
        }

        clearTimeout(searchTimeout);
        fetchBarang(q, function (data) {
            if (data.length === 1) {
                searchEl.value = '';
                resultEl.style.display = 'none';
                var b = data[0];
                var hargaKhususAda = b.harga_khusus && parseInt(b.harga_khusus) > 0;
                if (hargaKhususAda) {
                    bukaModalPilihHarga(b);
                } else {
                    tambahItem(b, 'normal');
                }
                searchEl.focus();
            } else if (data.length > 1) {
                tampilkanHasil(data);
            } else {
                tampilkanHasil([]);
            }
        });
    });

    searchEl.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        var q = this.value.trim();
        if (q.length < 1) { resultEl.style.display = 'none'; return; }

        searchTimeout = setTimeout(function () {
            fetchBarang(q, function (data) { tampilkanHasil(data); });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!resultEl.contains(e.target) && e.target !== searchEl) {
            resultEl.style.display = 'none';
        }
    });

    // ══ SUBMIT FORM (AJAX) ═════════════════════════════════
    document.getElementById('form-transaksi').addEventListener('submit', function (e) {
        e.preventDefault();

        if (keranjang.length === 0) { alert('Keranjang masih kosong!'); return; }

        if (modeHutang) {
            var namaPelanggan = document.getElementById('inp-nama-pelanggan').value.trim();
            if (!namaPelanggan) { alert('Isi nama pelanggan!'); document.getElementById('inp-nama-pelanggan').focus(); return; }
        } else {
            var bayarEl  = document.getElementById('inp-bayar');
            var bayarRaw = bayarEl.value.replace(/[^0-9]/g, '');
            if (!bayarRaw || parseInt(bayarRaw) === 0) { alert('Isi jumlah bayar terlebih dahulu!'); bayarEl.focus(); return; }

            var metodeEl = document.getElementById('sel-metode');
            if (!metodeEl.value) { alert('Pilih metode pembayaran!'); metodeEl.focus(); return; }

            if (document.getElementById('inp-kembalian').value === '(kurang)') {
                alert('Jumlah bayar kurang dari total transaksi!');
                bayarEl.focus();
                return;
            }
        }

        var formData = new FormData(this);

        if (modeHutang) {
            var dp = getRawInt(document.getElementById('inp-dp'));
            formData.set('bayar',    dp);
            formData.set('potongan', 0);
        } else {
            var bayarRaw2 = document.getElementById('inp-bayar').value.replace(/[^0-9]/g, '');
            formData.set('bayar',    bayarRaw2);
            formData.set('potongan', 0);
        }

        var btnBayar = document.getElementById('btn-bayar');
        btnBayar.disabled  = true;
        btnBayar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

        fetch('/admin/transaksi/create', {
            method : 'POST',
            body   : formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            var ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) return res.json();
            if (!res.ok) throw new Error('Server error: ' + res.status);
            return { success: true };
        })
        .then(function (result) {
            btnBayar.disabled  = false;
            btnBayar.innerHTML = modeHutang
                ? '<i class="fa fa-save"></i> Simpan Hutang'
                : '<i class="fa fa-check"></i> Simpan Transaksi';

            if (!result || result.success === false) {
                alert('Error: ' + (result.message || 'Terjadi kesalahan'));
                return;
            }

            if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                window.location.href = '/admin/transaksi/add';
            }
        })
        .catch(function (err) {
            btnBayar.disabled  = false;
            btnBayar.innerHTML = modeHutang
                ? '<i class="fa fa-save"></i> Simpan Hutang'
                : '<i class="fa fa-check"></i> Simpan Transaksi';
            alert('Terjadi kesalahan jaringan: ' + err.message);
        });
    });

    // ══ INIT ═══════════════════════════════════════════════
    renderKeranjang();
    document.getElementById('search-barang').focus();

})();
</script>
@endsection