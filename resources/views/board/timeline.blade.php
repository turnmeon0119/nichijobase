@extends('board.layout', ['title' => 'タイムライン'])

@section('content')
    <section class="panel">
        <h2 class="section-title">タイムライン</h2>

        @if ($items->isEmpty())
            <p class="empty">まだ投稿がありません。まずは <a href="{{ route('board.index') }}">掲示板一覧</a> からスレッドを作ってください。</p>
        @else
            <div class="post-list">
                @foreach ($items as $item)
                    <article class="post-card">
                        <p class="meta">
                            {{ $item['kind'] === 'thread' ? '新規スレッド' : '返信' }}
                            / {{ $item['name'] ?: '名無し' }}
                            / {{ $item['created_label'] }} ({{ $item['created_exact'] }})
                        </p>

                        <h3 class="card-title">
                            <a href="{{ route('board.show', $item['thread_id']).($item['kind'] === 'post' ? '#post-'.$item['id'] : '#thread-'.$item['thread_id']) }}">
                                {{ $item['title'] ?: 'スレッド' }}
                            </a>
                        </h3>

                        <p class="card-body">{{ $item['body'] }}</p>

                        <div class="badge-row">
                            <span class="badge">{{ $item['kind'] === 'thread' ? 'Thread' : 'Reply' }}</span>
                            @if ($item['article'])
                                <span class="badge">記事: {{ $item['article']->title }}</span>
                            @endif
                            @if ($item['kind'] === 'thread')
                                <span class="badge">返信 {{ $item['posts_count'] }} 件</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
