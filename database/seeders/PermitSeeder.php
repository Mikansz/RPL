<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermitType;

use App\Models\OvertimeRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class PermitSeeder extends Seeder
{
    public function run()
    {
        // Create Permit Types
        $permitTypes = [
            [
                'name' => 'Tukar Hari',
                'code' => 'DAY_EXCHANGE',
                'description' => 'Pertukaran hari kerja dengan hari libur',
                'requires_approval' => true,
                'affects_attendance' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Lembur',
                'code' => 'OVERTIME',
                'description' => 'Kerja lembur di luar jam kerja normal',
                'requires_approval' => true,
                'affects_attendance' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Izin Keluar',
                'code' => 'LEAVE_EARLY',
                'description' => 'Izin keluar sebelum jam kerja selesai',
                'requires_approval' => true,
                'affects_attendance' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Izin Datang Terlambat',
                'code' => 'LATE_ARRIVAL',
                'description' => 'Izin datang terlambat dengan alasan tertentu',
                'requires_approval' => true,
                'affects_attendance' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Izin Tidak Masuk',
                'code' => 'ABSENT',
                'description' => 'Izin tidak masuk kerja (bukan cuti)',
                'requires_approval' => true,
                'affects_attendance' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($permitTypes as $type) {
            PermitType::create($type);
        }

        // Get sample users
        $employees = User::whereHas('employee')->take(5)->get();
        
        if ($employees->count() > 0) {


            // Create sample Overtime requests
            foreach ($employees->take(4) as $employee) {
                OvertimeRequest::create([
                    'user_id' => $employee->id,
                    'overtime_date' => now()->addDays(rand(1, 7)),
                    'start_time' => '17:00',
                    'end_time' => '20:00',
                    'planned_hours' => 3,
                    'work_description' => 'Menyelesaikan laporan bulanan dan persiapan presentasi untuk klien.',
                    'reason' => 'Deadline laporan yang mendesak dan perlu diselesaikan segera.',
                    'status' => 'pending',
                ]);

                OvertimeRequest::create([
                    'user_id' => $employee->id,
                    'overtime_date' => now()->subDays(rand(1, 7)),
                    'start_time' => '17:30',
                    'end_time' => '21:30',
                    'planned_hours' => 4,
                    'actual_hours' => 4,
                    'work_description' => 'Maintenance server dan backup database.',
                    'reason' => 'Maintenance rutin yang harus dilakukan di luar jam kerja.',
                    'status' => 'completed',
                    'approved_by' => User::whereHas('roles', function($q) {
                        $q->where('name', 'hrd');
                    })->first()->id ?? null,
                    'approved_at' => now()->subDays(rand(1, 5)),
                    'approval_notes' => 'Disetujui. Pastikan dokumentasi maintenance lengkap.',
                    'is_completed' => true,
                    'completed_at' => now()->subDays(rand(1, 3)),
                    'overtime_rate' => 25000, // Rp 25,000 per hour
                    'overtime_amount' => 100000, // 4 hours * Rp 25,000
                ]);
            }

            // Create sample Leave requests
            foreach ($employees as $employee) {
                LeaveRequest::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => 1, // Cuti Tahunan
                    'start_date' => now()->addDays(rand(10, 30)),
                    'end_date' => now()->addDays(rand(31, 35)),
                    'total_days' => 3,
                    'reason' => 'Liburan keluarga yang sudah direncanakan sejak lama.',
                    'notes' => 'Sudah koordinasi dengan tim untuk backup pekerjaan.',
                    'emergency_contact' => 'Istri - Sarah',
                    'emergency_phone' => '081234567890',
                    'work_handover' => 'Pekerjaan harian sudah didelegasikan ke rekan tim. Laporan mingguan akan diselesaikan sebelum cuti.',
                    'status' => 'pending',
                ]);

                LeaveRequest::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => 2, // Cuti Sakit
                    'start_date' => now()->subDays(rand(5, 10)),
                    'end_date' => now()->subDays(rand(3, 4)),
                    'total_days' => 2,
                    'reason' => 'Sakit demam dan flu yang cukup parah.',
                    'notes' => 'Sudah periksa ke dokter dan disarankan istirahat total.',
                    'status' => 'approved',
                    'approved_by' => User::whereHas('roles', function($q) {
                        $q->where('name', 'hrd');
                    })->first()->id ?? null,
                    'approved_at' => now()->subDays(rand(1, 3)),
                    'approval_notes' => 'Disetujui. Harap istirahat yang cukup dan segera sembuh.',
                    'attachments' => [
                        [
                            'filename' => 'surat_dokter_' . time() . '.pdf',
                            'original_name' => 'Surat Keterangan Dokter.pdf',
                            'size' => 245760,
                            'uploaded_at' => now()->subDays(rand(1, 3))->toISOString(),
                        ]
                    ],
                ]);

                // Half day leave
                LeaveRequest::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => 1, // Cuti Tahunan
                    'start_date' => now()->addDays(rand(5, 15)),
                    'end_date' => now()->addDays(rand(5, 15)),
                    'total_days' => 0.5,
                    'reason' => 'Ada keperluan keluarga yang mendesak.',
                    'status' => 'pending',
                    'is_half_day' => true,
                    'half_day_type' => 'afternoon',
                ]);
            }
        }

        $this->command->info('Permit data seeded successfully!');
    }
}
