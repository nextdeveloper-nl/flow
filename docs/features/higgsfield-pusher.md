# Higgsfield pusher (`higgsfield`)

Generates an image or video with [Higgsfield](https://docs.higgsfield.ai) when a Flow automation fires, and
stores the result on the Flow item.

```
Flow automation -> pusher `higgsfield` -> Higgsfield API (submit)
                                              |
                  webhook  --------------------+--------------------  polling fallback
                     |                                                      |
                     +----------> HiggsfieldGenerationsService::finalize() <+
                                              |
                              common_media row on the Flow item
```

## How it works

1. The automation pushes the item payload. `send()` renders the prompt, asks Higgsfield for a **cost estimate**,
   checks the budgets, **submits** the generation and returns `202`. The pusher log completes as *submitted*;
   the generation itself finishes later.
2. One `common_media` row per generation is created when it is submitted (no table of its own):

   | column | content |
   |---|---|
   | `object_type` / `object_id` | the Flow item |
   | `collection_name` | `higgsfield` |
   | `tags` | `higgsfield`, `higgsfield:image` or `higgsfield:video` |
   | `manipulations` | how it was made: `model_path`, `media_type`, `params` (request body) |
   | `custom_properties` | state: `status`, `request_id`, `pusher_id`, `pusher_log_id`, `external_service_id`, `estimated_usd`, `submitted_at`, `completed_at`, `result_expires_at`, `error` |

   `status` is `pending` -> `submitted` -> `completed`, or `failed` / `nsfw` / `canceled`.
   **Until a generation completes its media row has no file** (`cdn_url` is empty). Consumers that list media
   should show only `custom_properties.status = completed`, or filter on the `higgsfield` tag.
3. When Higgsfield finishes it calls the webhook
   `POST /public/flow/higgsfield/webhook/{generation_id}` (the media row's uuid). The signature (Standard Webhooks)
   is verified, then `HiggsfieldFinalizeJob` reads the final status from the API, downloads the file to our own
   storage (Higgsfield deletes its copies after at least 7 days) and fills the media row.
4. If no webhook arrives, `HiggsfieldPollJob` checks the status 90 s after submission and then at growing intervals
   (60 s up to 5 min, 26 checks, about two hours) and finishes the generation itself. After the deadline the
   generation is marked `failed` ("Timed out ...").

Both paths end in the same idempotent `finalize()`, which claims the row under a lock first, so a repeated
webhook or a poll racing the webhook stores the result only once.

## Setup

1. In the Higgsfield console create an API key (key id + secret) and top up the **API balance**. It is separate from
   the website plan: web credits do not pay for API calls.
2. Platform credentials go in the environment (see `config/higgsfield.php`):

   ```
   HIGGSFIELD_API_KEY_ID=
   HIGGSFIELD_API_KEY_SECRET=
   HIGGSFIELD_WEBHOOK_SECRET=     # whsec_..., console Settings; needed for the webhook, polling works without it
   # optional
   HIGGSFIELD_BASE_URL=https://api.higgsfield.ai
   HIGGSFIELD_STORAGE=            # disk name, 'local', or empty for the Commons default
   ```

   A pusher may instead use its own account's credentials: a Commons *external service* row with
   `configuration.key_id`, `token` = key secret and `refresh_token` = webhook secret (both encrypted on save), referenced
   by the optional `external_service_id`. Without `external_service_id` the platform credentials are used.
3. Create a pusher with provider `higgsfield` and attach it to a Flow automation. The pusher `url` is not used.

```json
{
  "provider": "higgsfield",
  "provider_metadata": {
    "external_service_id": "<optional: uuid of the account's own credentials>",
    "model_path": "higgsfield-ai/soul/standard",
    "media_type": "image",
    "prompt_template": "Product poster for {{ object.name }}, clean studio light",
    "params": { "aspect_ratio": "1:1", "resolution": "1080p" },
    "hourly_limit": 10,
    "max_cost_usd": "0.50",
    "daily_budget_usd": "20"
  }
}
```

`{{ path }}` placeholders are filled from the push payload: the item's own fields (`id`, `flow_stage_id`, ...) and
the linked object under `object` (for example `{{ object.name }}`). A missing path renders empty. They also work
inside `params`. A prompt that renders empty is refused.

`params` is passed to the model as is, so each model's own fields apply (for example `duration`, `image_urls`
for video models; see the Higgsfield model pages). `model_path` is restricted to a plain relative path on
`https://api.higgsfield.ai`.

## Cost guards

Every generation is paid from the Higgsfield balance, so before submitting:

- `hourly_limit` caps submissions per pusher per hour (default 10).
- `max_cost_usd` refuses a generation whose Higgsfield estimate is higher.
- `daily_budget_usd` refuses it when today's estimated spend of the account (tagged media rows, excluding failed /
  nsfw / canceled ones) plus this one would exceed the budget.
- If a budget is set and Higgsfield returns no estimate, the generation is refused, not run unchecked.

Failed, NSFW and canceled-before-start requests are not charged by Higgsfield.

## Failure modes

| situation | result |
|---|---|
| bad API key (401) / empty balance (403) | push fails with that code; the media row is marked `failed`; repeated permanent failures auto-disable the pusher |
| Higgsfield queue full (429) / 5xx / unreachable | push fails (502 / 429); retry the pusher log to resubmit |
| retried push of the same log | no second generation: the existing row is reused and Higgsfield's `Idempotency-Key` (the pusher log uuid) returns the original request |
| generation `failed` / `nsfw` / `canceled` | row stays as a record with `status` and `error`, no file |
| file cannot be stored | claim is released, the poller tries again |

## Files

- `src/Pushers/Drivers/HiggsfieldPusher.php` - the driver (registered in `FlowServiceProvider`)
- `src/Services/Higgsfield/HiggsfieldClient.php` - HTTP client (estimate, submit, status, cancel)
- `src/Services/HiggsfieldGenerationsService.php` - all writes and decisions, webhook signature check
- `src/Jobs/HiggsfieldPollJob.php`, `src/Jobs/HiggsfieldFinalizeJob.php` - queue `flow`
- host app: `App\Http\Controllers\Flow\HiggsfieldWebhookController` and the public route in `routes/api.php`
- Commons: `MediaService::createPending()` / `attachFile()`
