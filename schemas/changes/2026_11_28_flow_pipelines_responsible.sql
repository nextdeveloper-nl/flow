-- PostgreSQL — plusclouds/leo4#1399: a responsible person per pipeline, separate from the creator.
-- Run once per environment. Idempotent. Existing rows are not changed (responsible stays NULL).
--
-- flow_pipelines.iam_user_id stays the creator: flow-user's update/delete policy treats it as the
-- owner, so reassigning it would move edit rights and lose who created the pipeline.

-- 1. Column. Must be a member of the pipeline's account; PipelinesService checks that on write.
ALTER TABLE flow_pipelines ADD COLUMN IF NOT EXISTS responsible_iam_user_id bigint;

DO $$ BEGIN
    ALTER TABLE flow_pipelines ADD CONSTRAINT flow_pipelines_responsible_iam_user_id_fkey
        FOREIGN KEY (responsible_iam_user_id) REFERENCES iam_users (id) ON DELETE SET NULL;
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

COMMENT ON COLUMN flow_pipelines.responsible_iam_user_id IS
    'The person responsible for the pipeline (a member of iam_account_id). Not the creator (see iam_user_id).';

CREATE INDEX IF NOT EXISTS idx_flow_pipelines_responsible ON flow_pipelines USING btree (responsible_iam_user_id);

-- 2. The perspective exposes the responsible person and their name (new columns at the end).
CREATE OR REPLACE VIEW flow_pipelines_perspective AS
SELECT fp.id,
    fp.uuid,
    fp.name,
    fp.description,
    fp.object_type,
    fp.object_id,
    fp.is_template,
    fp.is_system,
    fp.is_active,
    fp.iam_account_id,
    fp.iam_user_id,
    fp.created_at,
    fp.updated_at,
    fp.deleted_at,
    COALESCE(sc.stage_count, 0::bigint) AS stage_count,
    COALESCE(ic.active_item_count, 0::bigint) AS active_item_count,
    COALESCE(ic.won_item_count, 0::bigint) AS won_item_count,
    COALESCE(ic.lost_item_count, 0::bigint) AS lost_item_count,
    fp.kind,
    camp.uuid AS campaign_id,
    camp.campaign_type,
    fp.responsible_iam_user_id,
    ru.fullname AS responsible_name
   FROM flow_pipelines fp
     LEFT JOIN ( SELECT flow_stages.flow_pipeline_id,
            count(*) AS stage_count
           FROM flow_stages
          WHERE flow_stages.deleted_at IS NULL AND flow_stages.is_active = true
          GROUP BY flow_stages.flow_pipeline_id) sc ON sc.flow_pipeline_id = fp.id
     LEFT JOIN ( SELECT fi.flow_pipeline_id,
            count(*) AS active_item_count,
            count(*) FILTER (WHERE fs.is_won = true) AS won_item_count,
            count(*) FILTER (WHERE fs.is_lost = true) AS lost_item_count
           FROM flow_items fi
             JOIN flow_stages fs ON fs.id = fi.flow_stage_id
          WHERE fi.deleted_at IS NULL
          GROUP BY fi.flow_pipeline_id) ic ON ic.flow_pipeline_id = fp.id
     LEFT JOIN LATERAL ( SELECT c.uuid, c.campaign_type
           FROM crm_campaigns c
          WHERE c.deleted_at IS NULL
            AND ((fp.object_type LIKE '%Campaigns' AND fp.object_id = c.id) OR c.flow_pipeline_id = fp.id)
          ORDER BY c.id
         LIMIT 1) camp ON true
     LEFT JOIN iam_users ru ON ru.id = fp.responsible_iam_user_id;

-- Rollback:
--   (re-run schemas/flow_pipelines_perspective.sql from before this change, after DROP VIEW flow_pipelines_perspective)
--   ALTER TABLE flow_pipelines DROP COLUMN IF EXISTS responsible_iam_user_id;
