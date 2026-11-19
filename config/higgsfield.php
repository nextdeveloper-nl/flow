<?php

/**
 * Higgsfield (image/video generation) settings for the Flow `higgsfield` pusher.
 *
 * The key id/secret are the PLATFORM credentials, used when a pusher has no external_service_id of its own.
 * Create the API key in the Higgsfield console (https://docs.higgsfield.ai) and fund the API balance, which is
 * separate from the website plan. The webhook secret ("whsec_...") is shown in the console Settings and verifies the
 * "generation finished" webhook.
 */
return [
    // API key id and secret (sent as "Authorization: Key <id>:<secret>", server side only)
    'key_id'         => env('HIGGSFIELD_API_KEY_ID'),
    'key_secret'     => env('HIGGSFIELD_API_KEY_SECRET'),

    // Standard Webhooks secret for POST /public/flow/higgsfield/webhook/{generation_id}
    'webhook_secret' => env('HIGGSFIELD_WEBHOOK_SECRET'),

    'base_url'       => env('HIGGSFIELD_BASE_URL', 'https://api.higgsfield.ai'),

    // Where generated files are stored: a disk name from config/filesystems.php, 'local', or empty for the Commons default
    'storage'        => env('HIGGSFIELD_STORAGE'),
];
