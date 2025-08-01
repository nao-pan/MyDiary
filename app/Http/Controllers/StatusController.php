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
        $barChartData = $emotionStatusService->getMonthlyChartData($user);
        $pieChartData = $emotionStatusService->getBaseEmotionChartData($user);

        return view('status.index', [
            'pieChartData' => $pieChartData,
            'postCount' => $postCount,
            'emotionStatuses' => $emotionStatusService->buildEmotionStatuses($user, $postCount)->sortByDesc('unlocked')->values(),
            'barChartData' => $barChartData,
            'recentEmotionScores' => $emotionStatusService->getRecentEmotionScores($user),
        ]);
    }
}
