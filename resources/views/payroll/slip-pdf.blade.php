<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .slip-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .employee-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th,
        .salary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .salary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .net-salary-row {
            background-color: #e8f5e8;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. GLOBAL ARTA XPRESS</div>
        <div>Jl. Contoh No. 123, Jakarta</div>
        <div>Telp: (021) 1234567 | Email: info@gax.com</div>
        <div class="slip-title">SLIP GAJI KARYAWAN</div>
    </div>

    <div class="employee-info">
        <div class="info-row">
            <div class="info-label">Nama Karyawan</div>
            <div class="info-value">: {{ $payroll->user->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Departemen</div>
            <div class="info-value">: {{ $payroll->user->employee->department->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Posisi</div>
            <div class="info-value">: {{ $payroll->user->employee->position->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Periode</div>
            <div class="info-value">: {{ $payroll->payrollPeriod->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div class="info-value">: {{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <table class="salary-table">
        <thead>
            <tr>
                <th width="60%">Komponen Gaji</th>
                <th width="40%" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Gaji Pokok -->
            <tr>
                <td><strong>Gaji Pokok</strong></td>
                <td class="text-right"><strong>{{ number_format($payroll->basic_salary, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Tunjangan -->
            @if($payroll->details->where('salaryComponent.type', 'allowance')->count() > 0)
                @foreach($payroll->details->where('salaryComponent.type', 'allowance') as $detail)
                <tr>
                    <td>{{ $detail->salaryComponent->name }}</td>
                    <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endif

            <!-- Lembur -->
            @if($payroll->overtime_amount > 0)
            <tr>
                <td>Lembur ({{ $payroll->total_overtime_hours }} jam)</td>
                <td class="text-right">{{ number_format($payroll->overtime_amount, 0, ',', '.') }}</td>
            </tr>
            @endif

            <!-- Total Kotor -->
            <tr class="total-row">
                <td><strong>TOTAL GAJI KOTOR</strong></td>
                <td class="text-right"><strong>{{ number_format($payroll->gross_salary, 0, ',', '.') }}</strong></td>
            </tr>

            <!-- Potongan -->
            @if($payroll->details->where('salaryComponent.type', 'deduction')->count() > 0)
                @foreach($payroll->details->where('salaryComponent.type', 'deduction') as $detail)
                <tr>
                    <td>{{ $detail->salaryComponent->name }}</td>
                    <td class="text-right">({{ number_format($detail->amount, 0, ',', '.') }})</td>
                </tr>
                @endforeach
            @endif

            <!-- Pajak -->
            @if($payroll->tax_amount > 0)
            <tr>
                <td>Pajak PPh 21</td>
                <td class="text-right">({{ number_format($payroll->tax_amount, 0, ',', '.') }})</td>
            </tr>
            @endif

            <!-- Gaji Bersih -->
            <tr class="net-salary-row">
                <td><strong>GAJI BERSIH</strong></td>
                <td class="text-right"><strong>{{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Attendance Summary -->
    <table class="salary-table">
        <thead>
            <tr>
                <th colspan="2" class="text-center">Ringkasan Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Hari Kerja</td>
                <td class="text-right">{{ $payroll->total_working_days }} hari</td>
            </tr>
            <tr>
                <td>Hari Hadir</td>
                <td class="text-right">{{ $payroll->total_present_days }} hari</td>
            </tr>
            <tr>
                <td>Hari Tidak Hadir</td>
                <td class="text-right">{{ $payroll->total_absent_days }} hari</td>
            </tr>
            <tr>
                <td>Hari Terlambat</td>
                <td class="text-right">{{ $payroll->total_late_days }} hari</td>
            </tr>
            @if($payroll->total_overtime_hours > 0)
            <tr>
                <td>Total Jam Lembur</td>
                <td class="text-right">{{ $payroll->total_overtime_hours }} jam</td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($payroll->notes)
    <div style="margin-top: 20px;">
        <strong>Catatan:</strong><br>
        {{ $payroll->notes }}
    </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div>Karyawan</div>
            <div class="signature-line">{{ $payroll->user->name }}</div>
        </div>
        <div class="signature">
            <div>HRD</div>
            <div class="signature-line">
                @if($payroll->approved_by)
                    {{ $payroll->approvedBy->name }}
                @else
                    ___________________
                @endif
            </div>
        </div>
    </div>

    <div class="print-date">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
        @if($payroll->approved_at)
        | Disetujui pada: {{ $payroll->approved_at->format('d/m/Y H:i:s') }}
        @endif
    </div>

    <script>
        // Auto print when page loads (for PDF generation)
        window.onload = function() {
            // Uncomment the line below if you want auto-print
            // window.print();
        }
    </script>
</body>
</html>
