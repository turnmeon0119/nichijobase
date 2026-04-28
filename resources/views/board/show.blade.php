@extends('board.layout', ['title' => $thread->title])

@section('content')
    <a class="back-link" href="{{ route('board.index') }}">← 掲示板一覧へ戻る</a>

    <div class="grid two">
        <section class="panel">
            <article class="thread-card">
                <div class="eyebrow">
                    {{ $thread->article?->title ? '記事連携: '.$thread->article->title.' / '.$thread->article->slug : '雑談スレッド' }}
                </div>
                <h2 class="card-title">{{ $thread->title }}</h2>
                <p class="meta">
                    {{ $thread->name ?: '名無し' }} / {{ $thread->created_at?->format('Y-m-d H:i') }}
                </p>
                <p class="card-body">{{ $thread->body }}</p>
            </article>

            <div style="height: 18px;"></div>

            <h3 class="section-title">返信</h3>

            @if ($thread->posts->isEmpty())
                <p class="empty">まだ返信はありません。</p>
            @else
                <div class="post-list">
                    @foreach ($thread->posts as $post)
                        <article class="post-card">
                            <p class="meta">#{{ $post->id }} {{ $post->name ?: '名無し' }} / {{ $post->created_at?->format('Y-m-d H:i') }}</p>
                            <p class="card-body">{{ $post->body }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="panel">
            <h3 class="section-title">返信する</h3>

            <form method="POST" action="{{ route('board.posts.store', $thread) }}">
                @csrf

                <label>
                    名前
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="未入力なら名無し">
                </label>

                <label>
                    本文
                    <textarea name="body" required>{{ old('body') }}</textarea>
                </label>

                <button type="submit">返信を投稿</button>
            </form>
        </aside>
    </div>
@endsection
