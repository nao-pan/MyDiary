<?php

namespace App\Policies;

use App\Models\Diary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DiaryPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }
}
