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
                background: #f5ede1;
                color: #24170c;
                font-family: Georgia, "Hiragino Mincho ProN", "Yu Mincho", serif;
            }
            .card {
                width: min(480px, calc(100% - 24px));
                padding: 28px;
                border-radius: 24px;
                background: rgba(255, 250, 244, 0.92);
                border: 1px solid rgba(111, 74, 42, 0.18);
                box-shadow: 0 24px 60px rgba(82, 48, 18, 0.14);
            }
            h1 {
                margin: 0 0 10px;
                font-size: 2rem;
            }
            p {
                margin: 0 0 18px;
                color: #6f5b49;
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
                border: 1px solid #d7c2a4;
            }
            button {
                margin-top: 16px;
                border: 0;
                border-radius: 999px;
                padding: 12px 18px;
                background: linear-gradient(135deg, #b04a1f, #6b2407);
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
