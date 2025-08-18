<?php

namespace Tests\Feature;

use App\Enums\EmotionState;
use App\Models\Diary;
use App\Models\EmotionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'emotion_score' => '0.6',
            'happiness_score' => '1',
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('diaries', 0);
    }

    public function test_guest_redirects_to_login_for_diary_index()
    {
        $this->get(route('diary.index'))
            ->assertRedirect(route('login'));
    }

    /**
     * 日記一覧ページの表示が正常に行われるか
     */
    public function test_index_displays_recent_diaries()
    {
        $user = User::factory()->create();
        Diary::factory()->for($user)->count(3)->create();

        $response = $this->actingAs($user, 'web')->get(route('diary.index'));

        $response->assertOk();
        $response->assertViewHas('recentDiaries', function ($diaries) {
            return $diaries->count() === 3;
        });
    }

    /**
     * 日記の詳細ページが正常に表示されることを確認
     */
    public function test_show_displays_diary_details()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->happy()->create(['diary_id' => $diary->id]);

        $response = $this->actingAs($user, 'web')->get(route('diary.show', $diary));

        $response->assertOk();
        $response->assertViewHas('diary', function ($viewDiary) use ($diary) {
            return $viewDiary->id === $diary->id;
        });
    }

    /**
     * 日記の詳細ページが他人からは見れないことを確認する
     */
    public function test_user_cannot_view_others_diary()
    {
        [$owner, $other] = [User::factory()->create(), User::factory()->create()];
        $diary = Diary::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('diary.show', $diary))
            ->assertForbidden();
    }

    /**
     * 日記投稿ページを表示できることを確認する
     */
    public function test_create_page_displays_available_emotions()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get(route('diary.create'));

        $response->assertOk();
        $response->assertViewHas('emotionStates', function ($emotions) {
            return count($emotions) > 0; // 初期アンロックされた感情があることを確認
        });
    }

    /**
     * 認証ミドルウェアの適用の確認
     */
    public function test_guest_is_redirected_by_auth_middleware()
    {
        $this->get(route('diary.index'))->assertRedirect(route('login'));
        $this->get(route('diary.create'))->assertRedirect(route('login'));
        $this->post(route('diary.store'), [])->assertRedirect(route('login'));
    }
}
