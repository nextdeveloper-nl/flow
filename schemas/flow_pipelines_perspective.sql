-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

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
    COALESCE(ic.lost_item_count, 0::bigint) AS lost_item_count
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
          GROUP BY fi.flow_pipeline_id) ic ON ic.flow_pipeline_id = fp.id;
