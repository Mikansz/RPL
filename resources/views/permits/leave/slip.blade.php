<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Cuti - {{ $leave->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        .slip-title {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }
        .slip-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            align-items: center;
        }
        .info-label {
            width: 200px;
            font-weight: bold;
            color: #333;
        }
        .info-value {
            flex: 1;
            color: #555;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .leave-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .description-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .description-title {
            font-weight: bold;
            color: #28a745;
            margin-bottom: 15px;
            font-size: 16px;
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
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background-color: #218838;
        }
        .emergency-section {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .slip-container {
                box-shadow: none;
                padding: 0;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <div class="slip-container">
        <div class="header">
            <div class="company-name">PT. GAX INDONESIA</div>
            <div class="slip-title">SLIP CUTI KARYAWAN</div>
            <div class="slip-number">No: LV-{{ str_pad($leave->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Nama Karyawan:</div>
                <div class="info-value">{{ $leave->user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Karyawan:</div>
                <div class="info-value">{{ $leave->user->employee->employee_id ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Departemen:</div>
                <div class="info-value">{{ $leave->user->employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Posisi:</div>
                <div class="info-value">{{ $leave->user->employee->position->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Jenis Cuti:</div>
                <div class="info-value">
                    <span class="leave-type-badge">{{ $leave->leaveType->name }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Mulai:</div>
                <div class="info-value">{{ $leave->start_date->format('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Selesai:</div>
                <div class="info-value">{{ $leave->end_date->format('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Durasi:</div>
                <div class="info-value">
                    {{ $leave->total_days }} hari
                    @if($leave->is_half_day)
                        <small>({{ ucfirst($leave->half_day_type) }} only)</small>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    @if($leave->status === 'approved')
                        <span class="status-badge status-approved">Disetujui</span>
                    @elseif($leave->status === 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @else
                        <span class="status-badge status-rejected">Ditolak</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="description-section">
            <div class="description-title">Alasan Cuti</div>
            <p>{{ $leave->reason }}</p>
            
            @if($leave->notes)
            <div class="description-title" style="margin-top: 20px;">Catatan Tambahan</div>
            <p>{{ $leave->notes }}</p>
            @endif
        </div>

        @if($leave->emergency_contact || $leave->emergency_phone)
        <div class="emergency-section">
            <div class="description-title">Kontak Darurat</div>
            @if($leave->emergency_contact)
            <div class="info-row">
                <div class="info-label">Nama:</div>
                <div class="info-value">{{ $leave->emergency_contact }}</div>
            </div>
            @endif
            @if($leave->emergency_phone)
            <div class="info-row">
                <div class="info-label">Telepon:</div>
                <div class="info-value">{{ $leave->emergency_phone }}</div>
            </div>
            @endif
        </div>
        @endif

        @if($leave->work_handover)
        <div class="description-section">
            <div class="description-title">Serah Terima Pekerjaan</div>
            <p>{{ $leave->work_handover }}</p>
        </div>
        @endif

        @if($leave->status === 'approved')
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Disetujui Oleh:</div>
                <div class="info-value">{{ $leave->approvedBy->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Persetujuan:</div>
                <div class="info-value">{{ $leave->approved_at ? $leave->approved_at->format('d F Y H:i') : 'N/A' }}</div>
            </div>
            @if($leave->approval_notes)
            <div class="info-row">
                <div class="info-label">Catatan Persetujuan:</div>
                <div class="info-value">{{ $leave->approval_notes }}</div>
            </div>
            @endif
        </div>
        @endif

        @if($leave->status === 'rejected' && $leave->rejection_reason)
        <div class="description-section" style="background-color: #f8d7da; border-left: 4px solid #dc3545;">
            <div class="description-title" style="color: #721c24;">Alasan Penolakan</div>
            <p>{{ $leave->rejection_reason }}</p>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div>Karyawan</div>
                <div class="signature-line">{{ $leave->user->name }}</div>
            </div>
            <div class="signature-box">
                <div>Atasan Langsung</div>
                <div class="signature-line">{{ $leave->approvedBy->name ?? '________________' }}</div>
            </div>
            <div class="signature-box">
                <div>HRD</div>
                <div class="signature-line">________________</div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
            Slip ini dicetak pada {{ now()->format('d F Y H:i') }}
        </div>
    </div>
</body>
</html>
