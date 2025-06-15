@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-2"></i>Tandai Semua Dibaca
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-success w-100" onclick="refreshNotifications()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-warning w-100" onclick="filterUnread()">
                    <i class="fas fa-filter me-2"></i>Belum Dibaca
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-danger w-100" onclick="clearAll()">
                    <i class="fas fa-trash me-2"></i>Hapus Semua
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('notifications.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">Semua Jenis</option>
                        <option value="leave_request" {{ request('type') == 'leave_request' ? 'selected' : '' }}>Pengajuan Cuti</option>
                        <option value="overtime_request" {{ request('type') == 'overtime_request' ? 'selected' : '' }}>Pengajuan Lembur</option>
                        <option value="attendance_alert" {{ request('type') == 'attendance_alert' ? 'selected' : '' }}>Alert Absensi</option>
                        <option value="payroll_ready" {{ request('type') == 'payroll_ready' ? 'selected' : '' }}>Slip Gaji</option>
                        <option value="system_update" {{ request('type') == 'system_update' ? 'selected' : '' }}>Update Sistem</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="priority">
                        <option value="">Semua Prioritas</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Notifications List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Daftar Notifikasi ({{ $notifications->total() ?? 0 }})</h5>
        <div>
            <span class="badge bg-danger">{{ $unread_count ?? 0 }} Belum Dibaca</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(isset($notifications) && $notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light border-start border-primary border-3' }}" 
                     onclick="markAsRead({{ $notification->id }})">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    @php
                                        $typeIcons = [
                                            'leave_request' => 'calendar-times',
                                            'overtime_request' => 'clock',
                                            'attendance_alert' => 'exclamation-triangle',
                                            'payroll_ready' => 'money-bill-wave',
                                            'system_update' => 'cog'
                                        ];
                                        $typeColors = [
                                            'leave_request' => 'success',
                                            'overtime_request' => 'info',
                                            'attendance_alert' => 'warning',
                                            'payroll_ready' => 'primary',
                                            'system_update' => 'secondary'
                                        ];
                                    @endphp
                                    <div class="bg-{{ $typeColors[$notification->type] ?? 'secondary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $typeIcons[$notification->type] ?? 'bell' }} text-{{ $typeColors[$notification->type] ?? 'secondary' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 {{ $notification->read_at ? 'text-muted' : '' }}">
                                        {{ $notification->title ?? 'Notifikasi' }}
                                        @if(!$notification->read_at)
                                            <span class="badge bg-danger ms-2">Baru</span>
                                        @endif
                                    </h6>
                                    <p class="mb-1 {{ $notification->read_at ? 'text-muted' : '' }}">
                                        {{ $notification->message ?? 'Tidak ada pesan' }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($notification->action_url)
                                <div class="mb-2">
                                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-end">
                            <small class="text-muted">
                                {{ $notification->created_at ? $notification->created_at->diffForHumans() : 'N/A' }}
                            </small>
                            <br>
                            @if($notification->priority)
                                @php
                                    $priorityColors = [
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'success'
                                    ];
                                    $priorityLabels = [
                                        'high' => 'Tinggi',
                                        'medium' => 'Sedang',
                                        'low' => 'Rendah'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$notification->priority] ?? 'secondary' }}">
                                    {{ $priorityLabels[$notification->priority] ?? ucfirst($notification->priority) }}
                                </span>
                            @endif
                            
                            <div class="btn-group btn-group-sm mt-1">
                                @if(!$notification->read_at)
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="event.stopPropagation(); markAsRead({{ $notification->id }})" title="Tandai Dibaca">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                    </div>
                    <div>
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5>Tidak ada notifikasi</h5>
                <p class="text-muted">Belum ada notifikasi untuk ditampilkan.</p>
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $unread_count ?? 0 }}</h4>
                        <p class="mb-0">Belum Dibaca</p>
                    </div>
                    <div>
                        <i class="fas fa-envelope fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $read_count ?? 0 }}</h4>
                        <p class="mb-0">Sudah Dibaca</p>
                    </div>
                    <div>
                        <i class="fas fa-envelope-open fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $today_count ?? 0 }}</h4>
                        <p class="mb-0">Hari Ini</p>
                    </div>
                    <div>
                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $high_priority_count ?? 0 }}</h4>
                        <p class="mb-0">Prioritas Tinggi</p>
                    </div>
                    <div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAsRead(notificationId) {
    // Implementation would send AJAX request to mark as read
    console.log('Marking notification as read:', notificationId);
    
    // Simulate marking as read
    setTimeout(() => {
        location.reload();
    }, 500);
}

function markAllAsRead() {
    if (confirm('Apakah Anda yakin ingin menandai semua notifikasi sebagai dibaca?')) {
        // Implementation would send AJAX request
        alert('Fitur mark all as read akan diimplementasikan');
    }
}

function deleteNotification(notificationId) {
    if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
        // Implementation would send AJAX request
        alert('Fitur delete notification akan diimplementasikan');
    }
}

function refreshNotifications() {
    location.reload();
}

function filterUnread() {
    window.location.href = '{{ route('notifications.index') }}?status=unread';
}

function clearAll() {
    if (confirm('Apakah Anda yakin ingin menghapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.')) {
        // Implementation would send AJAX request
        alert('Fitur clear all akan diimplementasikan');
    }
}

$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="type"], select[name="status"], select[name="priority"]').change(function() {
        $(this).closest('form').submit();
    });
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        // Check for new notifications without full page reload
        console.log('Checking for new notifications...');
    }, 30000);
});
</script>
@endpush
