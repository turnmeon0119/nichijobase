<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>管理トップ | 日常BASE</title>
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
            .hero, .card {
                background: #fff;
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }
            .hero {
                padding: 28px;
                margin-bottom: 20px;
            }
            h1 {
                margin: 0 0 8px;
                font-size: 2.2rem;
            }
            p {
                margin: 0;
                color: #52525b;
                line-height: 1.8;
            }
            .grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px;
                margin-top: 18px;
            }
            .card {
                padding: 22px;
            }
            .card h2 {
                margin: 0 0 10px;
                font-size: 1.4rem;
            }
            .actions {
                margin-top: 18px;
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            a, button {
                font: inherit;
            }
            .link, .logout {
                display: inline-block;
                padding: 10px 14px;
                border-radius: 999px;
                text-decoration: none;
                border: 1px solid #d4d4d8;
                background: #fff;
                color: #171717;
            }
            .logout {
                cursor: pointer;
            }
            .link {
                background: #171717;
                border-color: #171717;
                color: #fff;
            }
            @media (max-width: 720px) {
                .grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="hero">
                <h1>管理トップ</h1>
                <p>記事管理と掲示板管理の入口です。削除や確認をするときは、ここから使い分けます。</p>
                <div class="actions">
                    <a class="link" href="{{ route('admin.articles.index') }}">記事を管理する</a>
                    <a class="link" href="{{ route('admin.board.index') }}">掲示板を管理モードで開く</a>
                    <form method="POST" action="{{ route('admin.articles.logout') }}">
                        @csrf
                        <button class="logout" type="submit">ログアウト</button>
                    </form>
                </div>
            </section>

            <section class="grid">
                <article class="card">
                    <h2>記事管理</h2>
                    <p>記事ID、タイトル、slug を見ながら削除できます。記事単位の整理はこちらです。</p>
                </article>
                <article class="card">
                    <h2>掲示板管理</h2>
                    <p>掲示板一覧を開くと、管理モード中だけスレッドIDと削除ボタンが表示されます。</p>
                </article>
            </section>
        </main>
    </body>
</html>
