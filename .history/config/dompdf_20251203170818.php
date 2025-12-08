<?php

return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => false,

    'options' => [
        // Use Dompdf built-in fonts (DejaVu) to avoid path issues
        'font_dir' => storage_path('fonts'),
'font_cache' => storage_path('fonts/cache'),
'temp_dir' => storage_path('fonts/cache'),

'font_family' => [
    'NotoSansThai' => [
        'normal'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
        'bold'        => storage_path('fonts/NotoSansThai-Bold.ttf'),
        'italic'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
        'bold_italic' => storage_path('fonts/NotoSansThai-Bold.ttf'),
    ],
    'notosansthai' => [
        'normal'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
        'bold'        => storage_path('fonts/NotoSansThai-Bold.ttf'),
        'italic'      => storage_path('fonts/NotoSansThai-Regular.ttf'),
        'bold_italic' => storage_path('fonts/NotoSansThai-Bold.ttf'),
    ],
],

'default_font' => 'NotoSansThai',
