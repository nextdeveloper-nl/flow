-- PostgreSQL — plusclouds/leo4#1241: what a pipeline is for, independent of its campaign.
-- Run once per environment. Idempotent.

-- 1. Column. 'custom' is the default for anything without a known purpose.
ALTER TABLE flow_pipelines ADD COLUMN IF NOT EXISTS kind text NOT NULL DEFAULT 'custom';

DO $$ BEGIN
    ALTER TABLE flow_pipelines ADD CONSTRAINT flow_pipelines_kind_check
        CHECK (kind IN ('sales', 'marketing', 'project', 'custom'));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

COMMENT ON COLUMN flow_pipelines.kind IS
    'What the pipeline is for: sales | marketing | project | custom. Not the item type (see object_type).';

CREATE INDEX IF NOT EXISTS idx_flow_pipelines_kind ON flow_pipelines USING btree (kind);

-- 2. Backfill from the live campaign behind the pipeline, linked either way round
--    (flow_pipelines.object_id → crm_campaigns.id, or crm_campaigns.flow_pipeline_id → flow_pipelines.id).
--    Pipelines without a live sales/marketing campaign keep 'custom'.
UPDATE flow_pipelines p
   SET kind = c.campaign_type
  FROM crm_campaigns c
 WHERE c.deleted_at IS NULL
   AND c.campaign_type IN ('sales', 'marketing')
   AND ((p.object_type LIKE '%Campaigns' AND p.object_id = c.id) OR c.flow_pipeline_id = p.id)
   AND p.kind = 'custom';

-- 3. One-off review on the shared PlusClouds DB (2026-11-14): pipelines with no live campaign were
--    classified by name and the list was approved before running. Ids are specific to that DB.
-- UPDATE flow_pipelines SET kind = 'marketing' WHERE id IN (6, 9, 10, 24, 26, 50) AND kind = 'custom';
-- UPDATE flow_pipelines SET kind = 'sales' WHERE id IN (1, 2, 13, 14, 16, 25, 27, 28, 31, 36, 37, 38, 39, 51, 52) AND kind = 'custom';
-- #33 "Support" stays custom.
