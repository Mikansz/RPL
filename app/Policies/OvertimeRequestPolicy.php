<?php

namespace App\Policies;

use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OvertimeRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any overtime requests.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('overtime.view') || 
               $user->hasAnyRole(['Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can view the overtime request.
     */
    public function view(User $user, OvertimeRequest $overtimeRequest)
    {
        // User can view their own overtime requests
        if ($user->id === $overtimeRequest->user_id) {
            return true;
        }

        // Or if they have permission to view all overtime requests
        return $user->hasPermission('overtime.view') || 
               $user->hasAnyRole(['Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can create overtime requests.
     */
    public function create(User $user)
    {
        return $user->hasPermission('overtime.create') || 
               $user->hasAnyRole(['Admin', 'HR', 'Manager', 'Employee']);
    }

    /**
     * Determine whether the user can update the overtime request.
     */
    public function update(User $user, OvertimeRequest $overtimeRequest)
    {
        // User can only edit their own pending overtime requests
        if ($user->id === $overtimeRequest->user_id && $overtimeRequest->canBeEdited()) {
            return true;
        }

        // Or if they have admin permissions
        return $user->hasPermission('overtime.edit') && 
               $user->hasAnyRole(['Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can delete the overtime request.
     */
    public function delete(User $user, OvertimeRequest $overtimeRequest)
    {
        // User can only delete their own pending overtime requests
        if ($user->id === $overtimeRequest->user_id && $overtimeRequest->canBeEdited()) {
            return true;
        }

        // Or if they have admin permissions
        return $user->hasPermission('overtime.delete') && 
               $user->hasAnyRole(['Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can approve the overtime request.
     */
    public function approve(User $user, OvertimeRequest $overtimeRequest)
    {
        // User cannot approve their own overtime request
        if ($user->id === $overtimeRequest->user_id) {
            return false;
        }

        // Only pending requests can be approved
        if ($overtimeRequest->status !== 'pending') {
            return false;
        }

        // Check if user has permission to approve - prioritize roles for HRD and Admin
        return $user->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) ||
               $user->hasPermission('overtime.approve');
    }

    /**
     * Determine whether the user can reject the overtime request.
     */
    public function reject(User $user, OvertimeRequest $overtimeRequest)
    {
        return $this->approve($user, $overtimeRequest);
    }

    /**
     * Determine whether the user can complete the overtime request.
     */
    public function complete(User $user, OvertimeRequest $overtimeRequest)
    {
        // Only approved requests can be completed
        if (!$overtimeRequest->canBeCompleted()) {
            return false;
        }

        return $user->hasPermission('overtime.approve') || 
               $user->hasAnyRole(['Admin', 'HR', 'Manager']);
    }
}
