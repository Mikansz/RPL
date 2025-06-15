<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Cuti - {{ $leave->user->full_name }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .slip-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .slip-number {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            align-items: flex-start;
        }
        
        .info-label {
            width: 180px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
            min-height: 16px;
        }
        
        .reason-box {
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 60px;
            background-color: #f9f9f9;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .footer-note {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak Slip
    </button>

    <div class="slip-container">
        <div class="header">
            <div class="company-name">PT. RHI (RIAU HIJAU INDONESIA)</div>
            <div class="company-address">
                Jl. Contoh Alamat No. 123, Kota, Provinsi 12345<br>
                Telp: (021) 1234-5678 | Email: info@rhi.com
            </div>
            <div class="slip-title">Slip Izin Cuti</div>
            <div class="slip-number">No: SLIP/{{ $leave->id }}/{{ date('Y') }}</div>
        </div>

        <div class="content">
            <!-- Employee Information -->
            <div class="info-section">
                <div class="section-title">Data Karyawan</div>
                <div class="info-row">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value">{{ $leave->user->full_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ID Karyawan</div>
                    <div class="info-value">{{ $leave->user->employee_id ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Departemen</div>
                    <div class="info-value">{{ $leave->user->employee->department->name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jabatan</div>
                    <div class="info-value">{{ $leave->user->employee->position->name ?? '-' }}</div>
                </div>
            </div>

            <!-- Leave Information -->
            <div class="info-section">
                <div class="section-title">Data Cuti</div>
                <div class="info-row">
                    <div class="info-label">Jenis Cuti</div>
                    <div class="info-value">{{ $leave->leaveType->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Mulai</div>
                    <div class="info-value">{{ $leave->start_date->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Selesai</div>
                    <div class="info-value">{{ $leave->end_date->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Durasi</div>
                    <div class="info-value">{{ $leave->formatted_duration }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-approved">{{ strtoupper($leave->status) }}</span>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="info-section">
                <div class="section-title">Alasan Cuti</div>
                <div class="reason-box">
                    {{ $leave->reason }}
                </div>
            </div>

            @if($leave->notes)
            <!-- Notes -->
            <div class="info-section">
                <div class="section-title">Catatan Tambahan</div>
                <div class="reason-box">
                    {{ $leave->notes }}
                </div>
            </div>
            @endif

            @if($leave->emergency_contact)
            <!-- Emergency Contact -->
            <div class="info-section">
                <div class="section-title">Kontak Darurat</div>
                <div class="info-row">
                    <div class="info-label">Nama Kontak</div>
                    <div class="info-value">{{ $leave->emergency_contact }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nomor Telepon</div>
                    <div class="info-value">{{ $leave->emergency_phone ?? '-' }}</div>
                </div>
            </div>
            @endif

            <!-- Approval Information -->
            <div class="info-section">
                <div class="section-title">Data Persetujuan</div>
                <div class="info-row">
                    <div class="info-label">Disetujui Oleh</div>
                    <div class="info-value">{{ $leave->approvedBy->full_name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Persetujuan</div>
                    <div class="info-value">{{ $leave->approved_at ? $leave->approved_at->format('d F Y H:i') : '-' }}</div>
                </div>
                @if($leave->approval_notes)
                <div class="info-row">
                    <div class="info-label">Catatan Persetujuan</div>
                    <div class="info-value">{{ $leave->approval_notes }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Pemohon</div>
                <div class="signature-line">{{ $leave->user->full_name }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Atasan Langsung</div>
                <div class="signature-line">{{ $leave->approvedBy->full_name ?? '________________' }}</div>
            </div>
        </div>

        <div class="footer-note">
            Slip ini digenerate secara otomatis pada {{ now()->format('d F Y H:i:s') }}<br>
            Dokumen ini sah tanpa tanda tangan basah
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
