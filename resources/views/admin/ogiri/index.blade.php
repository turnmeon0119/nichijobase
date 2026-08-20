<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>大喜利管理 | 日常BASE</title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                background: #f7f7f8;
                color: #171717;
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
            }
            .shell {
                width: min(1040px, calc(100% - 24px));
                margin: 0 auto;
                padding: 24px 0 48px;
            }
            .panel, .card {
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }
            .panel { padding: 24px; }
            .topbar, .actions, .row {
                display: flex;
                gap: 14px;
                flex-wrap: wrap;
            }
            .topbar {
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            h1, h2, p { margin: 0; }
            h1 { font-size: 2rem; }
            p { color: #52525b; line-height: 1.8; }
            a, button { font: inherit; }
            .primary, .secondary, .danger {
                display: inline-block;
                padding: 10px 14px;
                border-radius: 999px;
                cursor: pointer;
                text-decoration: none;
            }
            .primary {
                border: 0;
                background: #171717;
                color: #fff;
            }
            .secondary {
                border: 1px solid #d4d4d8;
                background: #fff;
                color: #171717;
            }
            .danger {
                border: 1px solid #f59e0b;
                background: #fff;
                color: #d97706;
            }
            .status {
                margin-bottom: 18px;
                padding: 13px 15px;
                border-radius: 14px;
                background: rgba(38, 92, 52, 0.12);
                color: #265c34;
            }
            .list {
                display: grid;
                gap: 14px;
                margin-top: 20px;
            }
            .card {
                display: grid;
                grid-template-columns: 140px 1fr;
                gap: 16px;
                padding: 16px;
            }
            .thumb {
                width: 100%;
                aspect-ratio: 4 / 3;
                border-radius: 12px;
                background: #f4f4f5;
                object-fit: cover;
            }
            .meta {
                margin-top: 8px;
                color: #71717a;
                font-size: 0.9rem;
            }
            .empty {
                margin-top: 20px;
                padding: 24px;
                border: 1px dashed #d4d4d8;
                border-radius: 16px;
                text-align: center;
            }
            @media (max-width: 720px) {
                .card { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <div>
                        <h1>大喜利管理</h1>
                        <p>画像つきのお題を作成できます。回答は公開側の大喜利ページに集まります。</p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="primary" href="{{ route('admin.ogiri.create') }}">新規お題</a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($prompts->isEmpty())
                    <p class="empty">まだ大喜利のお題がありません。</p>
                @else
                    <div class="list">
                        @foreach ($prompts as $prompt)
                            <article class="card">
                                @if ($prompt->image_url)
                                    <img class="thumb" src="{{ $prompt->image_url }}" alt="">
                                @else
                                    <div class="thumb"></div>
                                @endif
                                <div>
                                    <h2>#{{ $prompt->id }} {{ $prompt->title }}</h2>
                                    <p class="meta">
                                        {{ $prompt->is_public ? '公開' : '非公開' }}
                                        / {{ $prompt->published_at?->format('Y/m/d H:i') ?? '公開日時なし' }}
                                        / 回答 {{ $prompt->answers_count }}
                                    </p>
                                    @if ($prompt->body)
                                        <p class="meta">{{ \Illuminate\Support\Str::limit($prompt->body, 120) }}</p>
                                    @endif
                                    <div class="actions" style="margin-top: 12px;">
                                        <a class="secondary" href="{{ config('app.front_url', 'http://localhost:3000').'/ogiri/'.$prompt->id }}" target="_blank" rel="noreferrer">表示確認</a>
                                        <form method="POST" action="{{ route('admin.ogiri.destroy', $prompt) }}" onsubmit="return confirm('このお題を削除しますか？回答も削除されます。');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="danger" type="submit">削除</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </body>
</html>
