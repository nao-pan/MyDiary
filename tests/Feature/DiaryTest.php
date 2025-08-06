<?php

namespace Tests\Feature;

use App\Enums\EmotionState;
use Tests\TestCase;
use App\Models\User;
use App\Models\Diary;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiaryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインユーザーが日記を正常に投稿できるか
     */
    public function test_logged_in_user_can_create_diary()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('diary.store'), [
            'title' => '今日の出来事',
            'content' => 'とても良い1日だった。',
            'emotion_state' => EmotionState::HAPPY->value,
            'emotion_score' => '0.2',
            'happiness_score' => '1',
        ]);

        $response->assertRedirect(route('diary.index'));

        $this->assertDatabaseHas('diaries', [
            'title' => '今日の出来事',
            'content' => 'とても良い1日だった。',
            'user_id' => $user->id,
            'happiness_score' => 1,
        ]);

        // 該当の日記を取得
        $diary = Diary::where('title', '今日の出来事')->firstOrFail();

        // 感情ログの保存確認
        $this->assertDatabaseHas('emotion_logs', [
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::HAPPY->value,
            'emotion_score' => 0.2,
        ]);
    }

    /**
     * 未ログインユーザーが投稿不可を確認するテスト
     */
    public function test_guest_cannot_create_diary()
    {
        $response = $this->post(route('diary.store'), [
            'title' => '不正アクセス',
            'content' => 'これは保存されるべきでない。',
            'emotion_state' => EmotionState::SAD->value,
            'emotion_score' => '0.4',
            'happiness_score' => '0',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('diaries', [
            'title' => '不正アクセス',
        ]);
    }

    /**
     * バリデーションエラーが起きる（タイトルが未入力）
     */
    public function test_diary_creation_fails_with_empty_title()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('diary.store'), [
            'title' => '', // バリデーション失敗させたい箇所
            'content' => '内容だけ書いてみた',
            'emotion_state' => EmotionState::HAPPY->value,
            'emotion_score' => '0.5',
            'happiness_score' => '1',
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('diaries', 0);
    }
}
