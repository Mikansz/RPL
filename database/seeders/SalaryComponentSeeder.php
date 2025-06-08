<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryComponent;

class SalaryComponentSeeder extends Seeder
{
    public function run()
    {
        $components = [
            // Allowances
            [
                'name' => 'Tunjangan Transport',
                'code' => 'TRANSPORT',
                'type' => 'allowance',
                'calculation_type' => 'fixed',
                'default_amount' => 500000,
                'is_taxable' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Tunjangan Makan',
                'code' => 'MEAL',
                'type' => 'allowance',
                'calculation_type' => 'fixed',
                'default_amount' => 600000,
                'is_taxable' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Tunjangan Komunikasi',
                'code' => 'COMMUNICATION',
                'type' => 'allowance',
                'calculation_type' => 'fixed',
                'default_amount' => 300000,
                'is_taxable' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Tunjangan Jabatan',
                'code' => 'POSITION',
                'type' => 'allowance',
                'calculation_type' => 'percentage',
                'percentage' => 20,
                'is_taxable' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Tunjangan Keluarga',
                'code' => 'FAMILY',
                'type' => 'allowance',
                'calculation_type' => 'percentage',
                'percentage' => 10,
                'is_taxable' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Bonus Kinerja',
                'code' => 'PERFORMANCE',
                'type' => 'allowance',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_taxable' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Lembur',
                'code' => 'OVERTIME',
                'type' => 'allowance',
                'calculation_type' => 'formula',
                'formula' => '(basic_salary / 173) * overtime_hours * 1.5',
                'is_taxable' => true,
                'sort_order' => 7,
            ],

            // Deductions
            [
                'name' => 'BPJS Kesehatan (Karyawan)',
                'code' => 'BPJS_HEALTH_EMP',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'percentage' => 1,
                'is_taxable' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'BPJS Ketenagakerjaan (Karyawan)',
                'code' => 'BPJS_WORK_EMP',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'percentage' => 2,
                'is_taxable' => false,
                'sort_order' => 11,
            ],
            [
                'name' => 'PPh 21',
                'code' => 'TAX_PPH21',
                'type' => 'deduction',
                'calculation_type' => 'formula',
                'formula' => 'calculate_pph21(taxable_income)',
                'is_taxable' => false,
                'sort_order' => 12,
            ],
            [
                'name' => 'Potongan Keterlambatan',
                'code' => 'LATE_DEDUCTION',
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_taxable' => false,
                'sort_order' => 13,
            ],
            [
                'name' => 'Potongan Alpha',
                'code' => 'ABSENT_DEDUCTION',
                'type' => 'deduction',
                'calculation_type' => 'formula',
                'formula' => '(basic_salary / working_days) * absent_days',
                'is_taxable' => false,
                'sort_order' => 14,
            ],
            [
                'name' => 'Pinjaman Karyawan',
                'code' => 'LOAN',
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_taxable' => false,
                'sort_order' => 15,
            ],

            // Benefits (Company contributions)
            [
                'name' => 'BPJS Kesehatan (Perusahaan)',
                'code' => 'BPJS_HEALTH_COMP',
                'type' => 'benefit',
                'calculation_type' => 'percentage',
                'percentage' => 4,
                'is_taxable' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'BPJS Ketenagakerjaan (Perusahaan)',
                'code' => 'BPJS_WORK_COMP',
                'type' => 'benefit',
                'calculation_type' => 'percentage',
                'percentage' => 3.7,
                'is_taxable' => false,
                'sort_order' => 21,
            ],
        ];

        foreach ($components as $component) {
            SalaryComponent::create($component);
        }
    }
}
