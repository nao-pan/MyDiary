<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;
use App\Services\EmotionStatusService;


class StatusController extends Controller
{
    public function index(EmotionStatusService $emotionStatusService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $postCount = $user->diaries()->count();
        $labels = $emotionStatusService->generateMonthLabels(6);
        $chartData = $emotionStatusService->getMonthlyChartData($user);

        return view('status.index', [
            'baseEmotionChartData' => $emotionStatusService->getBaseEmotionChartData($user),
            'postCount' => $postCount,
            'emotionStatuses' => $emotionStatusService->buildEmotionStatuses($user, $postCount)->sortByDesc('unlocked')->values(),
            'chartData' => $chartData,
            'recentEmotionScores' => $emotionStatusService->getRecentEmotionScores($user),
        ]);
    }
}
