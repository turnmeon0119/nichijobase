<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>News管理 | 日常BASE</title>
        <style>
            body { margin: 0; background: #f7f7f8; color: #171717; font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif; }
            .shell { width: min(1080px, calc(100% - 24px)); margin: 0 auto; padding: 24px 0 48px; }
            .panel { background: #fff; border: 1px solid #e4e4e7; border-radius: 16px; box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06); padding: 24px; }
            .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
            h1 { margin: 0; font-size: 2rem; }
            p { color: #52525b; line-height: 1.7; }
            table { width: 100%; border-collapse: collapse; }
            th, td { text-align: left; padding: 14px 10px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
            th { color: #52525b; font-weight: normal; }
            code { font-size: 0.9rem; }
            .muted { color: #52525b; font-size: 0.95rem; }
            .badge { display: inline-block; padding: 5px 10px; border-radius: 999px; border: 1px solid #d4d4d8; font-size: 0.85rem; background: #fff; }
            .actions { display: flex; gap: 10px; flex-wrap: wrap; }
            .filters { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 18px; }
            .filter-link { display: inline-flex; align-items: center; border: 1px solid #d4d4d8; border-radius: 999px; color: #171717; padding: 8px 12px; text-decoration: none; }
            .filter-link.active { background: #171717; border-color: #171717; color: #fff; }
            button, a { font: inherit; }
            .danger, .primary, .secondary { border: 0; border-radius: 999px; padding: 10px 14px; cursor: pointer; text-decoration: none; }
            .danger { background: #8c1d18; color: #fff; }
            .primary { background: #171717; color: #fff; }
            .secondary { background: #fff; color: #171717; border: 1px solid #d4d4d8; }
            .status { margin-bottom: 16px; padding: 12px 14px; border-radius: 16px; background: rgba(38, 92, 52, 0.12); color: #265c34; }
            @media (max-width: 720px) { table, thead, tbody, tr, th, td { display: block; } thead { display: none; } td { padding: 10px 0; } }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <div>
                        <h1>News管理</h1>
                        <p>お知らせ・更新情報・イベント告知を管理できます。</p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="primary" href="{{ route('admin.news.create') }}">新規作成</a>
                        <form method="POST" action="{{ route('admin.articles.logout') }}">
                            @csrf
                            <button class="secondary" type="submit">ログアウト</button>
                        </form>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @php
                    $filters = [
                        'all' => 'すべて',
                        'published' => '公開中',
                        'draft' => '下書き',
                        'scheduled' => '予約投稿',
                    ];
                @endphp
                <nav class="filters" aria-label="Newsの公開状態で絞り込み">
                    @foreach ($filters as $value => $label)
                        <a
                            class="filter-link {{ ($statusFilter ?? 'all') === $value ? 'active' : '' }}"
                            href="{{ $value === 'all' ? route('admin.news.index') : route('admin.news.index', ['status' => $value]) }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>タイトル</th>
                            <th>slug</th>
                            <th>公開状態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td><strong>#{{ $item->id }}</strong></td>
                                <td>
                                    {{ $item->title }}
                                    <div class="muted">{{ $item->published_at?->format('Y/m/d H:i') ?: '未設定' }}</div>
                                </td>
                                <td><code>{{ $item->slug }}</code></td>
                                <td>
                                    @if (! $item->is_public)
                                        <span class="badge">下書き</span>
                                    @elseif (! $item->published_at)
                                        <span class="badge">公開日時未設定</span>
                                    @elseif ($item->published_at->isFuture())
                                        <span class="badge">公開予約</span>
                                    @else
                                        <span class="badge">公開中</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="secondary" href="{{ route('admin.news.edit', $item) }}">編集</a>
                                        <a class="secondary" href="{{ config('app.front_url', 'http://localhost:3000').'/news/'.$item->slug }}" target="_blank" rel="noreferrer">表示確認</a>
                                        <form method="POST" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('このNewsを削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="danger" type="submit">削除</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Newsはまだありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
