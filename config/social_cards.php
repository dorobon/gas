<?php

return [
    'cache_ttl_minutes' => (int) env('SOCIAL_CARD_CACHE_TTL', 1440),
    'node_binary' => env('SOCIAL_CARD_NODE_BINARY', 'node'),
    'renderer_script' => resource_path('scripts/render-social-card.mjs'),
    'output_directory' => storage_path('app/social-cards'),
    'version' => 'v1',
    'default_social' => 'facebook',
    'default_og_type' => 'website',
    'og_types' => [
        'website' => 'Objeto genérico de sitio web',
        'article' => 'Artículo editorial',
        'profile' => 'Perfil',
        'book' => 'Libro',
        'music.song' => 'Canción',
        'music.album' => 'Álbum',
        'music.playlist' => 'Playlist',
        'music.radio_station' => 'Emisora de radio',
        'video.movie' => 'Película',
        'video.episode' => 'Episodio',
        'video.tv_show' => 'Programa de TV',
        'video.other' => 'Otro vídeo',
    ],
    'socials' => [
        'facebook' => [
            'label' => 'Facebook',
            'default_type' => 'landscape',
            'default_og_type' => 'website',
            'twitter_card' => null,
            'types' => [
                'landscape' => [
                    'label' => 'Feed OG 1.91:1',
                    'width' => 1200,
                    'height' => 630,
                    'aspect_ratio' => '1.91:1',
                ],
                'square' => [
                    'label' => 'Square 1:1',
                    'width' => 1200,
                    'height' => 1200,
                    'aspect_ratio' => '1:1',
                ],
            ],
        ],
        'twitter' => [
            'label' => 'X / Twitter',
            'default_type' => 'summary_large_image',
            'default_og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'types' => [
                'summary_large_image' => [
                    'label' => 'summary_large_image',
                    'width' => 1200,
                    'height' => 628,
                    'aspect_ratio' => '1.91:1',
                ],
                'summary' => [
                    'label' => 'summary',
                    'width' => 1200,
                    'height' => 1200,
                    'aspect_ratio' => '1:1',
                ],
            ],
        ],
        'whatsapp' => [
            'label' => 'WhatsApp',
            'default_type' => 'landscape',
            'default_og_type' => 'website',
            'twitter_card' => null,
            'types' => [
                'landscape' => [
                    'label' => 'Preview compartido 1.91:1',
                    'width' => 1200,
                    'height' => 630,
                    'aspect_ratio' => '1.91:1',
                ],
                'square' => [
                    'label' => 'Preview cuadrado 1:1',
                    'width' => 1080,
                    'height' => 1080,
                    'aspect_ratio' => '1:1',
                ],
            ],
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'default_type' => 'landscape',
            'default_og_type' => 'article',
            'twitter_card' => null,
            'types' => [
                'landscape' => [
                    'label' => 'Feed horizontal 1.91:1',
                    'width' => 1200,
                    'height' => 627,
                    'aspect_ratio' => '1.91:1',
                ],
            ],
        ],
        'telegram' => [
            'label' => 'Telegram',
            'default_type' => 'landscape',
            'default_og_type' => 'website',
            'twitter_card' => null,
            'types' => [
                'landscape' => [
                    'label' => 'Preview horizontal 1.91:1',
                    'width' => 1200,
                    'height' => 630,
                    'aspect_ratio' => '1.91:1',
                ],
                'square' => [
                    'label' => 'Preview cuadrado 1:1',
                    'width' => 1200,
                    'height' => 1200,
                    'aspect_ratio' => '1:1',
                ],
            ],
        ],
        'generic' => [
            'label' => 'Open Graph genérico',
            'default_type' => 'landscape',
            'default_og_type' => 'website',
            'twitter_card' => null,
            'types' => [
                'landscape' => [
                    'label' => 'Universal 1.91:1',
                    'width' => 1200,
                    'height' => 630,
                    'aspect_ratio' => '1.91:1',
                ],
            ],
        ],
    ],
];
