<?php

return [
    /*
    |----------------------------------------------------------------------
    | Local path to GraphictoriaPlayer.exe (developer machines only).
    | Leave blank in production — the client is installed by the user.
    |----------------------------------------------------------------------
    */
    'client_path' => env('G5_CLIENT_PATH', ''),

    /*
    |----------------------------------------------------------------------
    | Local path to GraphictoriaStudio.exe (developer machines only).
    |----------------------------------------------------------------------
    */
    'studio_path' => env('G5_STUDIO_PATH', ''),

    /*
    |----------------------------------------------------------------------
    | Base URL of the Cloud Compute Service (GtoriaCompute).
    | Example: http://localhost:9000
    | Leave blank to disable avatar/thumbnail rendering.
    |----------------------------------------------------------------------
    */
    'cloud_compute_url' => env('CLOUD_COMPUTE_URL', ''),
];
