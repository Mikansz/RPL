@extends('layouts.app')

@section('title', 'Kalender Jadwal')
@section('page-title', 'Kalender Jadwal Kerja')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar me-2"></i>
                    Kalender Jadwal - {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                </h5>
                <div>
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm me-2">
                        <i class="fas fa-list me-1"></i>
                        List View
                    </a>
                    @if(auth()->user()->hasPermission('schedules.create'))
                    <a href="{{ route('schedules.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        Tambah Jadwal
                    </a>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <!-- Month Navigation -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('schedules.calendar', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-chevron-left"></i>
                        Bulan Sebelumnya
                    </a>
                    
                    <h4 class="mb-0">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</h4>
                    
                    <a href="{{ route('schedules.calendar', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}" 
                       class="btn btn-outline-primary">
                        Bulan Berikutnya
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <!-- Legend -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">WFO</span>
                                <small>Work From Office</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">WFA</span>
                                <small>Work From Anywhere</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">Scheduled</span>
                                <small>Menunggu Approval</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">Approved</span>
                                <small>Sudah Disetujui</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="table-responsive">
                    <table class="table table-bordered calendar-table">
                        <thead>
                            <tr class="bg-light">
                                <th class="text-center">Minggu</th>
                                <th class="text-center">Senin</th>
                                <th class="text-center">Selasa</th>
                                <th class="text-center">Rabu</th>
                                <th class="text-center">Kamis</th>
                                <th class="text-center">Jumat</th>
                                <th class="text-center">Sabtu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $startDate = \Carbon\Carbon::create($year, $month, 1);
                                $endDate = $startDate->copy()->endOfMonth();
                                $startCalendar = $startDate->copy()->startOfWeek();
                                $endCalendar = $endDate->copy()->endOfWeek();
                                
                                $current = $startCalendar->copy();
                                $schedulesByDate = $schedules->groupBy(function($schedule) {
                                    return $schedule->schedule_date->format('Y-m-d');
                                });
                            @endphp
                            
                            @while($current <= $endCalendar)
                            <tr>
                                @for($i = 0; $i < 7; $i++)
                                    @php
                                        $dateKey = $current->format('Y-m-d');
                                        $daySchedules = $schedulesByDate->get($dateKey, collect());
                                        $isCurrentMonth = $current->month == $month;
                                        $isToday = $current->isToday();
                                    @endphp
                                    <td class="calendar-day {{ !$isCurrentMonth ? 'text-muted bg-light' : '' }} {{ $isToday ? 'bg-warning bg-opacity-25' : '' }}" 
                                        style="height: 120px; vertical-align: top;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold {{ $isToday ? 'text-primary' : '' }}">
                                                {{ $current->day }}
                                            </span>
                                            @if($isToday)
                                                <small class="badge bg-warning">Hari Ini</small>
                                            @endif
                                        </div>
                                        
                                        @if($daySchedules->count() > 0)
                                            <div class="schedule-items">
                                                @foreach($daySchedules->take(3) as $schedule)
                                                    <div class="mb-1">
                                                        <div class="d-flex align-items-center">
                                                            @if($schedule->work_type == 'WFO')
                                                                <span class="badge bg-primary me-1" style="font-size: 0.7em;">WFO</span>
                                                            @else
                                                                <span class="badge bg-success me-1" style="font-size: 0.7em;">WFA</span>
                                                            @endif
                                                            
                                                            @if($schedule->status == 'approved')
                                                                <i class="fas fa-check-circle text-success" title="Approved"></i>
                                                            @elseif($schedule->status == 'cancelled')
                                                                <i class="fas fa-times-circle text-danger" title="Cancelled"></i>
                                                            @else
                                                                <i class="fas fa-clock text-warning" title="Scheduled"></i>
                                                            @endif
                                                        </div>
                                                        <small class="text-truncate d-block" style="font-size: 0.7em;">
                                                            {{ $schedule->user->first_name }}
                                                        </small>
                                                        <small class="text-muted" style="font-size: 0.65em;">
                                                            {{ $schedule->shift->formatted_start_time }}
                                                        </small>
                                                    </div>
                                                @endforeach
                                                
                                                @if($daySchedules->count() > 3)
                                                    <small class="text-muted">
                                                        +{{ $daySchedules->count() - 3 }} lainnya
                                                    </small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    @php $current->addDay(); @endphp
                                @endfor
                            </tr>
                            @endwhile
                        </tbody>
                    </table>
                </div>

                <!-- Schedule Details Modal -->
                <div class="modal fade" id="scheduleModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detail Jadwal</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="scheduleModalBody">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.calendar-table {
    table-layout: fixed;
}

.calendar-day {
    width: 14.28%;
    padding: 8px;
    position: relative;
}

.schedule-items {
    max-height: 80px;
    overflow-y: auto;
}

.schedule-items::-webkit-scrollbar {
    width: 3px;
}

.schedule-items::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.schedule-items::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.calendar-day:hover {
    background-color: #f8f9fa !important;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Click on calendar day to show schedules
    $('.calendar-day').click(function() {
        const date = $(this).find('.fw-bold').text();
        const month = {{ $month }};
        const year = {{ $year }};
        
        // Format date properly
        const fullDate = `${year}-${month.toString().padStart(2, '0')}-${date.padStart(2, '0')}`;
        
        // Get schedules for this date
        const schedules = @json($schedules);
        const daySchedules = schedules.filter(schedule => schedule.schedule_date === fullDate);
        
        if (daySchedules.length > 0) {
            showScheduleModal(fullDate, daySchedules);
        }
    });
    
    function showScheduleModal(date, schedules) {
        const formattedDate = new Date(date).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        let modalContent = `<h6 class="mb-3">Jadwal untuk ${formattedDate}</h6>`;
        modalContent += '<div class="table-responsive">';
        modalContent += '<table class="table table-sm">';
        modalContent += '<thead><tr><th>Karyawan</th><th>Shift</th><th>Tipe</th><th>Kantor</th><th>Status</th></tr></thead>';
        modalContent += '<tbody>';
        
        schedules.forEach(schedule => {
            const workTypeBadge = schedule.work_type === 'WFO' ? 
                '<span class="badge bg-primary">WFO</span>' : 
                '<span class="badge bg-success">WFA</span>';
                
            const statusBadge = schedule.status === 'approved' ?
                '<span class="badge bg-success">Active</span>' :
                '<span class="badge bg-danger">Cancelled</span>';
                
            modalContent += `
                <tr>
                    <td>${schedule.user.full_name}</td>
                    <td>${schedule.shift.name}<br><small class="text-muted">${schedule.shift.formatted_start_time} - ${schedule.shift.formatted_end_time}</small></td>
                    <td>${workTypeBadge}</td>
                    <td>${schedule.office ? schedule.office.name : '-'}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
        
        modalContent += '</tbody></table></div>';
        
        $('#scheduleModalBody').html(modalContent);
        $('#scheduleModal').modal('show');
    }
});
</script>
@endpush
