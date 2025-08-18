<?php

namespace App\Http\Controllers;

use App\Services\EmotionChartService;
use App\Services\EmotionStatusService;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function index(EmotionStatusService $emotionStatusService, EmotionChartService $emotionChartService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $postCount = $user->diaries()->count();

        return view('status.index', [
            'postCount' => $postCount,
            'emotionStatuses' => $emotionStatusService->buildEmotionStatuses($user, $postCount)->sortByDesc('unlocked')->values(),
            'pieChartData' => $emotionChartService->getEmotionPieChartData($user),
            'barChartData' => $emotionChartService->getMonthlyChartData($user),
            'recentEmotionScores' => $emotionStatusService->getRecentEmotionScores($user),
        ]);
    }
}
