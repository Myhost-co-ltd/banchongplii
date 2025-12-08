<?php

return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => false,

    'options' => [
        // Use Dompdf built-in fonts (DejaVu) to avoid path issues
        'font_dir' => null,
        'font_cache' => null,
        'temp_dir' => sys_get_temp_dir(),

        // No custom font registrations
        'font_family' => [],

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

        // Use built-in DejaVu Sans (Thai supported)
        'default_font' => 'dejavu sans',

        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => false,
        'allowed_remote_hosts' => null,
        'font_height_ratio' => 1.3,
        'enable_html5_parser' => true,
    ],
];
