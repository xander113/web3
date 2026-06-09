<?php

return [
    /*
    |----------------------------------------------------------------------
    | Local path to the Graphictoria5 Client directory (developer machines).
    | The player exe is expected to live inside this directory.
    | Leave blank in production — the client is installed by the user.
    |----------------------------------------------------------------------
    */
    'client_path' => env('G5_CLIENT_PATH', ''),

    /*
    |----------------------------------------------------------------------
    | Local path to the Graphictoria5 Studio directory (developer machines).
    |----------------------------------------------------------------------
    */
    'studio_path' => env('G5_STUDIO_PATH', ''),

    /*
    |----------------------------------------------------------------------
    | Port the Cloud Compute Service (GtoriaCompute) listens on.
    | The service is always reached at http://127.0.0.1:{port}.
    | Its executable is resolved from dirname(G5_CLIENT_PATH)/Cloud Compute Service/.
    | Set to 0 / leave blank to disable rendering.
    |----------------------------------------------------------------------
    */
    'cloud_compute_port' => (int) env('CLOUD_COMPUTE_PORT', 0),
];
