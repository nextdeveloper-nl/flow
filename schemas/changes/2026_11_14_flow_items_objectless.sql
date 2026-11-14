-- PostgreSQL — plusclouds/leo4#1243: cards without a linked object (custom pipelines).
-- Run once per environment. Idempotent. Existing rows are not changed.

-- 1. An item may have no object; then it carries a title.
ALTER TABLE flow_items ALTER COLUMN object_type DROP NOT NULL;
ALTER TABLE flow_items ALTER COLUMN object_id DROP NOT NULL;
ALTER TABLE flow_items ADD COLUMN IF NOT EXISTS title text;

COMMENT ON COLUMN flow_items.title IS 'Card title for items without a linked object (custom pipelines).';

-- 2. object_type and object_id are set together, and an object-less card needs a title.
DO $$ BEGIN
    ALTER TABLE flow_items ADD CONSTRAINT flow_items_object_pair_check
        CHECK ((object_type IS NULL) = (object_id IS NULL));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    ALTER TABLE flow_items ADD CONSTRAINT flow_items_object_or_title_check
        CHECK (object_id IS NOT NULL OR (title IS NOT NULL AND btrim(title) <> ''));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- 3. The existing UNIQUE (flow_pipeline_id, object_type, object_id) stays: Postgres treats NULLs as
--    distinct there, so any number of object-less cards fit, and one object still links at most once
--    per pipeline. No need to rebuild it as a partial index.

-- Rollback (only while no object-less card exists):
--   ALTER TABLE flow_items DROP CONSTRAINT IF EXISTS flow_items_object_or_title_check;
--   ALTER TABLE flow_items DROP CONSTRAINT IF EXISTS flow_items_object_pair_check;
--   ALTER TABLE flow_items ALTER COLUMN object_type SET NOT NULL;
--   ALTER TABLE flow_items ALTER COLUMN object_id SET NOT NULL;
--   ALTER TABLE flow_items DROP COLUMN IF EXISTS title;
--   then re-run the previous flow_items_perspective.sql.
