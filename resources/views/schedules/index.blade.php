@extends('layouts.app')

@section('title', 'Jadwal Kerja')
@section('page-title', 'Manajemen Jadwal Kerja')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Daftar Jadwal Kerja
                </h5>
                <div>
                    <a href="{{ route('schedules.calendar') }}" class="btn btn-info btn-sm me-2">
                        <i class="fas fa-calendar me-1"></i>
                        Kalender
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
                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    @if(!auth()->user()->hasRole('karyawan'))
                    <div class="col-md-2">
                        <label class="form-label">Karyawan</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label">Tipe Kerja</label>
                        <select name="work_type" class="form-select">
                            <option value="">Semua</option>
                            <option value="WFO" {{ request('work_type') == 'WFO' ? 'selected' : '' }}>WFO</option>
                            <option value="WFA" {{ request('work_type') == 'WFA' ? 'selected' : '' }}>WFA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Active</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Reset
                        </a>
                    </div>
                </form>

                <!-- Summary -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted">
                            Total: {{ $schedules->total() }} jadwal
                        </small>
                    </div>
                </div>

                <!-- Bulk Actions -->
                @if(auth()->user()->hasPermission('schedules.edit'))
                <div class="mb-3">
                    <form id="bulk-edit-form" method="POST" action="{{ route('schedules.bulk-edit') }}">
                        @csrf
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-outline-primary btn-sm" id="bulk-edit-btn" disabled>
                                <i class="fas fa-edit me-1"></i>
                                Bulk Edit (<span id="selected-count">0</span>)
                            </button>
                            <small class="text-muted">Pilih jadwal untuk bulk edit</small>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                @if(auth()->user()->hasPermission('schedules.edit'))
                                <th width="40">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                @endif
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Shift</th>
                                <th>Tipe Kerja</th>
                                <th>Kantor</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                            <tr>
                                @if(auth()->user()->hasPermission('schedules.edit'))
                                <td>
                                    <input type="checkbox" name="schedule_ids[]" value="{{ $schedule->id }}"
                                           class="form-check-input schedule-checkbox" form="bulk-edit-form">
                                </td>
                                @endif
                                <td>{{ $schedule->schedule_date->format('d/m/Y') }}</td>
                                <td>{{ $schedule->user->full_name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $schedule->shift->name }}</span>
                                    <br>
                                    <small class="text-muted">{{ $schedule->shift->formatted_start_time }} - {{ $schedule->shift->formatted_end_time }}</small>
                                </td>
                                <td>
                                    @if($schedule->work_type == 'WFO')
                                        <span class="badge bg-primary">WFO</span>
                                    @else
                                        <span class="badge bg-success">WFA</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->office)
                                        {{ $schedule->office->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->status == 'approved')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($schedule->status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('schedules.show', $schedule) }}" class="btn btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('schedules.edit'))
                                        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-outline-primary" title="Edit Jadwal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('schedules.delete'))
                                        <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    onclick="return confirm('Hapus jadwal ini?')" title="Hapus Jadwal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasPermission('schedules.edit') ? '8' : '7' }}" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada jadwal ditemukan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#select-all').change(function() {
        const isChecked = $(this).is(':checked');
        $('.schedule-checkbox').prop('checked', isChecked);
        updateBulkEditButton();
    });

    // Handle individual checkboxes
    $('.schedule-checkbox').change(function() {
        updateBulkEditButton();

        // Update select all checkbox
        const totalCheckboxes = $('.schedule-checkbox').length;
        const checkedCheckboxes = $('.schedule-checkbox:checked').length;

        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
        $('#select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    });

    function updateBulkEditButton() {
        const checkedCount = $('.schedule-checkbox:checked').length;
        $('#selected-count').text(checkedCount);
        $('#bulk-edit-btn').prop('disabled', checkedCount === 0);
    }
});
</script>
@endpush
