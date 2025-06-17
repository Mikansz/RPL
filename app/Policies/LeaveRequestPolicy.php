<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any leave requests.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('leave.view') || 
               $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can view the leave request.
     */
    public function view(User $user, LeaveRequest $leaveRequest)
    {
        // User can view their own leave requests
        if ($user->id === $leaveRequest->user_id) {
            return true;
        }

        // Or if they have permission to view all leave requests
        return $user->hasPermission('leave.view_all') || 
               $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can create leave requests.
     */
    public function create(User $user)
    {
        return $user->hasPermission('leave.create') || 
               $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager', 'Employee']);
    }

    /**
     * Determine whether the user can update the leave request.
     */
    public function update(User $user, LeaveRequest $leaveRequest)
    {
        // User can only edit their own pending leave requests
        if ($user->id === $leaveRequest->user_id && $leaveRequest->canBeEdited()) {
            return true;
        }

        // Or if they have admin permissions
        return $user->hasPermission('leave.edit') && 
               $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can delete the leave request.
     */
    public function delete(User $user, LeaveRequest $leaveRequest)
    {
        // User can only delete their own pending leave requests
        if ($user->id === $leaveRequest->user_id && $leaveRequest->canBeEdited()) {
            return true;
        }

        // Or if they have admin permissions
        return $user->hasPermission('leave.delete') && 
               $user->hasAnyRole(['Admin', 'HRD', 'HR']);
    }

    /**
     * Determine whether the user can approve the leave request.
     */
    public function approve(User $user, LeaveRequest $leaveRequest)
    {
        // User cannot approve their own leave request
        if ($user->id === $leaveRequest->user_id) {
            return false;
        }

        // Only pending requests can be approved
        if ($leaveRequest->status !== 'pending') {
            return false;
        }

        // Check if user has permission to approve - prioritize roles for HRD and Admin
        return $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
               $user->hasPermission('leave.approve');
    }

    /**
     * Determine whether the user can reject the leave request.
     */
    public function reject(User $user, LeaveRequest $leaveRequest)
    {
        return $this->approve($user, $leaveRequest);
    }

    /**
     * Determine whether the user can restore the leave request.
     */
    public function restore(User $user, LeaveRequest $leaveRequest)
    {
        return $user->hasAnyRole(['Admin', 'HRD']);
    }

    /**
     * Determine whether the user can permanently delete the leave request.
     */
    public function forceDelete(User $user, LeaveRequest $leaveRequest)
    {
        return $user->hasAnyRole(['Admin']);
    }
}
