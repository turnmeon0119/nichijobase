<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $item ? 'News編集' : 'News作成' }} | 日常BASE</title>
        <style>
            * { box-sizing: border-box; }
            body { margin: 0; background: #f7f7f8; color: #171717; font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif; }
            .shell { width: min(880px, calc(100% - 24px)); margin: 0 auto; padding: 24px 0 48px; }
            .panel { padding: 24px; border: 1px solid #e4e4e7; border-radius: 16px; background: #fff; box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06); }
            .topbar, .actions { display: flex; gap: 14px; flex-wrap: wrap; }
            .topbar { align-items: center; justify-content: space-between; margin-bottom: 24px; }
            h1 { margin: 0; font-size: 2rem; }
            label { display: block; margin-bottom: 18px; font-weight: bold; }
            input, textarea, button, a { font: inherit; }
            input[type="text"], input[type="datetime-local"], textarea { display: block; width: 100%; margin-top: 8px; padding: 12px 14px; border: 1px solid #d4d4d8; border-radius: 12px; background: #fff; color: #171717; }
            textarea { min-height: 320px; resize: vertical; line-height: 1.7; }
            .checkbox { display: flex; align-items: center; gap: 10px; font-weight: normal; }
            .primary, .secondary { display: inline-block; padding: 11px 17px; border-radius: 999px; cursor: pointer; text-decoration: none; }
            .primary { border: 0; background: #171717; color: #fff; }
            .secondary { border: 1px solid #d4d4d8; background: #fff; color: #171717; }
            .status, .errors { margin-bottom: 18px; padding: 13px 15px; border-radius: 14px; }
            .status { background: rgba(38, 92, 52, 0.12); color: #265c34; }
            .errors { background: rgba(140, 29, 24, 0.1); color: #8c1d18; }
            .errors ul { margin: 0; padding-left: 20px; }
            .hint { display: block; margin-top: 6px; color: #52525b; font-size: 0.85rem; font-weight: normal; }
            .save-note { margin: 6px 0 18px; padding: 14px 16px; border: 1px solid #e4e4e7; border-radius: 14px; background: #fafafa; color: #52525b; line-height: 1.7; }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="panel">
                <div class="topbar">
                    <h1>{{ $item ? 'News編集 #'.$item->id : 'News作成' }}</h1>
                    <div class="actions">
                        <a class="secondary" href="{{ route('admin.dashboard') }}">管理トップへ</a>
                        <a class="secondary" href="{{ route('admin.news.index') }}">News一覧へ戻る</a>
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

                <form method="POST" action="{{ $item ? route('admin.news.update', $item) : route('admin.news.store') }}">
                    @csrf
                    @if ($item)
                        @method('PUT')
                    @endif

                    <label>
                        タイトル
                        <input type="text" name="title" value="{{ old('title', $item?->title) }}" maxlength="255" required>
                    </label>

                    <label>
                        slug
                        <input type="text" name="slug" value="{{ old('slug', $item?->slug) }}" maxlength="255" required>
                        <span class="hint">URLに使う英数字・ハイフン・アンダースコア（例: site-renewal）</span>
                    </label>

                    <label>
                        本文
                        <textarea name="body" required>{{ old('body', $item?->body) }}</textarea>
                    </label>

                    <label>
                        公開日時
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $item?->published_at?->format('Y-m-d\TH:i')) }}">
                        <span class="hint">未来日時を指定すると公開予約になります。</span>
                    </label>

                    <input type="hidden" name="is_public" value="0">
                    <div class="save-note">
                        下書き保存すると公開ページには表示されません。公開して保存すると公開対象になります。
                        公開日時が空の場合は、今すぐ公開として保存します。
                    </div>

                    <div class="actions">
                        <button class="secondary" type="submit" name="save_mode" value="draft">下書き保存</button>
                        <button class="primary" type="submit" name="save_mode" value="publish">公開して保存</button>
                        <a class="secondary" href="{{ route('admin.news.index') }}">キャンセル</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
