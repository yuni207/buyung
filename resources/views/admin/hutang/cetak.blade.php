<!DOCTYPE html>
<html>
<head>
	<title>Laporan Hutang {{$label}}</title>
	<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="shortcut icon" href="public\assets-admin\vendors\images\apple-touch-icon.png" />
    <style>
        .footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            line-height: 35px;
        }
    </style>
</head>
<body>
<div style="margin-top:-30px; margin-bottom:-20px">
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td class="text-center">
				<p style="font-size:18px;font-weight: bold;font-family: 'Arial', sans-serif;">
					<span style="font-size:30px">TOKO BUYUNG</span><br>
                    <span style="font-size:13px; line-height:1.2; display:block; margin-bottom:5px;">
                        JL.Manggopoh, Kec. Lubuk Basung, Kabupaten Agam, Sumatera Barat 26451
                        <br>
                    </span>
                    <span style="font-size:13px; line-height:1.2; display:block; margin-bottom:5px;">
                        <b>Telp:</b> 0812-3456-7890    
                    </span>
				</p>
			</td>
		</tr>
	</table>
	<hr style="border: 1px solid #000; margin-top: -5px; margin-bottom: 10px;">
	<hr style="border: 2px solid #000; margin-top: -15px; margin-bottom: 10px;">
	<p style="font-size:18px; font-family: 'Arial', sans-serif;font-weight: bold;text-align: center;"><u style="border-bottom: 2px solid #000;">LAPORAN DATA HUTANG</u></p>
	<p style="font-size:14px; font-family: 'Arial', sans-serif;font-weight: bold;text-align: center; margin-top: -8px; margin-bottom: 10px;">
		{{strtoupper($label)}}
	</p>
	<table border="1" cellpadding="3" cellspacing="0" width="100%" style="font-size:12px; font-family: 'Arial', sans-serif;">
		<thead>
			<tr style="background-color: #1b00ff; color: white;">
	          <th class="text-center" width="5%">#</th>
              <th width="24%">Pelanggan</th>
              <th class="text-center" width="12%">Tanggal</th>
              <th class="text-center" width="12%">Jth Tempo</th>
              <th class="text-center" width="16%">Total</th>
              <th class="text-center" width="16%">Terbayar</th>
              <th class="text-center" width="15%">Sisa</th>
	        </tr>
	    </thead>
	    <tbody>
            <?php $no = 1; ?>
            @forelse($hutang as $data)
            <?php $sisa = $data->total - $data->terbayar; ?>
            <tr>
               <td class="text-center">{{$no++}}</td>
               <td>
                  {{ $data->nama_pelanggan }}
                  @if($data->no_hp)
                  <br><span style="font-size:10px;color:#555;">{{ $data->no_hp }}</span>
                  @endif
               </td>
               <td class="text-center">{{ \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') }}</td>
               <td class="text-center">{{ $data->jatuh_tempo ? \Carbon\Carbon::parse($data->jatuh_tempo)->format('d/m/Y') : '-' }}</td>
               <td class="text-right">{{ 'Rp ' . number_format($data->total, 0, ',', '.') }}</td>
               <td class="text-right">{{ 'Rp ' . number_format($data->terbayar, 0, ',', '.') }}</td>
               <td class="text-right">{{ 'Rp ' . number_format($sisa, 0, ',', '.') }} <br><small>({{ $data->status === 'lunas' ? 'Lunas' : 'Belum Lunas' }})</small></td>
            </tr>
            @empty
            <tr>
               <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
        	<tr style="background-color: #1b00ff; color: white;">
        		<th colspan="6">Total Hutang Keseluruhan</th>
                <th align="left">{{ 'Rp ' . number_format($totalKeseluruhan, 0, ',', '.') }}</th>
        	</tr>
        	<tr style="background-color: #1b00ff; color: white;">
        		<th colspan="6">Total Belum Lunas</th>
                <th align="left">{{ 'Rp ' . number_format($totalSisa, 0, ',', '.') }}</th>
        	</tr>
        	<tr style="background-color: #1b00ff; color: white;">
        		<th colspan="6">Total Sudah Lunas</th>
                <th align="left">{{ 'Rp ' . number_format($totalLunas, 0, ',', '.') }}</th>
        	</tr>
        </tfoot>
	</table>
	<table border="0" cellpadding="2" cellspacing="0" width="100%" style="font-size:14px; font-family: 'Arial', sans-serif;">
            <tr>
                <td width="50%" class="text-center"></td>
                <td width="50%" class="text-center">
                    <span style="margin-top: 10px;"><br>Lubuk Basung, {{\Carbon\Carbon::parse(date('Y-m-d'))->locale('id')->translatedFormat('d F Y')}}<br>Owner Toko Buyung</span><br><br><br><br>
                    <span style="font-weight: bold;">Buyung</span><br>
                </td>
            </tr>
    </table>
</div>
<div class="footer">
        <span style="font-size:12px; font-family: 'Arial', sans-serif;color:#a6a4a1">Dokumen ini dicetak dan ditandatangani melalui Website haqqgroup.com
</div>
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>