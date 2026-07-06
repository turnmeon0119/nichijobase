@extends('board.layout', ['title' => $thread->title])

@section('content')
    <a class="back-link" href="{{ route('admin.board.index') }}">← 掲示板一覧へ戻る</a>

    <div class="grid two">
        <section class="panel">
            <article class="thread-card" id="thread-{{ $thread->id }}">
                <div class="eyebrow">
                    {{ $thread->article?->title ? '記事連携: '.$thread->article->title.' / '.$thread->article->slug : '雑談スレッド' }}
                </div>
                @if ($isAdmin)
                    <div class="meta" style="margin-top: 8px;">管理用ID: #{{ $thread->id }}</div>
                @endif
                <h2 class="card-title">{{ $thread->title }}</h2>
                <p class="meta">
                    {{ $thread->name ?: '名無し' }} / {{ $thread->created_at?->diffForHumans() }} ({{ $thread->created_at?->format('Y/m/d H:i') }})
                </p>
                <p class="card-body">{{ $thread->body }}</p>
                @if ($isAdmin)
                    <form method="POST" action="{{ route('admin.board.destroy', $thread) }}" onsubmit="return confirm('このスレッドを削除しますか？');" style="margin-top: 14px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:#8c1d18;">このスレを削除</button>
                    </form>
                @endif
            </article>

            <div style="height: 18px;"></div>

            <h3 class="section-title">返信</h3>

            @if ($thread->posts->isEmpty())
                <p class="empty">まだ返信はありません。</p>
            @else
                <div class="post-list">
                    @foreach ($thread->posts as $post)
                        <article class="post-card" id="post-{{ $post->id }}">
                            <p class="meta">#{{ $post->id }} {{ $post->name ?: '名無し' }} / {{ $post->created_at?->diffForHumans() }} ({{ $post->created_at?->format('Y/m/d H:i') }})</p>
                            <p class="card-body">{{ $post->body }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="panel">
            <h3 class="section-title">返信する</h3>

            <form method="POST" action="{{ route('admin.board.posts.store', $thread) }}">
                @csrf

                <label>
                    名前
                    <input type="text" name="name" value="{{ old('name', $rememberedName) }}" placeholder="未入力なら名無し">
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
