@extends('layouts.guest')

@section('content')
    <div class="max-w-3xl mx-auto py-12 px-6">
        <h1 class="text-4xl font-bold text-pink-600 mb-6 text-center">
            Sincerely日記について
        </h1>

        <p class="text-lg text-gray-700 leading-relaxed mb-6">
            <strong>Sincerely日記</strong>は、自分の感情と誠実に向き合いながら日々を記録し、内省やEQ（感情知能）を高めることを目的としたアプリです。
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mb-3">🌱 どんな人に向いている？</h2>
        <ul class="list-disc list-inside text-gray-700 mb-6">
            <li>感情を整理したい</li>
            <li>自分の成長を記録したい</li>
            <li>心の変化に気づきたい</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-800 mb-3">🧠 特徴</h2>
        <ul class="list-disc list-inside text-gray-700 mb-6">
            <li>日記を書くたびに「感情」を記録</li>
            <li>感情ごとに色を設定できるカスタマイズ性</li>
            <li>投稿数に応じて解禁される「アドバンス感情」</li>
            <li>感情の記録を可視化する「ステータス画面」</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-800 mb-3">📲 今後の展望</h2>
        <p class="text-gray-700 mb-6">
            将来的には、AIによる感情フィードバック機能やiPhoneアプリ展開も予定しています。
        </p>

        <div class="text-center mt-10">
            <a href="{{ route('login') }}" class="inline-block bg-pink-500 text-white font-semibold px-6 py-3 rounded shadow hover:bg-pink-600 transition">
                ログインして始める
            </a>
        </div>
    </div>
@endsection
