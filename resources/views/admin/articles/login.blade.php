<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>管理者ログイン | 日常BASE</title>
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: #f7f7f8;
                color: #171717;
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
            }
            .card {
                width: min(480px, calc(100% - 24px));
                padding: 28px;
                border-radius: 16px;
                background: #fff;
                border: 1px solid #e4e4e7;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }
            h1 {
                margin: 0 0 10px;
                font-size: 2rem;
            }
            p {
                margin: 0 0 18px;
                color: #52525b;
                line-height: 1.8;
            }
            label {
                display: grid;
                gap: 8px;
            }
            input, button {
                font: inherit;
            }
            input {
                width: 100%;
                box-sizing: border-box;
                padding: 12px 14px;
                border-radius: 14px;
                border: 1px solid #d4d4d8;
            }
            button {
                margin-top: 16px;
                border: 0;
                border-radius: 999px;
                padding: 12px 18px;
                background: #171717;
                color: #fff;
                cursor: pointer;
            }
            .error {
                margin-top: 10px;
                color: #8c1d18;
            }
        </style>
    </head>
    <body>
        <main class="card">
            <h1>記事管理</h1>
            <p>管理者トークンを入れると、記事一覧と削除画面を開けます。</p>
            <form method="POST" action="{{ route('admin.articles.authenticate') }}">
                @csrf
                <label>
                    管理者トークン
                    <input type="password" name="token" value="{{ old('token') }}" required>
                </label>
                @error('token')
                    <div class="error">{{ $message }}</div>
                @enderror
                <button type="submit">入る</button>
            </form>
        </main>
    </body>
</html>
