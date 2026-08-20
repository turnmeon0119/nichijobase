<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>管理トップ | 日常BASE</title>
        <style>
            body {
                margin: 0;
                background:
                    radial-gradient(circle at 12% 10%, rgba(255, 248, 232, 0.9), transparent 28%),
                    linear-gradient(135deg, #fbfaf7 0%, #f2f2f0 100%);
                color: #171717;
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
            }
            .shell {
                width: min(1120px, calc(100% - 24px));
                margin: 0 auto;
                padding: 32px 0 56px;
            }
            .hero {
                background: #fff;
                border: 1px solid #e4e4e7;
                border-radius: 28px;
                box-shadow: 0 18px 50px rgba(24, 24, 27, 0.08);
            }
            .hero {
                padding: clamp(24px, 4vw, 44px);
            }
            h1 {
                margin: 0 0 12px;
                font-size: clamp(2.4rem, 6vw, 4.6rem);
                letter-spacing: -0.07em;
                line-height: 1;
            }
            p {
                margin: 0;
                color: #52525b;
                line-height: 1.8;
            }
            .actions {
                margin-top: 30px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
            }
            a, button {
                font: inherit;
            }
            .link {
                display: grid;
                min-height: 142px;
                align-content: space-between;
                padding: 22px;
                border-radius: 22px;
                text-decoration: none;
                border: 1px solid #d8d4cc;
                background: #fff;
                color: #171717;
                transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
            }
            .link:hover {
                transform: translateY(-3px);
                border-color: #171717;
                box-shadow: 0 14px 34px rgba(24, 24, 27, 0.1);
            }
            .link strong {
                display: block;
                font-size: 1.25rem;
            }
            .link span {
                display: block;
                margin-top: 12px;
                color: #71717a;
                font-size: 0.92rem;
                line-height: 1.7;
            }
            .logout-wrap {
                margin-top: 22px;
            }
            .logout {
                display: inline-block;
                padding: 10px 16px;
                border-radius: 999px;
                border: 1px solid #d4d4d8;
                background: transparent;
                color: #171717;
                cursor: pointer;
            }
            @media (max-width: 720px) {
                .actions {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="hero">
                <h1>管理トップ</h1>
                <p>公開コンテンツを整理するための管理入口です。必要な操作だけをここから開けます。</p>
                <div class="actions">
                    <a class="link" href="{{ route('admin.articles.index') }}">
                        <strong>記事管理</strong>
                        <span>記事の作成・編集・削除、公開状態を確認します。</span>
                    </a>
                    <a class="link" href="{{ route('admin.news.index') }}">
                        <strong>News管理</strong>
                        <span>お知らせ・更新情報・イベント告知を作成します。</span>
                    </a>
                    <a class="link" href="{{ route('admin.board.index') }}">
                        <strong>掲示板管理</strong>
                        <span>スレッドや返信を確認し、必要に応じて削除します。</span>
                    </a>
                    <a class="link" href="{{ route('admin.ogiri.index') }}">
                        <strong>大喜利管理</strong>
                        <span>画像つきのお題を作成し、公開側へ反映します。</span>
                    </a>
                </div>
                <div class="logout-wrap">
                    <form method="POST" action="{{ route('admin.articles.logout') }}">
                        @csrf
                        <button class="logout" type="submit">ログアウト</button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
