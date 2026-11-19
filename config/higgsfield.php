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

    // Accounts (uuids, comma separated) allowed to spend the PLATFORM credentials above. Every other account must bring
    // its own credentials through the pusher's external_service_id. Empty = nobody may use the platform credentials.
    'platform_account_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('HIGGSFIELD_PLATFORM_ACCOUNT_IDS', ''))))),

    // Hard ceilings applied whenever the platform credentials are used. They cap whatever the pusher's own
    // provider_metadata asks for (a tenant can lower them, never raise them or leave them off).
    'platform_hourly_limit'     => (int) env('HIGGSFIELD_PLATFORM_HOURLY_LIMIT', 10),
    'platform_max_cost_usd'     => (float) env('HIGGSFIELD_PLATFORM_MAX_COST_USD', 1),
    'platform_daily_budget_usd' => (float) env('HIGGSFIELD_PLATFORM_DAILY_BUDGET_USD', 5),

    'base_url'       => env('HIGGSFIELD_BASE_URL', 'https://api.higgsfield.ai'),

    // Where generated files are stored: a disk name from config/filesystems.php, 'local', or empty for the Commons default
    'storage'        => env('HIGGSFIELD_STORAGE'),
];
