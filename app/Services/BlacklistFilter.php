<?php

namespace App\Services;

use App\Models\BlacklistKeyword;

class BlacklistFilter
{
    public static function check(string $text): array
    {
        $keywords = cache()->remember('blacklist_keywords_v2', 600, function () {
            return BlacklistKeyword::where('is_active', true)
                ->get(['keyword', 'type'])
                ->map(fn ($k) => ['keyword' => $k->keyword, 'type' => $k->type])
                ->toArray();
        });

        $lower = mb_strtolower($text);

        foreach ($keywords as $kw) {
            $keyword = is_array($kw) ? ($kw['keyword'] ?? '') : (string) $kw;
            $type    = is_array($kw) ? ($kw['type'] ?? 'word') : 'word';

            if ($keyword === '') continue;

            $needle = mb_strtolower($keyword);

            if (str_contains($lower, $needle)) {
                return [
                    'blocked' => true,
                    'keyword' => $keyword,
                    'type'    => $type,
                    'reason'  => self::reasonMessage($type),
                ];
            }
        }

        return [
            'blocked' => false,
            'keyword' => null,
            'type'    => null,
            'reason'  => null,
        ];
    }

    protected static function reasonMessage(string $type): string
    {
        return match ($type) {
            'link'  => 'Pesan mengandung tautan yang tidak diizinkan.',
            'phone' => 'Pesan mengandung nomor telepon yang tidak diizinkan.',
            'email' => 'Pesan mengandung alamat email yang tidak diizinkan.',
            'word'  => 'Pesan mengandung kata yang dilarang.',
            default => 'Pesan mengandung konten yang dilarang.',
        };
    }
}