{{-- welcome.blade.php --}}
@extends('layouts.guest')

@section('content')
<div class="text-center py-16">
    <h1 class="text-4xl font-bold mb-4">ようこそ、Sincerely日記へ 🌸</h1>
    <p class="text-lg text-gray-600 mb-8">
        感情と向き合い、自分を育てる習慣を。
         <a href="{{ route('about') }}" class="px-6 py-3 text-white bg-gray-500 rounded">もっと詳しく</a>
    </p>
    <a href="{{ route('login') }}" class="px-6 py-3 bg-pink-400 text-white rounded">ログイン</a>
    <a href="{{ route('register') }}" class="px-6 py-3 bg-pink-400 text-white rounded">新規登録</a>
</div>
@endsection