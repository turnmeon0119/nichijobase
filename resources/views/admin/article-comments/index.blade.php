<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>記事コメント管理 | 日常BASE</title>
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
            .comment-body {
                max-width: 560px;
                white-space: pre-wrap;
                line-height: 1.8;
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
                        <h1>記事コメント管理</h1>
                        <p>記事に届いたコメントを確認し、不要なものを削除できます。</p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="primary" href="{{ route('admin.articles.index') }}">記事管理へ</a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>記事</th>
                            <th>名前</th>
                            <th>コメント</th>
                            <th>投稿日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comments as $comment)
                            <tr>
                                <td><strong>#{{ $comment->id }}</strong></td>
                                <td>
                                    @if ($comment->article)
                                        {{ $comment->article->title }}
                                        <div class="muted">{{ $comment->article->slug }}</div>
                                    @else
                                        <span class="muted">記事なし</span>
                                    @endif
                                </td>
                                <td>{{ $comment->name ?: '名無し' }}</td>
                                <td><div class="comment-body">{{ $comment->body }}</div></td>
                                <td class="muted">{{ $comment->created_at?->format('Y/m/d H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.article-comments.destroy', $comment) }}" onsubmit="return confirm('この記事コメントを削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">記事コメントはまだありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
