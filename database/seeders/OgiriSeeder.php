<?php

namespace Database\Seeders;

use App\Models\OgiriPrompt;
use Illuminate\Database\Seeder;

class OgiriSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'title' => 'こんなPodcastの告知はいやだ',
                'body' => '思わず二度見してしまうPodcast告知を考えてください。',
            ],
            [
                'title' => '日常BASEに突然追加された謎機能とは？',
                'body' => '便利そうで便利じゃない、でもちょっと欲しい機能をお願いします。',
            ],
            [
                'title' => 'エンジニアが朝イチで言いそうな寝言',
                'body' => '開発者あるあるをゆるく混ぜて回答してください。',
            ],
        ];

        foreach ($prompts as $prompt) {
            OgiriPrompt::query()->updateOrCreate(
                ['title' => $prompt['title']],
                [
                    ...$prompt,
                    'is_public' => true,
                    'published_at' => now()->subMinute(),
                ],
            );
        }
    }
}
