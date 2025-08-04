@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-6">✍️ 新しい日記を書く</h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('diary.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 font-medium mb-1">タイトル</label>
                    <input type="text" id="title" name="title"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('title') }}" required>
                </div>

                <div class="mb-4">
                    <label for="content" class="block text-gray-700 font-medium mb-1">本文</label>
                    <textarea id="content" name="content" rows="6"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" required>{{ old('content') }}</textarea>
                </div>

                {{-- 感情選択（ロック済みのみ表示） --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">感情を選ぶ</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($emotionStates as $emotion)
                            <label class="cursor-pointer">
                                <input type="radio" name="emotion_state" value="{{ $emotion->value }}" class="peer hidden"
                                    {{ old('emotion_state') === $emotion->value ? 'checked' : '' }}>
                                <div class="peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-indigo-500 rounded px-3 py-1 text-sm"
                                    style="background-color: {{ $emotion->defaultColor() }}; color: {{ $emotion->textColor() }}">
                                    {{ $emotion->label() }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="emotion_score" class="block font-semibold mb-2">感情の強さ</label>

                    {{-- スライダー入力 --}}
                    <input type="range" name="emotion_score" id="emotion_score" min="0.2" max="1.0"
                        step="0.2" value="0.6" class="w-full" oninput="updateEmotionLabel(this.value)">

                    {{-- 強度ラベル表示 --}}
                    <div class="flex justify-between text-sm text-gray-500 mt-2">
                        <span id="label-0.2">とても弱い</span>
                        <span id="label-0.4">やや弱い</span>
                        <span id="label-0.6">普通</span>
                        <span id="label-0.8">やや強い</span>
                        <span id="label-1.0">とても強い</span>
                    </div>
                    <label for="happiness_score">今日の幸福度（1〜10）</label>
                    <input type="range" name="happiness_score" id="happiness_score" min="1" max="10"
                        value="5" class="w-full mb-4">


                    <div class="mt-6 text-right">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">保存する</button>
                    </div>
            </form>
        </div>
    </div>
@endsection
