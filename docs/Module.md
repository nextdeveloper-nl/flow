# Flow Module

A generic, reusable pipeline engine. Any object in the LEO platform (CRM opportunity, support ticket, IAAS provisioning request, etc.) can be placed into a flow and moved through configurable stages — without touching the original table.

---

## Table of Contents

- [Core Concepts](#core-concepts)
- [Table Reference](#table-reference)
- [Polymorphic Linking](#polymorphic-linking)
- [Lifecycle: Creating a Pipeline](#lifecycle-creating-a-pipeline)
- [Lifecycle: Moving an Item Through Stages](#lifecycle-moving-an-item-through-stages)
- [Custom Columns (EAV)](#custom-columns-eav)
- [Checklists](#checklists)
- [Stage Entry Requirements](#stage-entry-requirements)
- [SLA / Stale Detection](#sla--stale-detection)
- [Automations](#automations)
- [Templates](#templates)
- [Watchers](#watchers)
- [Key Queries](#key-queries)

---

## Core Concepts

| Concept | Description |
|---|---|
| **Pipeline** | A named workflow definition with an ordered set of stages and optional custom fields |
| **Stage** | A step in the pipeline (e.g. Lead → Qualified → Proposal → Won). Ordered by `position` |
| **Item** | A link record that places any platform object into a pipeline at a given stage |
| **Column** | A custom field definition scoped to a pipeline (e.g. "Contract Value", "Decision Maker") |
| **Value** | The actual data stored for a custom column on a specific item (EAV pattern) |
| **Automation** | A rule that fires a named event into the events system when a pipeline transition occurs |

---

## Table Reference

```
flow_pipelines               — pipeline definitions
flow_stages                  — ordered stages per pipeline
flow_columns                 — custom field definitions per pipeline
flow_stage_required_columns  — fields that must be filled before entering a stage
flow_items                   — polymorphic object ↔ stage links
flow_item_values             — EAV: custom column values per item
flow_stage_history           — immutable audit log of all stage transitions
flow_automations             — event rules triggered on stage transitions
flow_item_watchers           — users following an item
```

---

## Polymorphic Linking

The flow module does not modify any existing table. Instead, `flow_items` uses the platform-standard polymorphic pattern:

```
object_type  TEXT   — e.g. 'crm_opportunity', 'support_ticket', 'iaas_virtual_machine'
object_id    BIGINT — the primary key of the record in that table
```

A UNIQUE constraint on `(flow_pipeline_id, object_type, object_id)` guarantees that one object occupies exactly one stage within a given pipeline, while still allowing the same object to participate in multiple different pipelines simultaneously.

> There are no database-level foreign keys on `object_id`. Referential integrity is the responsibility of the application layer.

---

## Lifecycle: Creating a Pipeline

### 1. Create the pipeline

```sql
INSERT INTO flow_pipelines (name, object_type, iam_account_id, iam_user_id)
VALUES ('Sales Pipeline', 'crm_opportunity', :account_id, :user_id)
RETURNING id;
```

Use `object_type` as an advisory hint so the API knows which objects belong in this pipeline. It is not enforced at the DB level.

### 2. Define stages

```sql
INSERT INTO flow_stages (flow_pipeline_id, name, position, color, probability, sla_days, is_won, is_lost)
VALUES
    (:pipeline_id, 'Lead',      0, '#94a3b8', 10,  7,    false, false),
    (:pipeline_id, 'Qualified', 1, '#60a5fa', 30,  14,   false, false),
    (:pipeline_id, 'Proposal',  2, '#f59e0b', 60,  10,   false, false),
    (:pipeline_id, 'Won',       3, '#22c55e', 100, NULL, true,  false),
    (:pipeline_id, 'Lost',      4, '#ef4444', 0,   NULL, false, true);
```

Each pipeline must have at most one `is_won = true` and one `is_lost = true` stage.

### 3. Define custom columns (optional)

```sql
INSERT INTO flow_columns (flow_pipeline_id, name, label, field_type, is_required, position)
VALUES
    (:pipeline_id, 'decision_maker', 'Decision Maker', 'text',   false, 0),
    (:pipeline_id, 'contract_value', 'Contract Value', 'number', false, 1),
    (:pipeline_id, 'close_date',     'Expected Close', 'date',   false, 2),
    (:pipeline_id, 'priority',       'Priority',       'select', false, 3);

-- For the select column, set options:
UPDATE flow_columns
SET options = '["High", "Medium", "Low"]'
WHERE name = 'priority' AND flow_pipeline_id = :pipeline_id;
```

### 4. Set stage entry requirements (optional)

```sql
-- 'close_date' must be filled before an item can enter Proposal stage
INSERT INTO flow_stage_required_columns (flow_stage_id, flow_column_id)
VALUES (:proposal_stage_id, :close_date_column_id);
```

---

## Lifecycle: Moving an Item Through Stages

### Adding an object to a pipeline

```sql
INSERT INTO flow_items (
    flow_pipeline_id, flow_stage_id,
    object_type, object_id,
    iam_account_id, iam_user_id, assigned_iam_user_id
)
VALUES (
    :pipeline_id, :first_stage_id,
    'crm_opportunity', :opportunity_id,
    :account_id, :user_id, :assigned_user_id
)
RETURNING id;
```

Record the initial entry in history (`from_stage_id` is NULL for the first placement):

```sql
INSERT INTO flow_stage_history (flow_item_id, flow_pipeline_id, from_stage_id, to_stage_id, moved_by_iam_user_id)
VALUES (:item_id, :pipeline_id, NULL, :first_stage_id, :user_id);
```

### Before moving to a new stage — validate requirements

```sql
-- Find required columns for the target stage that have no value on this item
SELECT fc.name, fc.label
FROM flow_stage_required_columns fsr
JOIN flow_columns fc ON fc.id = fsr.flow_column_id
WHERE fsr.flow_stage_id = :target_stage_id
  AND NOT EXISTS (
      SELECT 1 FROM flow_item_values
      WHERE flow_item_id  = :item_id
        AND flow_column_id = fsr.flow_column_id
        AND value IS NOT NULL
  );
```

If this returns any rows, reject the move and return the missing fields to the client.

### Executing the stage move

```sql
-- 1. Update the item
UPDATE flow_items
SET flow_stage_id         = :new_stage_id,
    last_stage_changed_at = NOW(),
    checklist_state       = NULL,
    updated_at            = NOW()
WHERE id = :item_id;

-- 2. Append to history
INSERT INTO flow_stage_history (flow_item_id, flow_pipeline_id, from_stage_id, to_stage_id, moved_by_iam_user_id)
VALUES (:item_id, :pipeline_id, :old_stage_id, :new_stage_id, :user_id);

-- 3. Fire automations (see Automations section)
```

---

## Custom Columns (EAV)

Custom column values are stored as `TEXT` in `flow_item_values`. The `field_type` on `flow_columns` is the contract for how the API should cast and validate values.

| `field_type` | Expected `value` format |
|---|---|
| `text` | Plain string |
| `number` | Numeric string, e.g. `"42500"` |
| `date` | ISO 8601, e.g. `"2026-07-01"` |
| `boolean` | `"true"` or `"false"` |
| `select` | One of the strings in `options` |
| `multi_select` | JSON array string, e.g. `'["High","Urgent"]'` |
| `json` | Any valid JSON string |

### Writing a value

```sql
INSERT INTO flow_item_values (flow_item_id, flow_column_id, value)
VALUES (:item_id, :column_id, :value)
ON CONFLICT (flow_item_id, flow_column_id)
DO UPDATE SET value = EXCLUDED.value, updated_at = NOW();
```

### Reading all values for an item

```sql
SELECT fc.name, fc.label, fc.field_type, fiv.value
FROM flow_columns fc
LEFT JOIN flow_item_values fiv
    ON fiv.flow_column_id = fc.id
    AND fiv.flow_item_id  = :item_id
WHERE fc.flow_pipeline_id = :pipeline_id
  AND fc.is_active = true
ORDER BY fc.position;
```

---

## Checklists

The checklist for a stage is defined as a JSON array on `flow_stages.checklist`:

```json
[
  { "key": "proposal_sent",    "label": "Proposal sent to client",  "required": true  },
  { "key": "budget_confirmed", "label": "Budget confirmed",         "required": true  },
  { "key": "legal_reviewed",   "label": "Legal review completed",   "required": false }
]
```

Completion state is tracked per item on `flow_items.checklist_state`:

```json
{
  "proposal_sent":    { "completed": true,  "completed_by": 42, "completed_at": "2026-05-04T10:00:00Z" },
  "budget_confirmed": { "completed": false, "completed_by": null, "completed_at": null },
  "legal_reviewed":   { "completed": false, "completed_by": null, "completed_at": null }
}
```

### Marking a checklist item complete

```sql
UPDATE flow_items
SET checklist_state = jsonb_set(
    COALESCE(checklist_state::jsonb, '{}'::jsonb),
    ARRAY[:key],
    jsonb_build_object(
        'completed',    true,
        'completed_by', :user_id,
        'completed_at', NOW()
    )
),
updated_at = NOW()
WHERE id = :item_id;
```

> When an item moves to a new stage, reset `checklist_state = NULL` so the new stage's checklist starts fresh.

---

## Stage Entry Requirements

Before allowing a stage transition, the API must query `flow_stage_required_columns` for the target stage and verify all referenced `flow_columns` have a non-null value in `flow_item_values` for the given item. See the validation query in the [Lifecycle](#lifecycle-moving-an-item-through-stages) section.

---

## SLA / Stale Detection

`flow_stages.sla_days` defines the maximum number of days an item should stay in that stage. Use `flow_items.last_stage_changed_at` to detect breaches:

```sql
SELECT fi.id, fi.object_type, fi.object_id, fs.name AS stage, fs.sla_days,
       NOW() - fi.last_stage_changed_at AS time_in_stage
FROM flow_items fi
JOIN flow_stages fs ON fs.id = fi.flow_stage_id
WHERE fs.sla_days IS NOT NULL
  AND fs.is_won = false
  AND fs.is_lost = false
  AND fi.flow_pipeline_id = :pipeline_id
  AND fi.deleted_at IS NULL
  AND (NOW() - fi.last_stage_changed_at) > (fs.sla_days || ' days')::interval;
```

The `sla_breached` automation trigger should be evaluated by a scheduled job running this query and firing the relevant events.

---

## Automations

Automations declare which event to fire into the LEO events system when a pipeline transition occurs. The events system is responsible for all execution — the flow module only fires the event name with a payload.

### Trigger types

| Trigger | Fires when |
|---|---|
| `stage_entered` | An item moves into `flow_stage_id` |
| `stage_exited` | An item moves out of `flow_stage_id` |
| `item_created` | A new item is added to the pipeline (any stage) |
| `sla_breached` | An item has exceeded `sla_days` in its current stage |

`flow_stage_id` can be NULL, meaning the automation fires for all stage transitions in the pipeline.

### Fetching automations to fire after a stage move

```sql
SELECT event_name, payload_template
FROM flow_automations
WHERE flow_pipeline_id = :pipeline_id
  AND is_active = true
  AND deleted_at IS NULL
  AND trigger IN ('stage_entered', 'stage_exited')
  AND (flow_stage_id IS NULL
       OR (trigger = 'stage_entered' AND flow_stage_id = :new_stage_id)
       OR (trigger = 'stage_exited'  AND flow_stage_id = :old_stage_id));
```

The API merges `payload_template` with the runtime context (item id, object type, object id, user id, account id) and dispatches the event.

---

## Templates

A pipeline with `is_template = true` is a reusable blueprint. Set `is_system = true` for platform-level templates that are not owned by any account.

To clone a template into a live pipeline for an account:

1. `INSERT INTO flow_pipelines` copying the template row with `is_template = false`, `iam_account_id = :account_id`
2. `INSERT INTO flow_stages` for each stage in the template
3. `INSERT INTO flow_columns` for each column in the template
4. Recreate `flow_stage_required_columns` and `flow_automations` referencing the new stage/column ids

This is an application-level operation — no DB function required.

---

## Watchers

Users subscribed to a flow item receive notifications on stage changes and SLA breaches. After firing automations, the API should notify watchers:

```sql
SELECT iam_user_id
FROM flow_item_watchers
WHERE flow_item_id = :item_id;
```

Use the `common_pushers` mechanism to deliver the notification to each returned user.

---

## Key Queries

### Get all items in a pipeline, grouped by stage

```sql
SELECT fs.name AS stage, fs.position, fi.id, fi.object_type, fi.object_id,
       fi.assigned_iam_user_id, fi.last_stage_changed_at
FROM flow_stages fs
LEFT JOIN flow_items fi
    ON fi.flow_stage_id = fs.id AND fi.deleted_at IS NULL
WHERE fs.flow_pipeline_id = :pipeline_id
  AND fs.deleted_at IS NULL
ORDER BY fs.position, fi.position;
```

### Get full stage history for an item

```sql
SELECT
    from_s.name AS from_stage,
    to_s.name   AS to_stage,
    fsh.moved_by_iam_user_id,
    fsh.moved_at
FROM flow_stage_history fsh
LEFT JOIN flow_stages from_s ON from_s.id = fsh.from_stage_id
JOIN  flow_stages to_s   ON to_s.id   = fsh.to_stage_id
WHERE fsh.flow_item_id = :item_id
ORDER BY fsh.moved_at;
```

### Find which pipeline(s) a specific object is in

```sql
SELECT fp.id, fp.name, fs.name AS current_stage
FROM flow_items fi
JOIN flow_pipelines fp ON fp.id = fi.flow_pipeline_id
JOIN flow_stages    fs ON fs.id = fi.flow_stage_id
WHERE fi.object_type = 'crm_opportunity'
  AND fi.object_id   = :opportunity_id
  AND fi.deleted_at  IS NULL;
```

### Conversion rate between two stages

```sql
SELECT
    COUNT(DISTINCT CASE WHEN to_stage_id = :from_stage_id THEN flow_item_id END) AS entered,
    COUNT(DISTINCT CASE WHEN to_stage_id = :to_stage_id   THEN flow_item_id END) AS converted
FROM flow_stage_history
WHERE flow_pipeline_id = :pipeline_id;
```
