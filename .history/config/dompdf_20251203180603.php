<?php

return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => false,

    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts/cache'),
        'temp_dir' => storage_path('fonts/cache'),

        // Thai fonts (add project's Noto Sans Thai if available)
        'font_family' => [
            'LeelawUI' => [
                'normal' => storage_path('fonts/LeelawUI.ttf'),
                'bold' => storage_path('fonts/LeelaUIb.ttf'),
                'italic' => storage_path('fonts/LeelawUI.ttf'),
                'bold_italic' => storage_path('fonts/LeelaUIb.ttf'),
            ],
            'leelawui' => [
                'normal' => storage_path('fonts/LeelawUI.ttf'),
                'bold' => storage_path('fonts/LeelaUIb.ttf'),
                'italic' => storage_path('fonts/LeelawUI.ttf'),
                'bold_italic' => storage_path('fonts/LeelaUIb.ttf'),
            ],
            // Noto Sans Thai (project-provided). Ensure `storage/fonts/NotoSansThai-Regular.ttf` exists.
            'NotoSansThai' => [
                'normal' => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold' => storage_path('fonts/NotoSansThai-Bold.ttf'),
                'italic' => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold_italic' => storage_path('fonts/NotoSansThai-Bold.ttf'),
            ],
            'notosansthai' => [
                'normal' => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold' => storage_path('fonts/NotoSansThai-Bold.ttf'),
                'italic' => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold_italic' => storage_path('fonts/NotoSansThai-Bold.ttf'),
            ],
        ],

        'chroot' => realpath(base_path()),
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
             {
              font-family: 'NotoSansThai';
              src: url('{{ storage_path('fonts/NotoSansThai-Regular.ttf') }}') format('truetype');
              font-weight: normal;
              font-style: normal;
            }
            body { font-family: 'NotoSansThai', sans-serif; }
        // Default Thai font (prefer project-provided Noto Sans Thai if present)
        'default_font' => 'NotoSansThai',

        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => false,
        'allowed_remote_hosts' => null,
        'font_height_ratio' => 1.3,
        'enable_html5_parser' => true,
    ],
];
