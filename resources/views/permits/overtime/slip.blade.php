<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Lembur - {{ $overtime->user->name }}</title>
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
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
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
        .calculation-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .calculation-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .amount-total {
            border-top: 2px solid #007bff;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
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
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background-color: #0056b3;
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
            <div class="slip-title">SLIP LEMBUR KARYAWAN</div>
            <div class="slip-number">No: OT-{{ str_pad($overtime->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Nama Karyawan:</div>
                <div class="info-value">{{ $overtime->user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Karyawan:</div>
                <div class="info-value">{{ $overtime->user->employee->employee_id ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Departemen:</div>
                <div class="info-value">{{ $overtime->user->employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Posisi:</div>
                <div class="info-value">{{ $overtime->user->employee->position->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Tanggal Lembur:</div>
                <div class="info-value">{{ $overtime->overtime_date->format('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jam Mulai:</div>
                <div class="info-value">{{ $overtime->start_time->format('H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jam Selesai:</div>
                <div class="info-value">{{ $overtime->end_time->format('H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Durasi:</div>
                <div class="info-value">{{ $overtime->planned_hours }} jam</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    @if($overtime->status === 'approved')
                        <span class="status-badge status-approved">Disetujui</span>
                    @elseif($overtime->status === 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @else
                        <span class="status-badge status-rejected">Ditolak</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Deskripsi Pekerjaan:</div>
                <div class="info-value">{{ $overtime->work_description }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Alasan Lembur:</div>
                <div class="info-value">{{ $overtime->reason }}</div>
            </div>
        </div>

        @if($overtime->status === 'approved')
        <div class="calculation-section">
            <div class="calculation-title">Perhitungan Lembur</div>
            <div class="amount-row">
                <span>Jam Lembur:</span>
                <span>{{ $overtime->actual_hours ?? $overtime->planned_hours }} jam</span>
            </div>
            <div class="amount-row">
                <span>Tarif per Jam:</span>
                <span>Rp {{ number_format($overtime->overtime_rate ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="amount-row">
                <span>Multiplier:</span>
                <span>1.5x</span>
            </div>
            <div class="amount-row amount-total">
                <span>Total Lembur:</span>
                <span>Rp {{ number_format($overtime->overtime_amount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        @if($overtime->status === 'approved')
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Disetujui Oleh:</div>
                <div class="info-value">{{ $overtime->approvedBy->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Persetujuan:</div>
                <div class="info-value">{{ $overtime->approved_at ? $overtime->approved_at->format('d F Y H:i') : 'N/A' }}</div>
            </div>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div>Karyawan</div>
                <div class="signature-line">{{ $overtime->user->name }}</div>
            </div>
            <div class="signature-box">
                <div>Atasan Langsung</div>
                <div class="signature-line">{{ $overtime->approvedBy->name ?? '________________' }}</div>
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
