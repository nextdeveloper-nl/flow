-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW flow_stages_perspective AS
SELECT fs.id,
    fs.uuid,
    fs.flow_pipeline_id,
    fs.name,
    fs.color,
    fs."position",
    fs.probability,
    fs.sla_days,
    fs.is_won,
    fs.is_lost,
    fs.checklist,
    fs.is_active,
    fs.created_at,
    fs.updated_at,
    fs.deleted_at,
    fp.name AS pipeline_name,
    fp.object_type AS pipeline_object_type,
    fp.iam_account_id AS pipeline_iam_account_id,
    COALESCE(ic.item_count, 0::bigint) AS item_count,
    COALESCE(ic.sla_breached_count, 0::bigint) AS sla_breached_count,
    ic.avg_days_in_stage,
    COALESCE(rc.required_column_count, 0::bigint) AS required_column_count
   FROM flow_stages fs
     JOIN flow_pipelines fp ON fp.id = fs.flow_pipeline_id
     LEFT JOIN ( SELECT fi.flow_stage_id,
            count(*) AS item_count,
            count(*) FILTER (WHERE fs2.sla_days IS NOT NULL AND NOT fs2.is_won AND NOT fs2.is_lost AND (now() - fi.last_stage_changed_at) > ((fs2.sla_days || ' days'::text)::interval)) AS sla_breached_count,
            round(avg(EXTRACT(epoch FROM now() - fi.last_stage_changed_at) / 86400.0), 1) AS avg_days_in_stage
           FROM flow_items fi
             JOIN flow_stages fs2 ON fs2.id = fi.flow_stage_id
          WHERE fi.deleted_at IS NULL
          GROUP BY fi.flow_stage_id) ic ON ic.flow_stage_id = fs.id
     LEFT JOIN ( SELECT flow_stage_required_columns.flow_stage_id,
            count(*) AS required_column_count
           FROM flow_stage_required_columns
          GROUP BY flow_stage_required_columns.flow_stage_id) rc ON rc.flow_stage_id = fs.id
  WHERE fs.deleted_at IS NULL;
