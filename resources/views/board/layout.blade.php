<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? '掲示板' }} | 日常BASE</title>
        <style>
            :root {
                --bg: #f7f7f8;
                --surface: #fff;
                --surface-strong: #fff;
                --line: #d4d4d8;
                --ink: #171717;
                --muted: #52525b;
                --brand: #171717;
                --brand-dark: #27272a;
                --success: #265c34;
                --shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                color: var(--ink);
                font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif;
                background: var(--bg);
                min-height: 100vh;
            }

            a {
                color: inherit;
            }

            .shell {
                width: min(1100px, calc(100% - 32px));
                margin: 0 auto;
                padding: 32px 0 56px;
            }

            .hero,
            .panel,
            .thread-card,
            .post-card {
                background: var(--surface);
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                box-shadow: var(--shadow);
            }

            .hero {
                padding: 28px;
                margin-bottom: 24px;
                position: relative;
                overflow: hidden;
            }

            .hero::after {
                content: none;
            }

            .hero h1 {
                margin: 0;
                font-size: clamp(2rem, 4vw, 3.6rem);
                line-height: 1;
                letter-spacing: 0.04em;
            }

            .hero p {
                max-width: 56rem;
                margin: 12px 0 0;
                color: var(--muted);
                font-size: 1rem;
                line-height: 1.8;
            }

            .hero-nav {
                margin-top: 18px;
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .hero-nav a {
                text-decoration: none;
                background: var(--surface-strong);
                border: 1px solid var(--line);
                border-radius: 999px;
                padding: 10px 16px;
            }

            .grid {
                display: grid;
                gap: 24px;
            }

            .grid.two {
                grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
                align-items: start;
            }

            .panel {
                padding: 24px;
            }

            .section-title {
                margin: 0 0 18px;
                font-size: 1.25rem;
            }

            .thread-list,
            .post-list {
                display: grid;
                gap: 16px;
            }

            .thread-card,
            .post-card {
                padding: 18px;
            }

            .thread-card a {
                text-decoration: none;
            }

            .eyebrow,
            .meta {
                color: var(--muted);
                font-size: 0.92rem;
                line-height: 1.6;
            }

            .card-title {
                margin: 8px 0 10px;
                font-size: 1.35rem;
                line-height: 1.35;
            }

            .card-body {
                margin: 0;
                white-space: pre-wrap;
                line-height: 1.8;
                color: #27272a;
            }

            .badge-row {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 12px;
            }

            .badge {
                border: 1px solid var(--line);
                border-radius: 999px;
                padding: 6px 10px;
                background: #fff;
                font-size: 0.85rem;
            }

            .status {
                margin-bottom: 16px;
                padding: 12px 14px;
                border-radius: 16px;
                background: rgba(38, 92, 52, 0.12);
                border: 1px solid rgba(38, 92, 52, 0.2);
                color: var(--success);
            }

            .admin-banner {
                margin-bottom: 16px;
                padding: 14px 16px;
                border-radius: 16px;
                background: rgba(140, 29, 24, 0.09);
                border: 1px solid rgba(140, 29, 24, 0.16);
                color: #7a1c1c;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .admin-banner form {
                display: block;
            }

            .admin-banner button {
                background: #8c1d18;
                padding: 10px 14px;
            }

            form {
                display: grid;
                gap: 14px;
            }

            label {
                display: grid;
                gap: 7px;
                font-size: 0.95rem;
            }

            input,
            select,
            textarea,
            button {
                font: inherit;
            }

            input,
            select,
            textarea {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: 14px;
                padding: 12px 14px;
                background: #fff;
                color: var(--ink);
            }

            textarea {
                min-height: 140px;
                resize: vertical;
            }

            button {
                border: 0;
                border-radius: 999px;
                padding: 12px 18px;
                background: var(--brand);
                color: white;
                cursor: pointer;
                justify-self: start;
            }

            .errors {
                margin: 0 0 16px;
                padding: 12px 16px;
                border-radius: 16px;
                border: 1px solid rgba(146, 37, 37, 0.2);
                background: rgba(146, 37, 37, 0.08);
                color: #7a1c1c;
            }

            .empty {
                margin: 0;
                color: var(--muted);
                line-height: 1.8;
            }

            .back-link {
                display: inline-block;
                margin-bottom: 16px;
                text-decoration: none;
                color: var(--muted);
            }

            @media (max-width: 900px) {
                .grid.two {
                    grid-template-columns: 1fr;
                }

                .shell {
                    width: min(100% - 24px, 1100px);
                    padding-top: 20px;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="hero">
                <h1>日常BASE</h1>
                <p>番組や記事の感想を、気軽にゆるく書いていける場所です。</p>
                <nav class="hero-nav">
                    <a href="{{ route('board.index') }}">掲示板一覧</a>
                    <a href="{{ route('board.timeline') }}">タイムライン</a>
                </nav>
            </section>

            @if (!empty($isAdmin))
                <div class="admin-banner">
                    <div>管理モードで表示中です。スレッドIDと削除ボタンが見えています。</div>
                    <form method="POST" action="{{ route('admin.articles.logout') }}">
                        @csrf
                        <button type="submit">管理モードを解除</button>
                    </form>
                </div>
            @endif

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>
