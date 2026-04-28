<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Article::query()->upsert(
            [
                [
                    'title' => 'Episode 01 補足: 開発環境セットアップの要点',
                    'slug' => 'episode-01-setup-notes',
                    'excerpt' => 'エピソード01で触れた開発環境セットアップの補足をまとめました。',
                    'body' => "このページはエピソード補足記事のサンプルです。\n\n- Docker構成\n- Laravel API\n- Next.js フロント\n\n初期MVPでは、まず記事が読まれる導線を最優先にします。",
                    'type' => 'episode',
                    'published_at' => Carbon::now()->subDays(5),
                    'is_public' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'title' => '編集記事: PodcastサイトMVPで最初に作るべき機能',
                    'slug' => 'editorial-mvp-priorities',
                    'excerpt' => '会員や決済より先に、読む体験と計測を成立させるための優先順位。',
                    'body' => "このページは編集記事のサンプルです。\n\nKPIは記事詳細ページの閲覧です。\n記事一覧から詳細へ迷わず到達できる構成を作ります。",
                    'type' => 'editorial',
                    'published_at' => Carbon::now()->subDays(3),
                    'is_public' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'title' => 'Episode 02 補足: 計測を最小実装する',
                    'slug' => 'episode-02-pageview-basics',
                    'excerpt' => 'page_viewsテーブルで初期の閲覧計測を実装する方法。',
                    'body' => "このページはエピソード補足記事のサンプルです。\n\n詳細APIアクセス時に1レコードを保存し、view_countで表示できます。",
                    'type' => 'episode',
                    'published_at' => Carbon::now()->subDay(),
                    'is_public' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ],
            ['slug'],
            ['title', 'excerpt', 'body', 'type', 'published_at', 'is_public', 'updated_at']
        );
    }
}
