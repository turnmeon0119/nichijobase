<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CloudinaryImageService
{
    public function upload(UploadedFile $image): array
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $timestamp = time();
        $parameters = ['folder' => 'nichijobase/board', 'timestamp' => $timestamp];

        $response = Http::attach('file', $image->get(), $image->getClientOriginalName())
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                ...$parameters,
                'api_key' => config('services.cloudinary.api_key'),
                'signature' => $this->signature($parameters),
            ]);

        if ($response->failed()) {
            Log::warning('Cloudinary image upload failed.', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException('画像のアップロードに失敗しました。');
        }

        return [
            'image_url' => $response->json('secure_url'),
            'image_public_id' => $response->json('public_id'),
        ];
    }

    public function delete(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        $cloudName = (string) config('services.cloudinary.cloud_name');
        $parameters = ['public_id' => $publicId, 'timestamp' => time()];

        Http::post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
            ...$parameters,
            'api_key' => config('services.cloudinary.api_key'),
            'signature' => $this->signature($parameters),
        ])->throw();
    }

    private function signature(array $parameters): string
    {
        ksort($parameters);
        $value = collect($parameters)
            ->map(fn ($value, $key): string => "{$key}={$value}")
            ->implode('&');

        return sha1($value.config('services.cloudinary.api_secret'));
    }
}
