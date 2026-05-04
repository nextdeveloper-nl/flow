# Flow Module — UI Development Guide

This document is written for the engineer building the frontend UI for the Flow module.
It covers every screen, all API endpoints with exact request/response shapes, and the
business rules the UI must enforce.

---

## Table of Contents

- [What Is Flow?](#what-is-flow)
- [Core Concepts](#core-concepts)
- [Screens to Build](#screens-to-build)
- [API Reference](#api-reference)
  - [Conventions](#conventions)
  - [Pipelines](#pipelines)
  - [Stages](#stages)
  - [Items](#items)
  - [Columns](#columns)
  - [Item Values](#item-values)
  - [Stage Required Columns](#stage-required-columns)
  - [Stage History](#stage-history)
  - [Automations](#automations)
  - [Item Watchers](#item-watchers)
- [Business Rules the UI Must Enforce](#business-rules-the-ui-must-enforce)
- [Data Shapes (Response Objects)](#data-shapes-response-objects)
- [UI Interaction Flows](#ui-interaction-flows)

---

## What Is Flow?

Flow is a generic pipeline engine. Any object in the platform (a CRM opportunity,
a support ticket, a provisioning request, etc.) can be enrolled in a pipeline and
moved through ordered stages — without modifying the original object's table.

Think of it as a Kanban board engine that can be attached to anything.

---

## Core Concepts

| Term | What it is |
|---|---|
| **Pipeline** | A named board. Has ordered stages and optional custom fields. Can be a template or a live instance. |
| **Stage** | A column on the board. Has position, color, SLA, win/lost flag, and an optional checklist. |
| **Item** | A card on the board. Links any platform object to one stage within a pipeline. |
| **Column** | A custom field definition for a pipeline (e.g. "Contract Value", "Priority"). |
| **Item Value** | The actual value of a custom column for a specific item (EAV). |
| **Stage Required Columns** | Columns that must have a value before an item can enter a particular stage. |
| **Stage History** | Immutable audit log — every stage transition an item has gone through. |
| **Automation** | A rule that fires a named platform event when a trigger condition is met (stage entered/exited, item created, SLA breached). |
| **Item Watcher** | A user subscribed to updates on an item. |

---

## Screens to Build

### 1. Pipeline List
- Show all pipelines for the current account.
- Columns: Name, Object Type, Status (active/inactive), Template badge, Created At.
- Actions: Create, Edit, Delete, Clone from Template.
- Filter by: `name`, `object_type`, `is_active`, `is_template`.

### 2. Pipeline Builder (Settings)
Inside a pipeline, a settings view with tabs:

#### Tab: Stages
- Drag-and-drop to reorder stages (PATCH `position`).
- Create / edit / delete stages.
- Stage fields: Name, Color (hex picker), Probability (0–100 slider), SLA Days, Is Won toggle, Is Lost toggle.
- Checklist editor: add/remove/reorder checklist items per stage (key + label + required toggle). Stored as JSON array on the stage.
- At most one stage can be `is_won = true` and one `is_lost = true` — enforce this in the UI.

#### Tab: Custom Fields (Columns)
- Create / edit / delete custom columns for the pipeline.
- Column fields: Name (machine key), Label (display), Field Type (see types below), Default Value, Is Required, Position.
- For `select` and `multi_select` types: an options list editor (add/remove string options).
- Drag-and-drop to reorder columns.

**Column field types:**

| `field_type` | Input to render |
|---|---|
| `text` | Text input |
| `number` | Number input |
| `date` | Date picker |
| `boolean` | Toggle / checkbox |
| `select` | Dropdown (single) from `options` array |
| `multi_select` | Multi-select from `options` array |
| `json` | Textarea / code editor |

#### Tab: Stage Entry Requirements
- For each stage: a multi-select of columns that must be filled before an item can enter.
- Show as a matrix or per-stage accordion.
- Uses `POST /flow/stage-required-columns` and `DELETE /flow/stage-required-columns/{id}`.

#### Tab: Automations
- List automations for the pipeline.
- Fields: Trigger type, Stage (optional — "any stage" or a specific stage), Event Name, Active toggle.
- Trigger types: `stage_entered`, `stage_exited`, `item_created`, `sla_breached`.
- Payload Template: optional JSON editor for the event payload.

### 3. Pipeline Board (Kanban)
Main working view. Shows items grouped by stage as columns.

- Load stages: `GET /flow/stages?flow_pipeline_id={uuid}`
- Load items: `GET /flow/items?flow_pipeline_id={uuid}`
- Each card shows: linked object reference (`object_type` + `object_id`), assigned user, `last_stage_changed_at`, SLA breach indicator.

**SLA breach indicator:** if the stage has `sla_days` set and `last_stage_changed_at` is older than `sla_days` days, show a visual warning (red border, clock icon, etc.).

**Moving a card (stage transition):**
- User drags card from one stage column to another.
- Before moving: call validation (see [Stage Move Flow](#stage-move-flow)).
- On success: PATCH the item with the new `flow_stage_id`.
- On 422 error: show which required columns are missing values and open the item detail panel.

### 4. Item Detail Panel / Modal
Opens when clicking a card. Contains:

- **Header:** Object reference, assigned user selector, current stage badge.
- **Custom Fields section:** Show all columns for the pipeline. Each field is editable inline. Save via `ItemValues` upsert.
- **Checklist section:** If the current stage has a `checklist` array, render each item as a checkbox. Read state from `item.checklist_state`. On toggle: PATCH the item with updated `checklist_state`.
- **Stage History tab:** Timeline of all stage transitions (from → to, who moved it, when).
- **Watchers tab:** List of watchers. Add / remove watcher.

### 5. Template Management
- Separate list of pipelines where `is_template = true`.
- "Clone to account" action: triggers `PipelinesService::cloneFromTemplate()` (no dedicated endpoint yet — this may need a custom action or be handled by a backend action endpoint).

---

## API Reference

### Conventions

- **Base path:** `/flow` (all endpoints are under this prefix)
- **All IDs are UUIDs.** The API returns and accepts UUIDs, never integer IDs.
- **Filtering:** Pass filter fields as query parameters on list endpoints.
  - Example: `GET /flow/items?flow_pipeline_id=<uuid>&flow_stage_id=<uuid>`
- **Pagination:** Add `paginate=true` query param to get paginated results.
  - Optional: `per_page=20&page=1`
- **Soft deletes:** Deleted records are excluded from results automatically.
- **Authentication:** Standard platform Bearer token — include `Authorization: Bearer {token}` on all requests.

---

### Pipelines

#### List pipelines
```
GET /flow/pipelines
```
Query filters: `name`, `object_type`, `is_template`, `is_system`, `is_active`,
`iam_account_id`, `iam_user_id`, `created_at_start`, `created_at_end`

#### Get single pipeline
```
GET /flow/pipelines/{uuid}
```

#### Create pipeline
```
POST /flow/pipelines
Content-Type: application/json

{
  "name": "Sales Pipeline",
  "description": "Main sales process",
  "object_type": "crm_opportunity",
  "is_template": false,
  "is_system": false,
  "is_active": true
}
```
`iam_account_id` and `iam_user_id` are set automatically from the session.

#### Update pipeline
```
PATCH /flow/pipelines/{uuid}
Content-Type: application/json

{
  "name": "Updated Name",
  "is_active": false
}
```

#### Delete pipeline
```
DELETE /flow/pipelines/{uuid}
```

---

### Stages

#### List stages
```
GET /flow/stages?flow_pipeline_id={uuid}
```
Query filters: `flow_pipeline_id`, `name`, `is_won`, `is_lost`, `is_active`
Sort by `position` to get them in order.

#### Get single stage
```
GET /flow/stages/{uuid}
```

#### Create stage
```
POST /flow/stages
Content-Type: application/json

{
  "flow_pipeline_id": "<pipeline-uuid>",
  "name": "Qualified",
  "color": "#60a5fa",
  "position": 1,
  "probability": 30,
  "sla_days": 14,
  "is_won": false,
  "is_lost": false,
  "is_active": true,
  "checklist": [
    { "key": "budget_confirmed", "label": "Budget confirmed", "required": true },
    { "key": "decision_maker_identified", "label": "Decision maker identified", "required": false }
  ]
}
```

`checklist` is optional. When provided it must be a JSON array of objects with `key`, `label`, and `required`.

#### Update stage
```
PATCH /flow/stages/{uuid}
Content-Type: application/json

{
  "position": 2,
  "sla_days": 7
}
```

#### Delete stage
```
DELETE /flow/stages/{uuid}
```

---

### Items

#### List items
```
GET /flow/items?flow_pipeline_id={uuid}
```
Query filters: `flow_pipeline_id`, `flow_stage_id`, `object_type`, `object_id`,
`assigned_iam_user_id`, `iam_account_id`, `iam_user_id`

#### Get single item
```
GET /flow/items/{uuid}
```

#### Add an object to a pipeline (create item)
```
POST /flow/items
Content-Type: application/json

{
  "flow_pipeline_id": "<pipeline-uuid>",
  "flow_stage_id": "<first-stage-uuid>",
  "object_type": "crm_opportunity",
  "object_id": 42,
  "assigned_iam_user_id": "<user-uuid>",
  "position": 0
}
```
The backend automatically records the initial stage history entry and fires `item_created` automations.

#### Move item to a new stage
```
PATCH /flow/items/{uuid}
Content-Type: application/json

{
  "flow_stage_id": "<target-stage-uuid>"
}
```

**This is the stage move operation.** The backend:
1. Validates all required columns for the target stage have values.
2. Resets `checklist_state` to null (new stage's checklist starts fresh).
3. Sets `last_stage_changed_at` to now.
4. Records a history entry.
5. Fires `stage_exited` and `stage_entered` automations.
6. Notifies watchers.

**On validation failure (missing required columns):**
- HTTP `422 Unprocessable Entity`
- Response body contains which column IDs are missing.
- The UI must show the user which fields to fill in before the move is allowed.

#### Update checklist state (mark item complete/incomplete)
```
PATCH /flow/items/{uuid}
Content-Type: application/json

{
  "checklist_state": {
    "budget_confirmed": {
      "completed": true,
      "completed_by": "<user-uuid>",
      "completed_at": "2026-05-04T10:00:00Z"
    },
    "decision_maker_identified": {
      "completed": false,
      "completed_by": null,
      "completed_at": null
    }
  }
}
```
Send the full updated `checklist_state` object. The checklist definition (keys and labels) comes from the item's current `stage.checklist`.

#### Update assigned user
```
PATCH /flow/items/{uuid}
Content-Type: application/json

{
  "assigned_iam_user_id": "<user-uuid>"
}
```

#### Remove item from pipeline
```
DELETE /flow/items/{uuid}
```

---

### Columns

#### List columns for a pipeline
```
GET /flow/columns?flow_pipeline_id={uuid}
```

#### Create column
```
POST /flow/columns
Content-Type: application/json

{
  "flow_pipeline_id": "<pipeline-uuid>",
  "name": "contract_value",
  "label": "Contract Value",
  "field_type": "number",
  "default_value": null,
  "is_required": false,
  "position": 1,
  "is_active": true
}
```

For `select` / `multi_select` types, include `options`:
```json
{
  "flow_pipeline_id": "<pipeline-uuid>",
  "name": "priority",
  "label": "Priority",
  "field_type": "select",
  "options": ["High", "Medium", "Low"],
  "is_required": false,
  "position": 3,
  "is_active": true
}
```

#### Update column
```
PATCH /flow/columns/{uuid}
Content-Type: application/json

{
  "label": "Deal Value",
  "position": 0
}
```

#### Delete column
```
DELETE /flow/columns/{uuid}
```

---

### Item Values

Item values store the custom column data for an item (EAV pattern). All values are stored as strings regardless of the column's `field_type` — the UI is responsible for casting when rendering.

#### List values for an item
```
GET /flow/item-values?flow_item_id={uuid}
```

#### Set (upsert) a value
```
POST /flow/item-values
Content-Type: application/json

{
  "flow_item_id": "<item-uuid>",
  "flow_column_id": "<column-uuid>",
  "value": "42500"
}
```
If a value already exists for this `(item, column)` pair it is updated. There is no separate PATCH needed — always POST to set a value.

#### Delete a value
```
DELETE /flow/item-values/{uuid}
```

**Value encoding by field type:**

| `field_type` | How to encode `value` |
|---|---|
| `text` | Plain string: `"Acme Corp"` |
| `number` | Numeric string: `"42500"` |
| `date` | ISO 8601 string: `"2026-07-01"` |
| `boolean` | `"true"` or `"false"` |
| `select` | One of the strings in column `options`: `"High"` |
| `multi_select` | JSON array string: `'["High","Urgent"]'` |
| `json` | Any valid JSON string |

---

### Stage Required Columns

#### List required columns for a stage
```
GET /flow/stage-required-columns?flow_stage_id={uuid}
```

#### Add a required column to a stage
```
POST /flow/stage-required-columns
Content-Type: application/json

{
  "flow_stage_id": "<stage-uuid>",
  "flow_column_id": "<column-uuid>"
}
```

#### Remove a required column from a stage
```
DELETE /flow/stage-required-columns/{uuid}
```

---

### Stage History

Read-only from the UI perspective. The backend writes all history entries automatically.

#### Get history for an item
```
GET /flow/stage-history?flow_item_id={uuid}
```

**Response fields:** `id`, `flow_item_id`, `flow_pipeline_id`, `from_stage_id` (null for first entry), `to_stage_id`, `moved_by_iam_user_id`, `moved_at`

Render as a timeline sorted by `moved_at` ascending. The first entry has `from_stage_id = null` (initial placement).

---

### Automations

#### List automations for a pipeline
```
GET /flow/automations?flow_pipeline_id={uuid}
```

#### Create automation
```
POST /flow/automations
Content-Type: application/json

{
  "flow_pipeline_id": "<pipeline-uuid>",
  "flow_stage_id": "<stage-uuid-or-null>",
  "trigger": "stage_entered",
  "event_name": "crm.opportunity.qualified",
  "payload_template": {
    "source": "flow",
    "pipeline": "{{pipeline_id}}"
  },
  "is_active": true
}
```

`flow_stage_id` is optional. If null, the automation fires for all stages on this trigger.

**Trigger values:** `stage_entered`, `stage_exited`, `item_created`, `sla_breached`

#### Update automation
```
PATCH /flow/automations/{uuid}
Content-Type: application/json

{
  "is_active": false
}
```

#### Delete automation
```
DELETE /flow/automations/{uuid}
```

---

### Item Watchers

#### List watchers for an item
```
GET /flow/item-watchers?flow_item_id={uuid}
```

#### Add a watcher
```
POST /flow/item-watchers
Content-Type: application/json

{
  "flow_item_id": "<item-uuid>",
  "iam_user_id": "<user-uuid>"
}
```

#### Remove a watcher
```
DELETE /flow/item-watchers/{uuid}
```

---

## Business Rules the UI Must Enforce

### Stage Move Validation
Before moving an item to a new stage the UI should optimistically attempt the PATCH.
If the API returns `422`, parse the error to find which column IDs are missing and show
the user a dialog listing the required fields that still need values. The user must fill
them in before the move is allowed.

Do NOT block the drag-and-drop in the UI ahead of time — always try the API call and
handle the 422 response. This keeps client-side logic simple and keeps validation
server-authoritative.

### One Won Stage, One Lost Stage
When building or editing stages in the Pipeline Builder, ensure:
- At most one stage has `is_won = true`. Toggling one on should automatically toggle
  any previously-won stage off (or prompt the user to confirm).
- At most one stage has `is_lost = true`. Same rule.

### Checklist Reset on Stage Move
When an item successfully moves to a new stage, its `checklist_state` is reset by the
backend. The UI should refresh the item after a successful stage move and re-render the
checklist from the new stage's `checklist` definition.

### SLA Breach Display
For each item on the board, check: does `now() - last_stage_changed_at > stage.sla_days`?
- If yes: render a visual warning on the card (red border, `!` badge, tooltip with time
  elapsed vs. SLA limit).
- If `sla_days` is null on the stage: no SLA applies.
- Stages with `is_won = true` or `is_lost = true` never show SLA warnings.

### Checklist Completion
The checklist definition is on `stage.checklist` (array of `{ key, label, required }`).
The completion state is on `item.checklist_state` (map of `key → { completed, completed_by, completed_at }`).
- Render each checklist item as a checkbox.
- On toggle: build the updated `checklist_state` and PATCH the item.
- If a checklist item has `required: true`, you may choose to block stage transitions
  visually (though this is not enforced by the backend — stage entry requirements via
  `stage_required_columns` are separate from checklists).

### Template Pipelines
Pipelines with `is_template = true` should be shown in a separate "Templates" section
and not in the main board list. Cloning a template creates a fully independent live
pipeline — after cloning, changes to the live pipeline do not affect the template.

---

## Data Shapes (Response Objects)

All responses wrap data in a standard envelope. Individual items are under `data`,
collections are under `data` as an array.

### Pipeline object
```json
{
  "id": "<uuid>",
  "name": "Sales Pipeline",
  "description": "Main sales process",
  "object_type": "crm_opportunity",
  "is_template": false,
  "is_system": false,
  "is_active": true,
  "iam_account_id": "<uuid>",
  "iam_user_id": "<uuid>",
  "created_at": "2026-05-01T10:00:00Z",
  "updated_at": "2026-05-01T10:00:00Z",
  "deleted_at": null
}
```

### Stage object
```json
{
  "id": "<uuid>",
  "flow_pipeline_id": "<uuid>",
  "name": "Qualified",
  "color": "#60a5fa",
  "position": 1,
  "probability": 30,
  "sla_days": 14,
  "is_won": false,
  "is_lost": false,
  "checklist": [
    { "key": "budget_confirmed", "label": "Budget confirmed", "required": true }
  ],
  "is_active": true,
  "created_at": "2026-05-01T10:00:00Z",
  "updated_at": "2026-05-01T10:00:00Z",
  "deleted_at": null
}
```

### Item object
```json
{
  "id": "<uuid>",
  "flow_pipeline_id": "<uuid>",
  "flow_stage_id": "<uuid>",
  "object_type": "crm_opportunity",
  "object_id": 42,
  "position": 0,
  "last_stage_changed_at": "2026-05-02T08:30:00Z",
  "checklist_state": {
    "budget_confirmed": {
      "completed": true,
      "completed_by": "<user-uuid>",
      "completed_at": "2026-05-03T09:00:00Z"
    }
  },
  "iam_account_id": "<uuid>",
  "iam_user_id": "<uuid>",
  "assigned_iam_user_id": "<uuid>",
  "created_at": "2026-05-01T10:00:00Z",
  "updated_at": "2026-05-03T09:00:00Z",
  "deleted_at": null
}
```

### Column object
```json
{
  "id": "<uuid>",
  "flow_pipeline_id": "<uuid>",
  "name": "contract_value",
  "label": "Contract Value",
  "field_type": "number",
  "options": null,
  "default_value": null,
  "is_required": false,
  "position": 1,
  "is_active": true,
  "created_at": "2026-05-01T10:00:00Z",
  "updated_at": "2026-05-01T10:00:00Z",
  "deleted_at": null
}
```

### Item Value object
```json
{
  "id": "<uuid>",
  "flow_item_id": "<uuid>",
  "flow_column_id": "<uuid>",
  "value": "42500",
  "created_at": "2026-05-02T10:00:00Z",
  "updated_at": "2026-05-03T09:00:00Z"
}
```

### Stage History object
```json
{
  "id": "<uuid>",
  "flow_item_id": "<uuid>",
  "flow_pipeline_id": "<uuid>",
  "from_stage_id": "<uuid-or-null>",
  "to_stage_id": "<uuid>",
  "moved_by_iam_user_id": "<uuid>",
  "moved_at": "2026-05-02T08:30:00Z"
}
```

### Automation object
```json
{
  "id": "<uuid>",
  "flow_pipeline_id": "<uuid>",
  "flow_stage_id": "<uuid-or-null>",
  "trigger": "stage_entered",
  "event_name": "crm.opportunity.qualified",
  "payload_template": { "source": "flow" },
  "is_active": true,
  "iam_account_id": "<uuid>",
  "created_at": "2026-05-01T10:00:00Z",
  "updated_at": "2026-05-01T10:00:00Z",
  "deleted_at": null
}
```

### Item Watcher object
```json
{
  "id": "<uuid>",
  "flow_item_id": "<uuid>",
  "iam_user_id": "<uuid>",
  "created_at": "2026-05-01T10:00:00Z"
}
```

---

## UI Interaction Flows

### Loading the Kanban Board
```
1. GET /flow/pipelines/{uuid}              → pipeline metadata
2. GET /flow/stages?flow_pipeline_id={uuid} → stage columns (sort by position)
3. GET /flow/items?flow_pipeline_id={uuid}  → all items (group by flow_stage_id)
4. GET /flow/columns?flow_pipeline_id={uuid} → column definitions (for item detail)
```

### Stage Move Flow
```
1. User drags item from Stage A → Stage B
2. PATCH /flow/items/{item-uuid}  { "flow_stage_id": "<stage-b-uuid>" }
   ├── 200 OK  → update item in local state, refresh checklist from new stage definition
   └── 422     → show "missing required fields" dialog
                  user fills fields via POST /flow/item-values (upsert)
                  retry PATCH /flow/items/{item-uuid}
```

### Opening Item Detail
```
1. GET /flow/items/{uuid}                          → item data
2. GET /flow/item-values?flow_item_id={uuid}       → current column values
3. GET /flow/stage-history?flow_item_id={uuid}     → transition timeline
4. GET /flow/item-watchers?flow_item_id={uuid}     → watcher list
   (columns already loaded at board level — reuse)
```

### Editing a Custom Field
```
1. User edits field value in detail panel
2. POST /flow/item-values
   { "flow_item_id": "<uuid>", "flow_column_id": "<uuid>", "value": "..." }
   → always POST (backend does upsert — no need for conditional PUT/PATCH)
```

### Building a Pipeline from a Template
```
1. GET /flow/pipelines?is_template=true            → list available templates
2. User selects a template and clicks "Use Template"
3. POST /flow/pipelines/{template-uuid}/do/clone   → triggers cloneFromTemplate action
   (or create pipeline + stages + columns manually if action endpoint is not wired)
4. Redirect to new pipeline's board
```

### Adding Stage Entry Requirements (UX pattern)
```
For each stage in the builder:
  - Show a multi-select of all pipeline columns
  - Pre-select those already in stage-required-columns for this stage
  - On add:    POST /flow/stage-required-columns { flow_stage_id, flow_column_id }
  - On remove: DELETE /flow/stage-required-columns/{uuid}
```
