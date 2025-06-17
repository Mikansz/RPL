@extends('layouts.app')

@section('title', 'Manajemen Cuti - Test')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Manajemen Cuti - Test Page</h1>
            
            <div class="alert alert-success">
                <h4>✅ Halaman berhasil dimuat!</h4>
                <p>Controller method <code>leaveManagement()</code> berfungsi dengan baik.</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Debug Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total Leaves:</strong> {{ $leaves->count() }}</p>
                    <p><strong>Current Page:</strong> {{ $leaves->currentPage() }}</p>
                    <p><strong>Total Pages:</strong> {{ $leaves->lastPage() }}</p>
                    <p><strong>Leave Types Count:</strong> {{ $leaveTypes->count() }}</p>
                    
                    <h6>Stats:</h6>
                    <ul>
                        <li>Total: {{ $stats['total'] }}</li>
                        <li>Pending: {{ $stats['pending'] }}</li>
                        <li>Approved: {{ $stats['approved'] }}</li>
                        <li>Rejected: {{ $stats['rejected'] }}</li>
                        <li>This Month: {{ $stats['total_this_month'] }}</li>
                        <li>Days This Month: {{ $stats['total_days_this_month'] }}</li>
                    </ul>
                </div>
            </div>

            @if($leaves->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Sample Leave Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves->take(5) as $leave)
                                <tr>
                                    <td>{{ $leave->id }}</td>
                                    <td>{{ $leave->user->full_name ?? 'N/A' }}</td>
                                    <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                    <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info mt-4">
                <h5>No Leave Requests Found</h5>
                <p>Tidak ada data pengajuan cuti yang ditemukan.</p>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('permits.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Permits
                </a>
                <a href="{{ route('permits.leave.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>Daftar Cuti
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
