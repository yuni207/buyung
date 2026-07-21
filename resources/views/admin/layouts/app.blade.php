<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8" />
    <title>Toko Buyung</title>

    <!-- Site favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{url('assets-admin')}}/vendors/images/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{url('assets-admin')}}/vendors/images/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{url('assets-admin')}}/vendors/images/favicon-16x16.png" />

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{url('assets-admin')}}/vendors/styles/core.css" />
    <link rel="stylesheet" type="text/css" href="{{url('assets-admin')}}/vendors/styles/icon-font.min.css" />
    <link rel="stylesheet" type="text/css" href="{{url('assets-admin')}}/src/plugins/datatables/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" type="text/css" href="{{url('assets-admin')}}/src/plugins/datatables/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" type="text/css" href="{{url('assets-admin')}}/vendors/styles/style.css" />

    <style type="text/css">
        .table td {
            font-size: 14px;
            font-weight: 500;
            padding: 0.5rem;
        }

        .btn-group-xs>.btn,
        .btn-xs {
            padding: 1px 7px;
            font-size: 12px;
            line-height: 1.9;
            border-radius: 3px;
        }

        .blink {
            animation: blinker 3s linear infinite;
        }

        @keyframes blinker {
            50% { opacity: 0; }
        }

        /* Hapus spinner di input number */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        .select2-container {
            width: 100% !important;
        }

        .input-error {
            border-color: red;
            background-color: #f8d7da;
        }

        /* Badge status printer global */
        #printer-status-badge.connected    { background-color: #28a745 !important; color: #fff; }
        #printer-status-badge.disconnected { background-color: #6c757d !important; color: #fff; }
        #printer-status-badge.connecting   { background-color: #ffc107 !important; color: #212529; }

        /* Badge hutang & piutang di sidebar */
        .hutang-badge {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 6px;
            margin-left: 4px;
            line-height: 1.6;
            vertical-align: middle;
        }
        .piutang-badge {
            display: inline-block;
            background: #f39c12;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 6px;
            margin-left: 4px;
            line-height: 1.6;
            vertical-align: middle;
        }
        /* Badge kasir aktif di sidebar */
        .kasir-badge {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 6px;
            margin-left: 4px;
            line-height: 1.6;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    @if ($activePage == 'dashboard')
    <div class="pre-loader">
        <div class="pre-loader-box">
            <div class="loader-logo">
                <img src="{{url('assets-admin')}}/vendors/images/buyung.svg" alt="" />
            </div>
            <div class="loader-progress" id="progress_div">
                <div class="bar" id="bar1"></div>
            </div>
            <div class="percent" id="percent1">0%</div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>
    @endif

    <div class="header">
        <div class="header-left">
            <div class="menu-icon bi bi-list"></div>
        </div>
        <div class="header-right">
            <div class="dashboard-setting user-notification">
                <div class="dropdown">
                    <a class="dropdown-toggle no-arrow" href="javascript:;" data-toggle="right-sidebar">
                        <i class="dw dw-settings2"></i>
                    </a>
                </div>
            </div>

            {{-- Notifikasi hutang jatuh tempo di header --}}
            @if(in_array(Auth::user()->level, ['1', '2']))
            @php
                $hutangJatuhTempo = \Illuminate\Support\Facades\DB::table('hutang')
                    ->where('status', 'belum')
                    ->where('jatuh_tempo', '<', date('Y-m-d'))
                    ->when(Auth::user()->level != '1', fn($q) => $q->where('id_user', Auth::id()))
                    ->count();
            @endphp
            @if($hutangJatuhTempo > 0)
            <div class="user-notification">
                <div class="dropdown">
                    <a class="dropdown-toggle no-arrow" href="/admin/hutang?status=belum">
                        <i class="dw dw-wallet-1" style="color:#f39c12; position:relative;"></i>
                        <span class="badge badge-danger"
                              style="position:absolute; top:12px; font-size:9px; padding:2px 4px; border-radius:8px;">
                            {{ $hutangJatuhTempo }}
                        </span>
                    </a>
                </div>
            </div>
            @endif
            @endif

            {{-- Notifikasi piutang jatuh tempo di header --}}
            @if(in_array(Auth::user()->level, ['1', '2']))
            @php
                $piutangJatuhTempo = \Illuminate\Support\Facades\DB::table('piutang')
                    ->where('status', 'belum')
                    ->where('jatuh_tempo', '<', date('Y-m-d'))
                    ->when(Auth::user()->level != '1', fn($q) => $q->where('id_user', Auth::id()))
                    ->count();
            @endphp
            @if($piutangJatuhTempo > 0)
            <div class="user-notification">
                <div class="dropdown">
                    <a class="dropdown-toggle no-arrow" href="/admin/piutang?status=belum"
                       title="Piutang Jatuh Tempo">
                        <i class="dw dw-money-2" style="color:#f39c12; position:relative;"></i>
                        <span class="badge badge-warning"
                              style="position:absolute; top:12px; font-size:9px; padding:2px 4px; border-radius:8px; color:#212529;">
                            {{ $piutangJatuhTempo }}
                        </span>
                    </a>
                </div>
            </div>
            @endif
            @endif

            {{-- Notifikasi kasir aktif di header --}}
            @if(in_array(Auth::user()->level, ['1', '2']))
            @php
                $kasirSesiAktif = \Illuminate\Support\Facades\DB::table('kasir_session')
                    ->where('id_user', Auth::id())
                    ->where('status', 'buka')
                    ->first();
            @endphp
            @if($kasirSesiAktif)
            <div class="user-notification">
                <div class="dropdown">
                    <a class="dropdown-toggle no-arrow" href="/admin/kasir"
                       title="Kasir terbuka sejak {{ \Carbon\Carbon::parse($kasirSesiAktif->waktu_buka)->format('H:i') }}">
                        <i class="dw dw-shop" style="color:#27ae60; position:relative;"></i>
                        <span class="badge badge-success"
                              style="position:absolute; top:12px; font-size:9px; padding:2px 4px; border-radius:8px;">
                            ●
                        </span>
                    </a>
                </div>
            </div>
            @endif
            @endif

            <div class="user-info-dropdown">
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <span class="user-icon">
                            <img src="{{url('assets-admin')}}/vendors/images/user.png" alt="" />
                        </span>
                        <span class="user-name">{{Auth::User()->name}}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                        <a class="dropdown-item" href="/admin/change"><i class="dw dw-password"></i> Ganti Password</a>
                        <a class="dropdown-item" href="#"><i class="dw dw-book"></i> Manual Book</a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();"><i class="dw dw-logout"></i> Log Out</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-sidebar">
        <div class="sidebar-title">
            <h3 class="weight-600 font-16 text-primary">
                Layout Settings
                <span class="btn-block font-weight-400 font-12">User Interface Settings</span>
            </h3>
            <div class="close-sidebar" data-toggle="right-sidebar-close">
                <i class="icon-copy ion-close-round"></i>
            </div>
        </div>
        <div class="right-sidebar-body customscroll">
            <div class="right-sidebar-body-content">
                <h4 class="weight-600 font-18 pb-10">Header Background</h4>
                <div class="sidebar-btn-group pb-30 mb-10">
                    <a href="javascript:void(0);" class="btn btn-outline-primary header-white active">White</a>
                    <a href="javascript:void(0);" class="btn btn-outline-primary header-dark">Dark</a>
                </div>

                <h4 class="weight-600 font-18 pb-10">Sidebar Background</h4>
                <div class="sidebar-btn-group pb-30 mb-10">
                    <a href="javascript:void(0);" class="btn btn-outline-primary sidebar-light">White</a>
                    <a href="javascript:void(0);" class="btn btn-outline-primary sidebar-dark active">Dark</a>
                </div>

                <div class="reset-options pt-30 text-center">
                    <button class="btn btn-primary" id="reset-settings">
                        Reset Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="left-side-bar">
        <div class="brand-logo">
            <a href="/">
                <img src="{{url('assets-admin')}}/vendors/images/buyung.svg" alt="" class="dark-logo" />
                <img src="{{url('assets-admin')}}/vendors/images/buyung-white.svg" alt="" class="light-logo" />
            </a>
            <div class="close-sidebar" data-toggle="left-sidebar-close">
                <i class="ion-close-round"></i>
            </div>
        </div>
        <div class="menu-block customscroll">
            <div class="sidebar-menu">
                <ul id="accordion-menu">
                    <li>
                        <a href="/admin/home" class="dropdown-toggle no-arrow @if ($activePage == 'dashboard') active @endif">
                            <span class="micon bi bi-house"></span><span class="mtext">Dashboard</span>
                        </a>
                    </li>

                    @if(Auth::user()->level == '1')
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon dw dw-file"></span><span class="mtext">Data Master</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="/admin/satuan" class="@if ($activePage == 'satuan') active @endif">Data Satuan Barang</a></li>
                            <li><a href="/admin/metode" class="@if ($activePage == 'metode') active @endif">Metode Pembayaran</a></li>
                        </ul>
                    </li>
                    @elseif(Auth::user()->level == '2')
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon dw dw-file"></span><span class="mtext">Data Master</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="/admin/metode" class="@if ($activePage == 'metode') active @endif">Metode Pembayaran</a></li>
                        </ul>
                    </li>
                    @else
                    <li>
                        <a href="/admin/satuan" class="dropdown-toggle no-arrow @if ($activePage == 'satuan') active @endif">
                            <span class="micon dw dw-file"></span><span class="mtext">Data Satuan</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::user()->level == '1')
                    <li>
                        <a href="/admin/account" class="dropdown-toggle no-arrow @if ($activePage == 'account') active @endif">
                            <span class="micon dw dw-user1"></span><span class="mtext">Data Account</span>
                        </a>
                    </li>
                    @endif

                    @if(in_array(Auth::user()->level, ['1', '3']))
                    <li>
                        <a href="/admin/barang" class="dropdown-toggle no-arrow @if ($activePage == 'barang') active @endif">
                            <span class="micon dw dw-notepad-2"></span><span class="mtext">Data Barang</span>
                        </a>
                    </li>
                    @endif

                    @if(in_array(Auth::user()->level, ['1', '3']))
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon dw dw-notebook"></span><span class="mtext">Data Stock</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="/admin/barang_masuk" class="@if ($activePage == 'barang_masuk') active @endif">Data Barang Masuk</a></li>
                            <li><a href="/admin/barang_keluar" class="@if ($activePage == 'barang_keluar') active @endif">Data Barang Keluar</a></li>
                        </ul>
                    </li>
                    @endif

                    @if(in_array(Auth::user()->level, ['1', '2']))
                    <li>
                        <a href="/admin/pengeluaran" class="dropdown-toggle no-arrow @if ($activePage == 'pengeluaran') active @endif">
                            <span class="micon dw dw-wallet-1"></span><span class="mtext">Data Pengeluaran</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/transaksi" class="dropdown-toggle no-arrow @if ($activePage == 'transaksi') active @endif">
                            <span class="micon dw dw-newspaper-1"></span><span class="mtext">Data Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/pemasukan" class="dropdown-toggle no-arrow @if ($activePage == 'pemasukan') active @endif">
                            <span class="micon dw dw-wallet"></span><span class="mtext">Data Pemasukan</span>
                        </a>
                    </li>
                    @endif

                    {{-- ══ MENU HUTANG (hanya owner & kasir) ══ --}}
                    @if(in_array(Auth::user()->level, ['1', '2']))
                    <li>
                        <a href="/admin/hutang" class="dropdown-toggle no-arrow @if ($activePage == 'hutang') active @endif">
                            <span class="micon dw dw-wallet-1" style="@if($activePage == 'hutang') @else color:primary; @endif"></span>
                            <span class="mtext">
                                Data Hutang
                                @php
                                    $jatuhTempoBadge = \Illuminate\Support\Facades\DB::table('hutang')
                                        ->where('status', 'belum')
                                        ->where('jatuh_tempo', '<', date('Y-m-d'))
                                        ->when(Auth::user()->level != '1', fn($q) => $q->where('id_user', Auth::id()))
                                        ->count();
                                @endphp
                                @if($jatuhTempoBadge > 0)
                                    <span class="hutang-badge">{{ $jatuhTempoBadge }}</span>
                                @endif
                            </span>
                        </a>
                    </li>

                    {{-- ══ MENU PIUTANG (hanya owner & kasir) ══ --}}
                    <li>
                        <a href="/admin/piutang" class="dropdown-toggle no-arrow @if ($activePage == 'piutang') active @endif">
                            <span class="micon dw dw-money-2"></span>
                            <span class="mtext">
                                Data Piutang
                                @php
                                    $piutangTempoBadge = \Illuminate\Support\Facades\DB::table('piutang')
                                        ->where('status', 'belum')
                                        ->where('jatuh_tempo', '<', date('Y-m-d'))
                                        ->when(Auth::user()->level != '1', fn($q) => $q->where('id_user', Auth::id()))
                                        ->count();
                                @endphp
                                @if($piutangTempoBadge > 0)
                                    <span class="piutang-badge">{{ $piutangTempoBadge }}</span>
                                @endif
                            </span>
                        </a>
                    </li>

                    {{-- ══ MENU KASIR (hanya owner & kasir, bukan operator) ══ --}}
                    @if(in_array(Auth::User()->level, ['1', '2']))
                    <li>
                        <a href="/admin/kasir" class="dropdown-toggle no-arrow @if ($activePage == 'kasir') active @endif">
                            <span class="micon dw dw-shop"></span>
                            <span class="mtext">
                                Sesi Kasir
                                @php
                                    $kasirAktifBadge = \Illuminate\Support\Facades\DB::table('kasir_session')
                                        ->where('id_user', Auth::id())
                                        ->where('status', 'buka')
                                        ->count();
                                @endphp
                                @if($kasirAktifBadge > 0)
                                    <span class="kasir-badge">Buka</span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/setor_tarik" class="dropdown-toggle no-arrow @if ($activePage == 'setor_tarik') active @endif">
                            <span class="micon dw dw-money-2"></span><span class="mtext">Tarik / Setor</span>
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="/admin/income" class="dropdown-toggle no-arrow @if ($activePage == 'income') active @endif">
                            <span class="micon dw dw-analytics-4"></span><span class="mtext">Data Income</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20">

            {{-- ══ FLASH MESSAGE (hanya tampil sekali, lalu session dihapus) ══ --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="dw dw-warning"></i> Perhatian!</strong> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @php session()->forget('error'); @endphp
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="dw dw-check"></i> Berhasil!</strong> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @php session()->forget('success'); @endphp
            @endif
            {{-- ══ END FLASH MESSAGE ══ --}}

            @yield('content')

            <div class="footer-wrap pd-20 mb-20 card-box">
                Toko Buyung Lubuk Basung - Copyright © {{date('Y')}}
                <a href="https://furgetech.com" style="text-decoration: none" target="_blank">Furgetech Theme</a>
            </div>
        </div>
    </div>

    <!-- ═══ JS LIBRARIES ═══════════════════════════════════════════════════ -->
    <script src="{{url('assets-admin')}}/vendors/scripts/core.js"></script>
    <script src="{{url('assets-admin')}}/vendors/scripts/script.min.js"></script>
    <script src="{{url('assets-admin')}}/vendors/scripts/process.js"></script>
    <script src="{{url('assets-admin')}}/vendors/scripts/layout-settings.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/dataTables.buttons.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/buttons.bootstrap4.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/buttons.print.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/buttons.html5.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/buttons.flash.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/pdfmake.min.js"></script>
    <script src="{{url('assets-admin')}}/src/plugins/datatables/js/vfs_fonts.js"></script>
    <script src="{{url('assets-admin')}}/vendors/scripts/datatable-setting.js"></script>

    <script>
        // Alert auto-hide
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function() { $(this).remove(); });
        }, 5000);

        // Select2 init
        $(document).ready(function() { $('.select2').select2(); });

        // LOGO_URL tersedia global untuk bt-printer.js
        window.LOGO_URL = "{{ url('assets-admin') }}/vendors/images/favicon-32x32.png";
    </script>

    <script src="{{ url('assets-admin') }}/js/bt-printer.js"></script>
    <script src="{{ url('assets-admin') }}/js/serial-printer.js"></script>

</body>
</html>