<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with('user', 'leaveType', 'approvedBy')
                      ->when(!Auth::user()->hasPermission('leaves.view_all'), function($query) {
                          return $query->where('user_id', Auth::id());
                      })
                      ->latest()
                      ->paginate(10);
        
        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        return view('leaves.create', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $totalDays = $startDate->diff($endDate)->days + 1;

        Leave::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')
                        ->with('success', 'Leave request submitted successfully.');
    }

    public function show(Leave $leave)
    {
        $leave->load('user', 'leaveType', 'approvedBy');
        return view('leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')
                           ->with('error', 'Cannot edit approved/rejected leave.');
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        return view('leaves.edit', compact('leave', 'leaveTypes'));
    }

    public function update(Request $request, Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')
                           ->with('error', 'Cannot update approved/rejected leave.');
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $totalDays = $startDate->diff($endDate)->days + 1;

        $leave->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
        ]);

        return redirect()->route('leaves.index')
                        ->with('success', 'Leave request updated successfully.');
    }

    public function pending()
    {
        $leaves = Leave::with('user', 'leaveType')
                      ->where('status', 'pending')
                      ->latest()
                      ->paginate(10);
        
        return view('leaves.pending', compact('leaves'));
    }

    public function approve(Request $request, Leave $leave)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        return redirect()->route('leaves.pending')
                        ->with('success', 'Leave request approved successfully.');
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        return redirect()->route('leaves.pending')
                        ->with('success', 'Leave request rejected.');
    }
}
