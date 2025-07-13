<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Anggaran - {{ $pengajuan->project_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; color: #333; font-size: 10pt; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 18pt; font-weight: bold; }
        .title { font-size: 14pt; margin: 5px 0; }
        .info-table, .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .info-label { font-weight: bold; width: 30%; }
        .detail-table th, .detail-table td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
        .detail-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
        .signature-section { margin-top: 40px; width: 100%; }
        .signature-box { width: 30%; text-align: center; float: left; margin: 0 1.5%; }
        .signature-box.right { float: right; }
        .signature-name { margin-top: 60px; font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 8pt; color: #666; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Efarina TV</div>
        <div class="title">FORMULIR PENGAJUAN ANGGARAN</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nama Proyek/Kegiatan</td>
            <td colspan="3">{{ $pengajuan->project_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Pengajuan</td>
            <td>{{ $pengajuan->created_at->format('d F Y') }}</td>
            <td class="info-label">Status</td>
            <td>{{ $pengajuan->status_text }}</td>
        </tr>
        <tr>
            <td class="info-label">Diajukan Oleh</td>
            <td>{{ $pengajuan->user->name }}</td>
            <td class="info-label">Penanggung Jawab</td>
            <td>{{ $pengajuan->pic->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Anggaran</td>
            <td>{{ $pengajuan->budgetType->name ?? 'Tidak Ditentukan' }}</td>
            <td class="info-label">Tingkat Urgensi</td>
            <td>{{ $pengajuan->urgency_text }}</td>
        </tr>
        <tr>
            <td class="info-label">Deskripsi & Tujuan</td>
            <td colspan="3" style="line-height: 1.5;">{{ $pengajuan->description }}</td>
        </tr>
    </table>

    <div style="text-align:center; font-weight:bold; margin-bottom: 10px; font-size: 12pt;">RINCIAN KEBUTUHAN ANGGARAN</div>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Deskripsi Item</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 8%;">Qty</th>
                <th style="width: 20%;" class="text-right">Harga Satuan</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengajuan->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->item_description }}</td>
                <td>{{ $detail->category->name ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $detail->quantity ?? 1 }}</td>
                <td class="text-right">{{ $detail->formatted_unit_price }}</td>
                <td class="text-right">{{ $detail->formatted_amount }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="total text-right">TOTAL KESELURUHAN</td>
                <td class="total text-right">{{ $pengajuan->formatted_total_amount }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div>Diajukan Oleh,</div>
            <div class="signature-name">{{ $pengajuan->user->name }}</div>
            <div>Pemohon</div>
        </div>
        <div class="signature-box right">
            <div>Disetujui Oleh,</div>
            <div class="signature-name">{{ $pengajuan->approver->name ?? '(______________________)' }}</div>
            <div>Manajer Keuangan / Direktur</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dicetak pada {{ now()->format('d F Y, H:i') }} oleh {{ auth()->user()->name }}.
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px;">Cetak Dokumen</button>
    </div>
</body>
</html>