@extends('board.layout', ['title' => '掲示板一覧'])

@section('content')
    <div class="grid two">
        <section class="panel">
            <h2 class="section-title">スレッド一覧</h2>
            <p class="meta" style="margin-top: -6px; margin-bottom: 18px;">
                Twitter 風に新着を流し見したい場合は <a href="{{ route('board.timeline') }}">タイムライン</a> を使えます。
            </p>

            @if ($threads->isEmpty())
                <p class="empty">まだスレッドがありません。右側のフォームから最初の投稿を作れます。</p>
            @else
                <div class="thread-list">
                    @foreach ($threads as $thread)
                        <article class="thread-card">
                            <div class="eyebrow">
                                {{ $thread->article?->title ? '記事連携: '.$thread->article->title : '雑談スレッド' }}
                            </div>
                            <h3 class="card-title">
                                <a href="{{ route('board.show', $thread) }}">{{ $thread->title }}</a>
                            </h3>
                            <p class="card-body">{{ $thread->body }}</p>
                            <div class="badge-row">
                                <span class="badge">返信 {{ $thread->posts_count }} 件</span>
                                <span class="badge">作成 {{ $thread->created_at?->format('Y-m-d H:i') }}</span>
                                @if ($thread->latest_post_at)
                                    <span class="badge">最終返信 {{ \Illuminate\Support\Carbon::parse($thread->latest_post_at)->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="panel">
            <h2 class="section-title">新規スレッド作成</h2>

            <form method="POST" action="{{ route('board.store') }}">
                @csrf

                <label>
                    記事に紐づける
                    <select name="article_id">
                        <option value="">紐づけない</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}" @selected(old('article_id') == $article->id)>
                                {{ $article->title }} ({{ $article->slug }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    タイトル
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </label>

                <label>
                    名前
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="未入力なら名無し">
                </label>

                <label>
                    本文
                    <textarea name="body" required>{{ old('body') }}</textarea>
                </label>

                <button type="submit">スレッドを作成</button>
            </form>
        </aside>
    </div>
@endsection
