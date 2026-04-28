<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>記事管理 | 日常BASE</title>
        <style>
            body {
                margin: 0;
                background: #f5ede1;
                color: #24170c;
                font-family: Georgia, "Hiragino Mincho ProN", "Yu Mincho", serif;
            }
            .shell {
                width: min(1080px, calc(100% - 24px));
                margin: 0 auto;
                padding: 24px 0 48px;
            }
            .panel {
                background: rgba(255, 250, 244, 0.92);
                border: 1px solid rgba(111, 74, 42, 0.18);
                border-radius: 24px;
                box-shadow: 0 24px 60px rgba(82, 48, 18, 0.14);
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
                color: #6f5b49;
                line-height: 1.7;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                text-align: left;
                padding: 14px 10px;
                border-bottom: 1px solid rgba(111, 74, 42, 0.12);
                vertical-align: top;
            }
            th {
                color: #6f5b49;
                font-weight: normal;
            }
            .muted {
                color: #6f5b49;
                font-size: 0.95rem;
            }
            .badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 999px;
                border: 1px solid #d7c2a4;
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
            .danger, .secondary {
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
            .secondary {
                background: #fff;
                color: #24170c;
                border: 1px solid #d7c2a4;
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
                        <h1>記事管理</h1>
                        <p>ID と slug を見ながら、管理者だけが削除できます。</p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('board.index') }}">掲示板へ</a>
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
                                    <span class="badge">{{ $article->is_public ? '公開' : '非公開' }}</span>
                                </td>
                                <td>{{ $article->boardThread?->id ? '#'.$article->boardThread->id : 'なし' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('この記事を削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">IDで削除</button>
                                    </form>
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
