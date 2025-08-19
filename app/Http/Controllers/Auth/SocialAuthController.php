<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    // Googleへリダイレクト
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    // Googleからのコールバック
    public function callback(): RedirectResponse
    {
        // 通常のWeb（セッションあり）なので stateless() は不要
        // ※API/SPAなら stateless() が有用（Twitter(OAuth1.0)除く）:contentReference[oaicite:2]{index=2}
        $oauth = Socialite::driver('google')->user();

        // 既存の紐付けがあればそのままログイン
        $linked = SocialAccount::where([
            'provider_name' => 'google',
            'provider_id' => $oauth->getId(),
        ])->first();

        if ($linked) {
            Auth::login($linked->user, remember: true);

            return redirect()->intended(route('diary.index'));
        }

        // メールが取れたら既存ユーザーに紐付け、無ければ新規
        $user = $oauth->getEmail()
            ? User::where('email', $oauth->getEmail())->first()
            : null;

        if (! $user) {
            $user = User::create([
                'name' => $oauth->getName() ?: 'User_'.Str::random(6),
                'nickname' => $oauth->getName() ?: 'User_'.Str::random(6),
                'email' => $oauth->getEmail() ?? (Str::uuid().'@example.invalid'),
                'password' => bcrypt(Str::random(32)),
            ]);
        }

        $user->socialAccounts()->create([
            'provider_name' => 'google',
            'provider_id' => $oauth->getId(),
            'email' => $oauth->getEmail(),
            'avatar' => $oauth->getAvatar(),
        ]);

        Auth::login($user, remember: true);

        return redirect()->intended(route('diary.index'));
    }
}
