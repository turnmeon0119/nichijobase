<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $item ? 'News編集' : 'News作成' }} | 日常BASE</title>
        <style>
            * { box-sizing: border-box; }
            body { margin: 0; background: #f7f7f8; color: #171717; font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", sans-serif; }
            .shell { width: min(960px, calc(100% - 24px)); margin: 0 auto; padding: 24px 0 48px; }
            .panel { padding: 24px; border: 1px solid #e4e4e7; border-radius: 16px; background: #fff; box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06); }
            .topbar, .actions { display: flex; gap: 14px; flex-wrap: wrap; }
            .topbar { align-items: center; justify-content: space-between; margin-bottom: 24px; }
            h1 { margin: 0; font-size: 2rem; }
            label { display: block; margin-bottom: 18px; font-weight: bold; }
            input, textarea, button, a { font: inherit; }
            input[type="text"], input[type="datetime-local"], textarea { display: block; width: 100%; margin-top: 8px; padding: 12px 14px; border: 1px solid #d4d4d8; border-radius: 12px; background: #fff; color: #171717; }
            textarea { min-height: 220px; resize: vertical; line-height: 1.7; }
            .primary, .secondary { display: inline-block; padding: 11px 17px; border-radius: 999px; cursor: pointer; text-decoration: none; }
            .primary { border: 0; background: #171717; color: #fff; }
            .secondary { border: 1px solid #d4d4d8; background: #fff; color: #171717; }
            .status, .errors { margin-bottom: 18px; padding: 13px 15px; border-radius: 14px; }
            .status { background: rgba(38, 92, 52, 0.12); color: #265c34; }
            .errors { background: rgba(140, 29, 24, 0.1); color: #8c1d18; }
            .errors ul { margin: 0; padding-left: 20px; }
            .hint { display: block; margin-top: 6px; color: #52525b; font-size: 0.85rem; font-weight: normal; }
            .save-note { margin: 6px 0 18px; padding: 14px 16px; border: 1px solid #e4e4e7; border-radius: 14px; background: #fafafa; color: #52525b; line-height: 1.7; }
            .block-editor { margin: 22px 0; padding: 0; border: 1px solid #e4e4e7; border-radius: 18px; overflow: hidden; background: #fff; }
            .block-editor-title { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 18px 18px 14px; border-bottom: 1px solid #e4e4e7; background: #fafafa; }
            .block-editor-title h2 { margin: 0; font-size: 1.15rem; }
            .block-toolbar { display: flex; flex-wrap: wrap; gap: 10px; }
            .news-canvas { min-height: 420px; padding: 22px; background: linear-gradient(rgba(24, 24, 27, 0.035) 1px, transparent 1px), #fff; background-size: 100% 34px; }
            .content-block { position: relative; margin: 0; padding: 6px 42px 6px 0; border: 0; background: transparent; }
            .content-block + .content-block { margin-top: 14px; }
            .block-head { position: absolute; top: 4px; right: 0; margin: 0; }
            .block-kicker { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
            .remove-block { border: 1px solid #e4e4e7; background: #fff; color: #52525b; border-radius: 999px; padding: 5px 9px; cursor: pointer; font-size: 0.82rem; opacity: 0; transition: opacity 0.15s ease; }
            .content-block:hover .remove-block, .content-block:focus-within .remove-block { opacity: 1; }
            .block-preview { width: min(100%, 620px); margin: 10px auto 12px; overflow: hidden; border: 1px solid #e4e4e7; border-radius: 14px; background: #f4f4f5; }
            .block-preview img { display: block; width: 100%; max-height: 420px; object-fit: contain; }
            .block-preview.is-empty { display: none; }
            .inline-label { margin: 0; font-weight: normal; }
            .inline-label-title { display: none; }
            .canvas-textarea { min-height: 170px; margin: 0; padding: 4px 0; border: 0; border-radius: 0; background: transparent; font-size: 1.02rem; line-height: 1.9; outline: none; }
            .canvas-textarea:focus { box-shadow: none; }
            .image-upload-row { width: min(100%, 620px); margin: 0 auto; padding: 14px; border: 1px dashed #d4d4d8; border-radius: 14px; background: rgba(250, 250, 250, 0.9); }
            .block-image-input { width: 100%; }
            .caption-input { width: min(100%, 620px) !important; margin: 10px auto 0 !important; border: 0 !important; border-bottom: 1px solid #d4d4d8 !important; border-radius: 0 !important; text-align: center; background: transparent !important; color: #52525b !important; }
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
                        @if ($item)
                            <a class="secondary" href="{{ config('app.front_url', 'http://localhost:3000').'/news/'.$item->slug }}" target="_blank" rel="noreferrer">公開ページを確認</a>
                        @endif
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

                @php
                    $oldBlocks = old('blocks');
                    $newsBlocks = $item?->blocks ?? collect();
                    $blocks = collect($oldBlocks ?? $newsBlocks)->values();

                    if ($blocks->isEmpty()) {
                        $blocks = collect([
                            [
                                'type' => 'text',
                                'body' => old('body', $item?->body),
                            ],
                        ]);
                    }
                @endphp

                <form
                    method="POST"
                    action="{{ $item ? route('admin.news.update', $item) : route('admin.news.store') }}"
                    enctype="multipart/form-data"
                >
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
                        <span class="hint">URLに使う英数字・ハイフン・アンダースコア（例: event-notice）</span>
                    </label>

                    <section class="block-editor" aria-label="News本文ブロック">
                        <div class="block-editor-title">
                            <div>
                                <h2>News本文</h2>
                                <span class="hint">テキストと画像を上から順番に並べられます。画像にはキャプションも付けられます。</span>
                            </div>
                            <div class="block-toolbar">
                                <button class="secondary" type="button" data-add-block="text">テキストを追加</button>
                                <button class="secondary" type="button" data-add-block="image">画像を追加</button>
                            </div>
                        </div>

                        <div id="news-blocks" class="news-canvas">
                            @foreach ($blocks as $index => $block)
                                @php
                                    $isArray = is_array($block);
                                    $blockType = $isArray ? ($block['type'] ?? 'text') : $block->type;
                                    $blockId = $isArray ? ($block['id'] ?? null) : $block->id;
                                    $blockBody = $isArray ? ($block['body'] ?? '') : $block->body;
                                    $blockImageUrl = $isArray ? ($block['image_url'] ?? null) : $block->image_url;
                                    $blockImageCaption = $isArray ? ($block['image_caption'] ?? '') : $block->image_caption;
                                @endphp

                                <div class="content-block" data-block>
                                    <div class="block-head">
                                        <strong class="block-kicker">{{ $blockType === 'image' ? 'Image block' : 'Text block' }}</strong>
                                        <button class="remove-block" type="button" data-remove-block>削除</button>
                                    </div>
                                    @if ($blockId)
                                        <input type="hidden" name="blocks[{{ $index }}][id]" value="{{ $blockId }}">
                                    @endif
                                    <input type="hidden" name="blocks[{{ $index }}][type]" value="{{ $blockType }}">

                                    @if ($blockType === 'image')
                                        <div class="block-preview {{ $blockImageUrl ? '' : 'is-empty' }}" data-image-preview>
                                            <img src="{{ $blockImageUrl ?? '' }}" alt="">
                                        </div>
                                        <label class="image-upload-row">
                                            画像
                                            <input class="block-image-input" type="file" name="blocks[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp">
                                            <span class="hint">差し替える場合だけ選択してください。JPEG / PNG / WebP、5MBまで。</span>
                                        </label>
                                        <label class="inline-label">
                                            <span class="inline-label-title">画像下の説明</span>
                                            <input class="caption-input" type="text" name="blocks[{{ $index }}][image_caption]" value="{{ $blockImageCaption }}" maxlength="160" placeholder="画像下の説明">
                                        </label>
                                    @else
                                        <label class="inline-label">
                                            <span class="inline-label-title">テキスト</span>
                                            <textarea class="canvas-textarea" name="blocks[{{ $index }}][body]" placeholder="本文を書く">{{ $blockBody }}</textarea>
                                        </label>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>

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

        <template id="text-block-template">
            <div class="content-block" data-block>
                <div class="block-head">
                    <strong class="block-kicker">Text block</strong>
                    <button class="remove-block" type="button" data-remove-block>削除</button>
                </div>
                <input type="hidden" name="blocks[__INDEX__][type]" value="text">
                <label class="inline-label">
                    <span class="inline-label-title">テキスト</span>
                    <textarea class="canvas-textarea" name="blocks[__INDEX__][body]" placeholder="続きを書く"></textarea>
                </label>
            </div>
        </template>

        <template id="image-block-template">
            <div class="content-block" data-block>
                <div class="block-head">
                    <strong class="block-kicker">Image block</strong>
                    <button class="remove-block" type="button" data-remove-block>削除</button>
                </div>
                <input type="hidden" name="blocks[__INDEX__][type]" value="image">
                <div class="block-preview is-empty" data-image-preview>
                    <img src="" alt="">
                </div>
                <label class="image-upload-row">
                    画像
                    <input class="block-image-input" type="file" name="blocks[__INDEX__][image]" accept="image/jpeg,image/png,image/webp">
                    <span class="hint">JPEG / PNG / WebP、5MBまで。</span>
                </label>
                <label class="inline-label">
                    <span class="inline-label-title">画像下の説明</span>
                    <input class="caption-input" type="text" name="blocks[__INDEX__][image_caption]" maxlength="160" placeholder="画像下の説明">
                </label>
            </div>
        </template>

        <script>
            const blocksRoot = document.getElementById('news-blocks');
            let blockIndex = {{ $blocks->count() }};
            let activeBlock = null;

            document.addEventListener('focusin', (event) => {
                const block = event.target.closest('[data-block]');

                if (block) {
                    activeBlock = block;
                }
            });

            document.querySelectorAll('[data-add-block]').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.getAttribute('data-add-block');
                    const inserted = appendBlock(type, activeBlock);

                    if (type === 'image') {
                        appendBlock('text', inserted);
                    }
                });
            });

            function appendBlock(type, afterBlock = null) {
                const template = document.getElementById(`${type}-block-template`);
                const html = template.innerHTML.replaceAll('__INDEX__', String(blockIndex++));
                const reference = afterBlock && afterBlock.parentElement === blocksRoot
                    ? afterBlock
                    : blocksRoot.lastElementChild;

                if (reference) {
                    reference.insertAdjacentHTML('afterend', html);
                    return reference.nextElementSibling;
                }

                blocksRoot.insertAdjacentHTML('beforeend', html);
                return blocksRoot.lastElementChild;
            }

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-block]');

                if (!button) {
                    return;
                }

                const block = button.closest('[data-block]');
                const currentBlocks = blocksRoot.querySelectorAll('[data-block]');

                if (currentBlocks.length <= 1) {
                    block.querySelectorAll('textarea, input[type="text"], input[type="file"]').forEach((input) => {
                        input.value = '';
                    });
                    return;
                }

                block.remove();
            });

            document.addEventListener('change', (event) => {
                const input = event.target.closest('.block-image-input');

                if (!input || !input.files || input.files.length === 0) {
                    return;
                }

                const block = input.closest('[data-block]');
                const preview = block?.querySelector('[data-image-preview]');
                const image = preview?.querySelector('img');

                if (!preview || !image) {
                    return;
                }

                image.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('is-empty');
            });
        </script>
    </body>
</html>
