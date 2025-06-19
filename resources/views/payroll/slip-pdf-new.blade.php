<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
            color: #000;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #000;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            font-size: 8px;
            text-align: center;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .company-address {
            font-size: 10px;
            color: #666;
        }
        .slip-title {
            flex: 1;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        .employee-info {
            display: flex;
            padding: 10px 15px;
            border-bottom: 1px solid #000;
        }
        .employee-left, .employee-right {
            flex: 1;
        }
        .info-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 10px;
        }
        .info-label {
            width: 80px;
            font-weight: normal;
        }
        .info-value {
            flex: 1;
            font-weight: bold;
        }
        .salary-section {
            display: flex;
            padding: 0;
        }
        .pendapatan, .potongan {
            flex: 1;
            padding: 10px 15px;
        }
        .pendapatan {
            border-right: 1px solid #000;
        }
        .section-title {
            background-color: #4a90a4;
            color: white;
            padding: 5px 10px;
            margin: 0 -15px 10px -15px;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .salary-table td {
            padding: 2px 5px;
            border: none;
        }
        .salary-table .amount {
            text-align: right;
            width: 80px;
        }
        .total-section {
            background-color: #4a90a4;
            color: white;
            padding: 8px 15px;
            margin: 0;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }
        .net-salary {
            background-color: #2c5f2d;
            color: white;
            padding: 10px 15px;
            margin: 0;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .attendance-section {
            padding: 10px 15px;
            border-top: 1px solid #000;
        }
        .attendance-title {
            background-color: #4a90a4;
            color: white;
            padding: 5px 10px;
            margin: 0 -15px 10px -15px;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .attendance-table td {
            padding: 2px 5px;
            border: none;
        }
        .attendance-table .value {
            text-align: right;
            width: 50px;
        }
        .notes-section {
            padding: 10px 15px;
            border-top: 1px solid #000;
        }
        .notes-title {
            background-color: #4a90a4;
            color: white;
            padding: 5px 10px;
            margin: 0 -15px 10px -15px;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        .notes-content {
            font-size: 10px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div>
                    RUMAH<br>
                    HALAL<br>
                    INDONESIA
                </div>
            </div>
            <div class="company-info">
                <div class="company-name">RUMAH HALAL INDONESIA</div>
                <div class="company-address">Jl. Sempur Kaler No 25<br>Kota Bogor</div>
            </div>
            <div class="slip-title">SLIP GAJI</div>
        </div>

        <!-- Employee Info -->
        <div class="employee-info">
            <div class="employee-left">
                <div class="info-row">
                    <div class="info-label">Nama Karyawan</div>
                    <div class="info-value">{{ $payroll->user->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Dept./Jabatan</div>
                    <div class="info-value">{{ $payroll->user->employee->department->name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Periode</div>
                    <div class="info-value">{{ $payroll->payrollPeriod->name }}</div>
                </div>
            </div>
            <div class="employee-right">
                <div class="info-row">
                    <div class="info-label">Nik Karyawan</div>
                    <div class="info-value">{{ $payroll->user->employee->employee_id ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Finance Staff</div>
                    <div class="info-value">{{ $payroll->user->employee->position->name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tgl Cetak</div>
                    <div class="info-value">{{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Salary Section -->
        <div class="salary-section">
            <!-- Pendapatan -->
            <div class="pendapatan">
                <div class="section-title">PENDAPATAN</div>
                <table class="salary-table">
                    <tr>
                        <td>Gaji Pokok</td>
                        <td class="amount">{{ number_format($payroll->basic_salary, 2, '.', ',') }}</td>
                    </tr>
                    @foreach($payroll->details->where('salaryComponent.type', 'allowance') as $detail)
                    <tr>
                        <td>{{ $detail->salaryComponent->name }}</td>
                        <td class="amount">{{ number_format($detail->amount, 2, '.', ',') }}</td>
                    </tr>
                    @endforeach
                    @if($payroll->overtime_amount > 0)
                    <tr>
                        <td>Lembur</td>
                        <td class="amount">{{ number_format($payroll->overtime_amount, 2, '.', ',') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Transport</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Uang Makan</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Komunikasi</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Kesehatan</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Lembur</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Hari Raya</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Insentif</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Lainnya</td>
                        <td class="amount">0.00</td>
                    </tr>
                </table>
                <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
                    <table class="salary-table">
                        <tr style="font-weight: bold;">
                            <td>Total Pendapatan</td>
                            <td class="amount">{{ number_format($payroll->gross_salary, 2, '.', ',') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Potongan -->
            <div class="potongan">
                <div class="section-title">POTONGAN</div>
                <table class="salary-table">
                    <tr>
                        <td>Kasbon</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Telat</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>Alfa</td>
                        <td class="amount">0.00</td>
                    </tr>
                    <tr>
                        <td>PPh 21</td>
                        <td class="amount">{{ number_format($payroll->tax_amount, 2, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td>Pot.Penyesuaian Lainnya</td>
                        <td class="amount">0.00</td>
                    </tr>
                    @foreach($payroll->details->where('salaryComponent.type', 'deduction') as $detail)
                    <tr>
                        <td>{{ $detail->salaryComponent->name }}</td>
                        <td class="amount">{{ number_format($detail->amount, 2, '.', ',') }}</td>
                    </tr>
                    @endforeach
                </table>
                <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
                    <table class="salary-table">
                        <tr style="font-weight: bold;">
                            <td>Total Potongan</td>
                            <td class="amount">{{ number_format($payroll->total_deductions + $payroll->tax_amount, 2, '.', ',') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Salary -->
        <div class="net-salary">
            Total Penerimaan Bulan Ini<br>
            <span style="font-size: 18px;">{{ number_format($payroll->net_salary, 0, ',', '.') }}</span>
        </div>

        <!-- Attendance Section -->
        <div class="attendance-section">
            <div class="attendance-title">Komponen Informasi Kehadiran</div>
            <table class="attendance-table">
                <tr>
                    <td>Absensi</td>
                    <td class="value">{{ $payroll->total_present_days }}</td>
                    <td>Telat</td>
                    <td class="value">{{ $payroll->total_late_days }}</td>
                </tr>
                <tr>
                    <td>Kurang Hadir</td>
                    <td class="value">{{ $payroll->total_absent_days }}</td>
                    <td>Pulang Lebih Dulu</td>
                    <td class="value">0</td>
                </tr>
                <tr>
                    <td>Ijin</td>
                    <td class="value">0</td>
                    <td>Total Absen</td>
                    <td class="value">{{ $payroll->total_absent_days }}</td>
                </tr>
                <tr>
                    <td>Cuti</td>
                    <td class="value">0</td>
                    <td></td>
                    <td class="value"></td>
                </tr>
                <tr>
                    <td>Sakit</td>
                    <td class="value">0</td>
                    <td></td>
                    <td class="value"></td>
                </tr>
            </table>
        </div>

        <!-- Notes Section -->
        <div class="notes-section">
            <div class="notes-title">Informasi</div>
            <div class="notes-content">
                1. Cuti = Cuti melahirkan
            </div>
            @if($payroll->notes)
            <div class="notes-content">
                2. {{ $payroll->notes }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
