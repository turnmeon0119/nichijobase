<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>日常BASE</title>
        <style>
            :root {
                --bg: #f5ede1;
                --bg-deep: #e7d5ba;
                --surface: rgba(255, 250, 244, 0.86);
                --surface-strong: rgba(255, 253, 248, 0.94);
                --line: rgba(111, 74, 42, 0.18);
                --ink: #24170c;
                --muted: #6f5b49;
                --brand: #ae4a1d;
                --brand-deep: #692207;
                --accent: #2e6259;
                --shadow: 0 24px 60px rgba(82, 48, 18, 0.14);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                color: var(--ink);
                font-family: Georgia, "Hiragino Mincho ProN", "Yu Mincho", serif;
                background:
                    radial-gradient(circle at top left, rgba(255, 255, 255, 0.8), transparent 24%),
                    radial-gradient(circle at bottom right, rgba(174, 74, 29, 0.12), transparent 22%),
                    linear-gradient(145deg, var(--bg) 0%, var(--bg-deep) 48%, #f8f4ee 100%);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            .shell {
                width: min(1160px, calc(100% - 32px));
                margin: 0 auto;
                padding: 28px 0 56px;
            }

            .masthead,
            .panel,
            .route-card,
            .quote-card {
                border: 1px solid var(--line);
                border-radius: 28px;
                background: var(--surface);
                box-shadow: var(--shadow);
                backdrop-filter: blur(12px);
            }

            .masthead {
                padding: 18px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 22px;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .brand-mark {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                background:
                    linear-gradient(135deg, var(--brand), var(--brand-deep));
                color: white;
                display: grid;
                place-items: center;
                font-size: 1.2rem;
                font-weight: bold;
            }

            .brand-copy strong,
            .metric strong {
                display: block;
            }

            .brand-copy span,
            .metric span,
            .lede,
            .card-copy,
            .panel-copy,
            .quote-card p {
                color: var(--muted);
            }

            .top-nav {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .top-nav a,
            .cta,
            .mini-link {
                border-radius: 999px;
                border: 1px solid var(--line);
                background: var(--surface-strong);
            }

            .top-nav a {
                padding: 10px 14px;
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
                gap: 22px;
            }

            .panel {
                padding: 30px;
                position: relative;
                overflow: hidden;
            }

            .panel::after {
                content: "";
                position: absolute;
                inset: auto -70px -70px auto;
                width: 220px;
                height: 220px;
                border-radius: 999px;
                background: radial-gradient(circle, rgba(174, 74, 29, 0.22), rgba(174, 74, 29, 0));
            }

            .eyebrow {
                display: inline-block;
                padding: 7px 12px;
                border-radius: 999px;
                background: rgba(46, 98, 89, 0.12);
                color: var(--accent);
                font-size: 0.9rem;
                letter-spacing: 0.05em;
            }

            h1 {
                margin: 16px 0 14px;
                font-size: clamp(2.8rem, 6vw, 5.6rem);
                line-height: 0.92;
                letter-spacing: 0.01em;
            }

            .lede {
                max-width: 41rem;
                margin: 0;
                line-height: 1.9;
                font-size: 1.04rem;
            }

            .cta-row {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
                margin-top: 24px;
            }

            .cta {
                padding: 13px 18px;
            }

            .cta.primary {
                border-color: transparent;
                color: #fff;
                background: linear-gradient(135deg, var(--brand), var(--brand-deep));
            }

            .metrics {
                display: grid;
                gap: 12px;
            }

            .metric,
            .quote-card {
                padding: 20px;
                border: 1px solid var(--line);
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.64);
            }

            .metric strong {
                font-size: 1.9rem;
                margin-bottom: 6px;
            }

            .section {
                margin-top: 22px;
            }

            .section-head {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 14px;
            }

            .section-head h2 {
                margin: 0;
                font-size: 1.7rem;
            }

            .section-head p {
                margin: 0;
                color: var(--muted);
            }

            .route-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
            }

            .route-card {
                padding: 22px;
            }

            .route-kicker {
                font-size: 0.88rem;
                letter-spacing: 0.06em;
                color: var(--accent);
                text-transform: uppercase;
            }

            .route-card h3 {
                margin: 10px 0 10px;
                font-size: 1.45rem;
            }

            .card-copy,
            .panel-copy,
            .quote-card p {
                margin: 0;
                line-height: 1.8;
            }

            .route-link {
                display: inline-block;
                margin-top: 18px;
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.72);
                border: 1px solid var(--line);
                color: var(--brand-deep);
            }

            .subgrid {
                margin-top: 18px;
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
                gap: 18px;
            }

            .stack {
                display: grid;
                gap: 14px;
            }

            .mini-link {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                margin-top: 16px;
            }

            code {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 0.92em;
            }

            @media (max-width: 920px) {
                .hero,
                .route-grid,
                .subgrid {
                    grid-template-columns: 1fr;
                }

                .masthead {
                    align-items: start;
                    flex-direction: column;
                }

                .shell {
                    width: min(100% - 24px, 1160px);
                    padding-top: 20px;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <header class="masthead">
                <div class="brand">
                    <div class="brand-mark">P</div>
                    <div class="brand-copy">
                        <strong>日常BASE</strong>
                        <span>日常の記録、掲示板、タイムライン</span>
                    </div>
                </div>

                <nav class="top-nav">
                    <a href="{{ route('board.timeline') }}">タイムライン</a>
                    <a href="{{ route('board.index') }}">掲示板</a>
                    <a href="/api/threads">API</a>
                </nav>
            </header>

            <section class="hero">
                <article class="panel">
                    <span class="eyebrow">日常BASE Community</span>
                    <h1>話したあとに、
                        <br>会話が残る場所。</h1>
                    <p class="lede">
                        番組を聴いたあと、その感想や補足を記事単位でも雑談単位でも残せる構成です。
                        今はタイムライン、掲示板、JSON API をひとつの入口から開けます。
                    </p>

                    <div class="cta-row">
                        <a class="cta primary" href="{{ route('board.timeline') }}">新着を追う</a>
                        <a class="cta" href="{{ route('board.index') }}">スレッドで読む</a>
                        <a class="cta" href="/api/threads">API を確認</a>
                    </div>
                </article>

                <div class="metrics">
                    <div class="metric">
                        <strong>/timeline</strong>
                        <span>スレ作成と返信をまとめて、新着順で流し見できます。</span>
                    </div>
                    <div class="metric">
                        <strong>/board</strong>
                        <span>スレッド一覧、詳細表示、返信投稿までブラウザで完結します。</span>
                    </div>
                    <div class="quote-card">
                        <p>トップは Laravel の初期画面ではなく、このプロジェクトの動線として差し替え済みです。</p>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-head">
                    <div>
                        <h2>Open Routes</h2>
                        <p>使い方ごとに入口を分けています。</p>
                    </div>
                </div>

                <div class="route-grid">
                    <article class="route-card">
                        <div class="route-kicker">Flow</div>
                        <h3>Timeline</h3>
                        <p class="card-copy">Twitter 風に、スレ本体と返信を時系列で追うための画面です。まず全体感を見たいときに向いています。</p>
                        <a class="route-link" href="{{ route('board.timeline') }}"><code>/timeline</code></a>
                    </article>

                    <article class="route-card">
                        <div class="route-kicker">Thread</div>
                        <h3>Board</h3>
                        <p class="card-copy">スレッド単位でじっくり読む画面です。新規スレ作成や返信投稿もここから行えます。</p>
                        <a class="route-link" href="{{ route('board.index') }}"><code>/board</code></a>
                    </article>

                    <article class="route-card">
                        <div class="route-kicker">Data</div>
                        <h3>API</h3>
                        <p class="card-copy">フロントや別クライアントから使うための JSON エンドポイントです。実装確認にも使えます。</p>
                        <a class="route-link" href="/api/threads"><code>/api/threads</code></a>
                    </article>
                </div>

                <div class="subgrid">
                    <article class="panel">
                        <h2 style="margin: 0 0 10px;">このサイトで今できること</h2>
                        <p class="panel-copy">記事 API、掲示板 API、ブラウザで開ける掲示板画面、タイムライン画面まで入っています。次に記事ページ側へ導線を増やせば、番組サイトらしさがさらに強くなります。</p>
                        <a class="mini-link" href="{{ route('board.index') }}">掲示板から確認する</a>
                    </article>

                    <div class="stack">
                        <div class="quote-card">
                            <strong>Recommended</strong>
                            <p>最初は <code>/timeline</code> を開くと、今のUIの方向性が一番わかりやすいです。</p>
                        </div>
                        <div class="quote-card">
                            <strong>Next Step</strong>
                            <p>記事一覧ページと記事詳細ページを追加すると、掲示板とのつながりが自然になります。</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
