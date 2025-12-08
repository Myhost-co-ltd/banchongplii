<?php

return [

    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => true,

    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),

        // -------------------------
        // ฟอนต์ที่ DomPDF รู้จัก
        // -------------------------
        'font_family' => [
            'sarabun' => [
                'normal'      => storage_path('fonts/Sarabun-Regular.ttf'),
                'bold'        => storage_path('fonts/Sarabun-Bold.ttf'),
                'italic'      => storage_path('fonts/Sarabun-Regular.ttf'),
                'bold_italic' => storage_path('fonts/Sarabun-Bold.ttf'),
            ],
            'noto sans thai' => [
                'normal'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold'        => storage_path('fonts/NotoSansThai-Bold.ttf'),
                'italic'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
                'bold_italic' => storage_path('fonts/NotoSansThai-Bold.ttf'),
            ],
            'leelawadee' => [
                'normal'      => storage_path('fonts/LeelawUI.ttf'),
                'bold'        => storage_path('fonts/LeelaUIb.ttf'),
            ],
        ],


        // -------------------------
        // การตั้งค่าทั่วไป
        // -------------------------
        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'artifactPathValidation' => null,
        'log_output_file' => null,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',

        // ✔ ฟอนต์เริ่มต้นเป็น Sarabun
        'default_font' => 'sarabun',

        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => false,
        'allowed_remote_hosts' => null,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],

];
