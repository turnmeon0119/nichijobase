<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>大喜利お題作成 | 日常BASE</title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                background: #f7f7f8;
                color: #171717;
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
            }
            .shell {
                width: min(880px, calc(100% - 24px));
                margin: 0 auto;
                padding: 24px 0 48px;
            }
            .panel {
                padding: 24px;
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }
            .topbar, .actions, .grid {
                display: flex;
                gap: 14px;
                flex-wrap: wrap;
            }
            .topbar {
                align-items: center;
                justify-content: space-between;
                margin-bottom: 24px;
            }
            .grid > label { flex: 1 1 260px; }
            h1 { margin: 0; font-size: 2rem; }
            p { margin: 0; color: #52525b; line-height: 1.8; }
            label {
                display: block;
                margin-bottom: 18px;
                font-weight: bold;
            }
            input, textarea, button, a { font: inherit; }
            input[type="text"], input[type="datetime-local"], input[type="file"], textarea {
                display: block;
                width: 100%;
                margin-top: 8px;
                padding: 12px 14px;
                border: 1px solid #d4d4d8;
                border-radius: 12px;
                background: #fff;
                color: #171717;
            }
            textarea {
                min-height: 150px;
                resize: vertical;
                line-height: 1.7;
            }
            .checkbox {
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: normal;
            }
            .primary, .secondary {
                display: inline-block;
                padding: 11px 17px;
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
            .errors {
                margin-bottom: 18px;
                padding: 13px 15px;
                border-radius: 14px;
                background: rgba(140, 29, 24, 0.1);
                color: #8c1d18;
            }
            .errors ul {
                margin: 0;
                padding-left: 20px;
            }
            .hint {
                display: block;
                margin-top: 6px;
                color: #52525b;
                font-size: 0.85rem;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <div>
                        <h1>大喜利お題作成</h1>
                        <p>画像を添えると、公開側でお題画像として表示されます。</p>
                    </div>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="secondary" href="{{ route('admin.ogiri.index') }}">大喜利管理へ戻る</a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.ogiri.store') }}" enctype="multipart/form-data">
                    @csrf

                    <label>
                        お題タイトル
                        <input type="text" name="title" value="{{ old('title') }}" maxlength="160" required>
                        <span class="hint">例: この画像に一言</span>
                    </label>

                    <label>
                        お題画像
                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
                        <span class="hint">jpeg / png / webp、5MBまで。未設定でも作成できます。</span>
                    </label>

                    <label>
                        補足本文
                        <textarea name="body">{{ old('body') }}</textarea>
                        <span class="hint">回答ルールや補足があれば入力します。</span>
                    </label>

                    <div class="grid">
                        <label>
                            公開日時
                            <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                            <span class="hint">未来日時を指定すると公開予約になります。</span>
                        </label>
                    </div>

                    <input type="hidden" name="is_public" value="0">
                    <label class="checkbox">
                        <input type="checkbox" name="is_public" value="1" @checked((bool) old('is_public', true))>
                        公開対象にする
                    </label>

                    <div class="actions">
                        <button class="primary" type="submit">お題を作成する</button>
                        <a class="secondary" href="{{ route('admin.ogiri.index') }}">キャンセル</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
