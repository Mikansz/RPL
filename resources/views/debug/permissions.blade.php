@extends('layouts.app')

@section('title', 'Debug Permissions')
@section('page-title', 'Debug User Permissions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bug me-2"></i>
                    Debug User Permissions
                </h5>
            </div>
            
            <div class="card-body">
                @auth
                <div class="row">
                    <div class="col-md-6">
                        <h6>Current User Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ auth()->user()->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td>{{ auth()->user()->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ auth()->user()->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Employee ID:</strong></td>
                                <td>{{ auth()->user()->employee_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>{{ auth()->user()->status }}</td>
                            </tr>
                        </table>

                        <h6>User Roles</h6>
                        @if(auth()->user()->roles->count() > 0)
                        <ul class="list-group">
                            @foreach(auth()->user()->roles as $role)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $role->display_name }} ({{ $role->name }})
                                <span class="badge bg-primary rounded-pill">{{ $role->permissions->count() }} permissions</span>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="alert alert-warning">No roles assigned to this user</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <h6>Schedule-Related Permissions</h6>
                        @php
                            $schedulePermissions = [
                                'schedules.view' => 'View Schedules',
                                'schedules.create' => 'Create Schedules',
                                'schedules.edit' => 'Edit Schedules',
                                'schedules.delete' => 'Delete Schedules',
                                'schedules.approve' => 'Approve Schedules',
                                'shifts.view' => 'View Shifts',
                                'shifts.create' => 'Create Shifts',
                                'shifts.edit' => 'Edit Shifts',
                                'shifts.delete' => 'Delete Shifts',
                                'offices.view' => 'View Offices',
                                'offices.create' => 'Create Offices',
                                'offices.edit' => 'Edit Offices',
                                'offices.delete' => 'Delete Offices',
                            ];
                        @endphp

                        <div class="list-group">
                            @foreach($schedulePermissions as $permission => $description)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $description }}
                                @if(auth()->user()->hasPermission($permission))
                                    <span class="badge bg-success">✅ Yes</span>
                                @else
                                    <span class="badge bg-danger">❌ No</span>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <h6 class="mt-4">Menu Visibility Test</h6>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Jadwal Kerja Menu
                                @if(auth()->user()->hasPermission('schedules.view'))
                                    <span class="badge bg-success">✅ Should be visible</span>
                                @else
                                    <span class="badge bg-warning">⚠️ Hidden by permission</span>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Shift Kerja Menu
                                @if(auth()->user()->hasPermission('shifts.view'))
                                    <span class="badge bg-success">✅ Should be visible</span>
                                @else
                                    <span class="badge bg-warning">⚠️ Hidden by permission</span>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Kantor Menu
                                @if(auth()->user()->hasPermission('offices.view'))
                                    <span class="badge bg-success">✅ Should be visible</span>
                                @else
                                    <span class="badge bg-warning">⚠️ Hidden by permission</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h6>All Available Permissions</h6>
                        @php
                            $allPermissions = \App\Models\Permission::orderBy('module')->orderBy('name')->get();
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Permission</th>
                                        <th>Display Name</th>
                                        <th>User Has Permission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allPermissions as $permission)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $permission->module }}</span></td>
                                        <td><code>{{ $permission->name }}</code></td>
                                        <td>{{ $permission->display_name }}</td>
                                        <td>
                                            @if(auth()->user()->hasPermission($permission->name))
                                                <span class="badge bg-success">✅ Yes</span>
                                            @else
                                                <span class="badge bg-danger">❌ No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h6>Quick Actions</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('schedules.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Test Jadwal Kerja
                            </a>
                            <a href="{{ route('shifts.index') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-business-time me-1"></i>
                                Test Shift Kerja
                            </a>
                            <a href="{{ route('offices.index') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-building me-1"></i>
                                Test Kantor
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    You are not logged in. Please log in to see permission information.
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
