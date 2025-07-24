@extends('layouts.test')

@section('title', '投稿一覧')

@section('content')
    <h1 class="text-center">投稿一覧</h1>

      @foreach($posts as $post)
                <div>
                    <h2>{{ $post->title }}</h2>
                    <p>{{ $post->content }}</p>
                </div>
            @endforeach

    {{-- <a href="{{ route('posts.create') }}">新規投稿</a> --}}
  

  
@endsection