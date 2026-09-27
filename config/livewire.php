<?php

// Solo se sobreescribe lo necesario; el resto de claves usa los valores por
// defecto del paquete (Livewire hace merge a nivel de primera clave).
//
// El consultorio trabaja con internet móvil lento (EDGE/3G por hotspot): una
// subida puede tardar varios minutos. Con el default (5 min, 12 MB) la URL
// firmada expira antes de terminar y los archivos de 12–20 MB se rechazan
// aunque la validación de los componentes permita 20 MB.

return [

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK'),
        'rules' => ['required', 'file', 'max:20480'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 30,
        'cleanup' => true,
    ],

];
