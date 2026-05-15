# NextDeveloper Flow

A generic, reusable pipeline engine for the LEO platform. Any object — a CRM opportunity,
a support ticket, a provisioning request — can be enrolled in a configurable workflow and
moved through ordered stages without touching the original table.

---

## Features

- **Polymorphic** — attach a pipeline to any platform object via `object_type` / `object_id`
- **Multi-pipeline** — the same object can participate in multiple pipelines simultaneously
- **Custom fields** — define pipeline-scoped columns (EAV) with typed validation
- **Stage entry requirements** — enforce that specific fields are filled before a stage transition
- **Checklists** — per-stage task lists tracked individually per item
- **SLA tracking** — configurable day limits per stage with breach detection
- **Automations** — fire named platform events on stage transitions, item creation, or SLA breach
- **Audit trail** — immutable stage history for every item
- **Watchers** — subscribe users to item updates
- **Templates** — define reusable pipeline blueprints and clone them per account

---

## Requirements

- PHP >= 8.2
- Laravel >= 10
- NextDeveloper Commons
- NextDeveloper IAM
- NextDeveloper Events

---

## Installation

Install via Composer:

```bash
composer require nextdeveloper/flow
```

Register the service provider (if not using auto-discovery):

```php
// config/app.php
'providers' => [
    NextDeveloper\Flow\FlowServiceProvider::class,
],
```

Publish the config file:

```bash
php artisan vendor:publish --provider="NextDeveloper\Flow\FlowServiceProvider" --tag="config"
```

Run the migrations (managed at the application level — see your platform's migration set for `flow_*` tables).

---

## Database Tables

| Table | Purpose |
|---|---|
| `flow_pipelines` | Pipeline definitions |
| `flow_stages` | Ordered stages per pipeline |
| `flow_columns` | Custom field definitions per pipeline |
| `flow_stage_required_columns` | Fields required before entering a stage |
| `flow_items` | Polymorphic object ↔ stage links |
| `flow_item_values` | EAV values for custom columns per item |
| `flow_stage_history` | Immutable audit log of all stage transitions |
| `flow_automations` | Event rules triggered on stage transitions |
| `flow_item_watchers` | Users following an item |

---

## Quick Start

### 1. Create a pipeline

```php
use NextDeveloper\Flow\Services\PipelinesService;

$pipeline = PipelinesService::create([
    'name'        => 'Sales Pipeline',
    'object_type' => 'crm_opportunity',
    'is_active'   => true,
]);
```

### 2. Add stages

```php
use NextDeveloper\Flow\Services\StagesService;

StagesService::create([
    'flow_pipeline_id' => $pipeline->uuid,
    'name'             => 'Lead',
    'position'         => 0,
    'color'            => '#94a3b8',
    'probability'      => 10,
    'sla_days'         => 7,
]);

StagesService::create([
    'flow_pipeline_id' => $pipeline->uuid,
    'name'             => 'Won',
    'position'         => 3,
    'color'            => '#22c55e',
    'probability'      => 100,
    'is_won'           => true,
]);
```

### 3. Enroll an object

```php
use NextDeveloper\Flow\Services\ItemsService;

$item = ItemsService::create([
    'flow_pipeline_id' => $pipeline->uuid,
    'flow_stage_id'    => $leadStage->uuid,
    'object_type'      => 'crm_opportunity',
    'object_id'        => $opportunity->id,
]);
// Initial stage history entry is recorded automatically.
```

### 4. Move to the next stage

```php
// The service validates required columns, resets the checklist,
// records history, fires automations and notifies watchers.
ItemsService::update($item->uuid, [
    'flow_stage_id' => $qualifiedStage->uuid,
]);
```

### 5. Set a custom field value

```php
use NextDeveloper\Flow\Services\ItemValuesService;

ItemValuesService::upsert(
    itemUuid:   $item->uuid,
    columnUuid: $contractValueColumn->uuid,
    value:      '42500'
);
```

### 6. Clone a template pipeline

```php
$livePipeline = PipelinesService::cloneFromTemplate($templateUuid);
// Stages, columns, entry requirements and automations are all copied.
```

---

## API Endpoints

All endpoints are mounted under the `/flow` prefix. Every resource follows the same
REST pattern:

```
GET    /flow/{resource}              List (supports filters & pagination)
GET    /flow/{resource}/{uuid}       Show
POST   /flow/{resource}              Create
PATCH  /flow/{resource}/{uuid}       Update
DELETE /flow/{resource}/{uuid}       Delete
POST   /flow/{resource}/{uuid}/do/{action}  Execute a named action
```

**Resources:** `pipelines`, `stages`, `items`, `columns`, `item-values`,
`stage-required-columns`, `stage-history`, `automations`, `item-watchers`

Filter parameters are passed as query strings. Add `paginate=true` for paginated
responses (`per_page` and `page` are also accepted).

For a full endpoint reference including request bodies and response shapes, see
[docs/UI.md](docs/UI.md).

---

## Stage Move Business Logic

When `flow_stage_id` is updated on an item the following happens automatically:

1. **Validate** — checks `flow_stage_required_columns` for the target stage. If any
   required column has no value in `flow_item_values`, the update is rejected with a
   `NotAllowedException` listing the missing column IDs.
2. **Reset checklist** — `checklist_state` is set to `null` so the new stage's
   checklist starts fresh.
3. **Timestamp** — `last_stage_changed_at` is set to now.
4. **History** — an entry is appended to `flow_stage_history`.
5. **Automations** — active `stage_exited` automations for the old stage and
   `stage_entered` automations for the new stage are fired via the Events system.
6. **Watchers** — a `item_stage_changed:NextDeveloper\Flow\Items` event is fired for
   any registered listeners to notify watchers.

---

## Automation Triggers

| Trigger | When it fires |
|---|---|
| `item_created` | A new item is added to a pipeline |
| `stage_entered` | An item moves into a stage |
| `stage_exited` | An item moves out of a stage |
| `sla_breached` | An item has exceeded `sla_days` in its current stage |

Set `flow_stage_id` to `null` on an automation to make it fire for all stage transitions
in the pipeline.

---

## SLA Detection

Dispatch `CheckSlaBreachesJob` on a schedule (recommended: hourly) to detect and fire
`sla_breached` automations:

```php
// app/Console/Kernel.php
$schedule->job(new \NextDeveloper\Flow\Jobs\CheckSlaBreachesJob)->hourly();
```

The job finds all items where time in stage exceeds the stage's `sla_days` and fires
the matching automations. Stages with `is_won = true` or `is_lost = true` are excluded.

---

## Custom Field Types

| `field_type` | Expected value format |
|---|---|
| `text` | Plain string |
| `number` | Numeric string, e.g. `"42500"` |
| `date` | ISO 8601, e.g. `"2026-07-01"` |
| `boolean` | `"true"` or `"false"` |
| `select` | One of the strings in column `options` |
| `multi_select` | JSON array string, e.g. `'["High","Urgent"]'` |
| `json` | Any valid JSON string |

All values are stored as `TEXT`. The `field_type` on `flow_columns` is the contract for
how consumers should cast and validate them.

---

## Configuration

After publishing, `config/flow.php` accepts global and per-model scope classes:

```php
return [
    'scopes' => [
        'global'      => [],         // Applied to every flow model
        'flow_items'  => [],         // Applied to Items only
        // flow_pipelines, flow_stages, flow_columns, ...
    ],
];
```

To enable or disable the module's routes:

```php
// config/leo.php
'allowed_routes' => [
    'flow' => true,
],
```

---

## Documentation

| File | Contents |
|---|---|
| [docs/Module.md](docs/Module.md) | Architecture overview, data model, lifecycle queries |
| [docs/UI.md](docs/UI.md) | UI development guide — screens, API reference, interaction flows |

---

## License

MIT — see [LICENSE](LICENSE).

---

## Support

For questions, issues, or enterprise support:

**Email:** support@plusclouds.com

Please include your platform version, PHP version, and a description of the issue.


---

## Our Libraries

This library is part of the **NextDeveloper / PlusClouds open-source ecosystem**. Browse all available libraries and find the right building blocks for your next project:

[https://plusclouds.com/us/solutions/libraries](https://plusclouds.com/us/solutions/libraries)

---

## Join the Community

We believe great software is built together. The PlusClouds developer community is a place where engineers share ideas, ask questions, showcase what they have built, and help shape the direction of these libraries. Whether you are integrating a single package or building an entire platform on top of our stack, you are very welcome here.

Come and join us — we would love to see what you build:

[https://plusclouds.com/us/community](https://plusclouds.com/us/community)
