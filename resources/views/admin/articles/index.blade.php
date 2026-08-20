<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>記事管理 | 日常BASE</title>
        <style>
            body {
                margin: 0;
                background: #f7f7f8;
                color: #171717;
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
            }
            .shell {
                width: min(1080px, calc(100% - 24px));
                margin: 0 auto;
                padding: 24px 0 48px;
            }
            .panel {
                background: #fff;
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
                padding: 24px;
            }
            .topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
                margin-bottom: 18px;
                flex-wrap: wrap;
            }
            h1 {
                margin: 0;
                font-size: 2rem;
            }
            p {
                color: #52525b;
                line-height: 1.7;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                text-align: left;
                padding: 14px 10px;
                border-bottom: 1px solid #e4e4e7;
                vertical-align: top;
            }
            th {
                color: #52525b;
                font-weight: normal;
            }
            .muted {
                color: #52525b;
                font-size: 0.95rem;
            }
            .badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 999px;
                border: 1px solid #d4d4d8;
                font-size: 0.85rem;
                background: #fff;
            }
            .actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            button, a {
                font: inherit;
            }
            .danger, .primary, .secondary {
                border: 0;
                border-radius: 999px;
                padding: 10px 14px;
                cursor: pointer;
                text-decoration: none;
            }
            .danger {
                background: #8c1d18;
                color: #fff;
            }
            .primary {
                background: #171717;
                color: #fff;
            }
            .secondary {
                background: #fff;
                color: #171717;
                border: 1px solid #d4d4d8;
            }
            .status {
                margin-bottom: 16px;
                padding: 12px 14px;
                border-radius: 16px;
                background: rgba(38, 92, 52, 0.12);
                color: #265c34;
            }
            @media (max-width: 720px) {
                table, thead, tbody, tr, th, td {
                    display: block;
                }
                thead {
                    display: none;
                }
                td {
                    padding: 10px 0;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <div>
                        <h1>{{ $showingTrash ? '記事のゴミ箱' : '記事管理' }}</h1>
                        <p>
                            {{ $showingTrash
                                ? '削除した記事を復元するか、完全に削除できます。'
                                : '記事の作成・編集・公開状態の管理ができます。' }}
                        </p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        @if ($showingTrash)
                            <a class="primary" href="{{ route('admin.articles.index') }}">記事一覧へ戻る</a>
                        @else
                            <a class="primary" href="{{ route('admin.articles.create') }}">新規作成</a>
                            <a class="secondary" href="{{ route('admin.articles.trash') }}">ゴミ箱</a>
                        @endif
                        <a class="secondary" href="{{ route('admin.board.index') }}">掲示板へ</a>
                        <form method="POST" action="{{ route('admin.articles.logout') }}">
                            @csrf
                            <button class="secondary" type="submit">ログアウト</button>
                        </form>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>タイトル</th>
                            <th>slug</th>
                            <th>公開状態</th>
                            <th>連携スレ</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td><strong>#{{ $article->id }}</strong></td>
                                <td>
                                    {{ $article->title }}
                                    <div class="muted">{{ $article->published_at?->format('Y/m/d H:i') ?: '未設定' }}</div>
                                </td>
                                <td><code>{{ $article->slug }}</code></td>
                                <td>
                                    @if ($showingTrash)
                                        <span class="badge">削除済み</span>
                                        <div class="muted">{{ $article->deleted_at?->format('Y/m/d H:i') }}</div>
                                    @elseif (! $article->is_public)
                                        <span class="badge">下書き</span>
                                    @elseif (! $article->published_at)
                                        <span class="badge">公開日時未設定</span>
                                    @elseif ($article->published_at->isFuture())
                                        <span class="badge">公開予約</span>
                                    @else
                                        <span class="badge">公開中</span>
                                    @endif
                                </td>
                                <td>{{ $article->boardThread?->id ? '#'.$article->boardThread->id : 'なし' }}</td>
                                <td>
                                    <div class="actions">
                                        @if ($showingTrash)
                                            <form method="POST" action="{{ route('admin.articles.restore', $article->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="primary" type="submit">復元</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.articles.force-destroy', $article->id) }}" onsubmit="return confirm('この記事を完全に削除します。元に戻せません。よろしいですか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="danger" type="submit">完全削除</button>
                                            </form>
                                        @else
                                            <a class="secondary" href="{{ route('admin.articles.edit', $article) }}">編集</a>
                                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('この記事をゴミ箱へ移動しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="danger" type="submit">ゴミ箱へ</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">記事はまだありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
