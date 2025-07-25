<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\EmotionState;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class StatusController extends Controller
{
    public function index()
{
    $user = Auth::user();
    $postCount = $user->diaries()->count();

    $statuses = EmotionState::cases();

    $emotionStatuses = collect($statuses)->map(function ($emotion) use ($postCount) {
        return [
            'label' => $emotion->label(),
            'color' => $emotion->color(),
            'unlocked' => $postCount >= $emotion->unlockThreshold(),
            'required' => $emotion->unlockThreshold(),
        ];
    });

    return view('status.index', compact('postCount', 'emotionStatuses'));
}

}
