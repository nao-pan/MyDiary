<?php

namespace App\Policies;

use App\Models\Diary;
use App\Models\User;

class DiaryPolicy
{
    /**
     * 一覧表示：所有者のみ閲覧可能
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 個別閲覧：所有者のみ閲覧可能
     */
    public function view(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * 作成：ログイン済みユーザはOK
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 更新：所有者のみ更新可能（更新は未実装）
     */
    public function update(User $user, Diary $diary): bool
    {
        return $user->id === $diary->user_id;
    }

    /**
     * 削除：所有者のみ削除可能（削除は未実装）
     */
    // public function delete(User $user, Diary $diary): bool
    // {
    //     return $user->id === $diary->user_id;
    // }
}
