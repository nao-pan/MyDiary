@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">日記を書く</h1>

        {{-- 投稿成功・失敗メッセージ --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    {{-- バリデーションエラーの表示 --}}
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 日記投稿フォーム --}}
        <form action="{{ route('diary.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">タイトル</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">内容</label>
                <textarea class="form-control" id="content" name="content" rows="5" required>{{ old('content') }}</textarea>
            </div>

            {{-- 今後の追加予定：タグ、感情選択などをUIとして組み込む --}}

            <button type="submit" class="btn btn-primary">投稿する</button>
        </form>
    </div>
@endsection