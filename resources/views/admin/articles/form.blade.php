<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $article ? '記事編集' : '記事作成' }} | 日常BASE</title>
        <style>
            * {
                box-sizing: border-box;
            }
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
            .grid > label {
                flex: 1 1 260px;
            }
            h1 {
                margin: 0;
                font-size: 2rem;
            }
            label {
                display: block;
                margin-bottom: 18px;
                font-weight: bold;
            }
            input, select, textarea, button, a {
                font: inherit;
            }
            input[type="text"], input[type="datetime-local"], select, textarea {
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
                min-height: 110px;
                resize: vertical;
                line-height: 1.7;
            }
            textarea.body {
                min-height: 320px;
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
            .status, .errors {
                margin-bottom: 18px;
                padding: 13px 15px;
                border-radius: 14px;
            }
            .status {
                background: rgba(38, 92, 52, 0.12);
                color: #265c34;
            }
            .errors {
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
            .preview {
                width: min(100%, 520px);
                margin-top: 10px;
                border: 1px solid #e4e4e7;
                border-radius: 16px;
                overflow: hidden;
                background: #f4f4f5;
            }
            .preview img {
                display: block;
                width: 100%;
                height: auto;
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <h1>{{ $article ? '記事編集 #'.$article->id : '記事作成' }}</h1>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="secondary" href="{{ route('admin.articles.index') }}">記事一覧へ戻る</a>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ $article ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @if ($article)
                        @method('PUT')
                    @endif

                    <label>
                        タイトル
                        <input type="text" name="title" value="{{ old('title', $article?->title) }}" maxlength="255" required>
                    </label>

                    <label>
                        slug
                        <input type="text" name="slug" value="{{ old('slug', $article?->slug) }}" maxlength="255" required>
                        <span class="hint">URLに使う英数字・ハイフン・アンダースコア（例: episode-03-notes）</span>
                    </label>

                    <label>
                        概要
                        <textarea name="excerpt">{{ old('excerpt', $article?->excerpt) }}</textarea>
                    </label>

                    <label>
                        アイキャッチ画像
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        <span class="hint">JPEG / PNG / WebP、5MBまで。設定すると記事一覧と詳細に表示されます。</span>
                        @if ($article?->image_url)
                            <div class="preview">
                                <img src="{{ $article->image_url }}" alt="">
                            </div>
                        @endif
                    </label>

                    <label>
                        本文
                        <textarea class="body" name="body" required>{{ old('body', $article?->body) }}</textarea>
                    </label>

                    <div class="grid">
                        <label>
                            記事種別
                            <select name="type">
                                <option value="">未設定</option>
                                <option value="episode" @selected(old('type', $article?->type) === 'episode')>エピソード補足</option>
                                <option value="editorial" @selected(old('type', $article?->type) === 'editorial')>編集記事</option>
                            </select>
                        </label>

                        <label>
                            公開日時
                            <input
                                type="datetime-local"
                                name="published_at"
                                value="{{ old('published_at', $article?->published_at?->format('Y-m-d\TH:i')) }}"
                            >
                            <span class="hint">未来日時を指定すると公開予約になります。</span>
                        </label>
                    </div>

                    <input type="hidden" name="is_public" value="0">
                    <label class="checkbox">
                        <input
                            type="checkbox"
                            name="is_public"
                            value="1"
                            @checked((bool) old('is_public', $article?->is_public ?? false))
                        >
                        公開対象にする
                    </label>

                    <div class="actions">
                        <button class="primary" type="submit">{{ $article ? '更新する' : '作成する' }}</button>
                        <a class="secondary" href="{{ route('admin.articles.index') }}">キャンセル</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
