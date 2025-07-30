<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\EmotionStatusService;

class StatusController extends Controller
{
    public function index(EmotionStatusService $emotionStatusService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $postCount = $user->diaries()->count();

        return view('status.index', [
            'baseEmotionChartData' => $emotionStatusService->getBaseEmotionChartData($user),
            'postCount' => $postCount,
            'emotionStatuses' => $emotionStatusService->buildEmotionStatuses($user, $postCount)->sortByDesc('unlocked')->values(),
            'monthlyLabels' => $emotionStatusService->getMonthlyLabels($user),
            'monthlyData' => $emotionStatusService->getMonthlyData($user),
            'recentEmotionScores' => $emotionStatusService->getRecentEmotionScores($user),
        ]);
    }
}
