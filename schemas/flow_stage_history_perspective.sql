-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW flow_stage_history_perspective AS
SELECT fsh.id,
    fsh.uuid,
    fsh.flow_item_id,
    fsh.flow_pipeline_id,
    fsh.from_stage_id,
    fsh.to_stage_id,
    fsh.moved_by_iam_user_id,
    fsh.moved_at,
    from_s.name AS from_stage_name,
    from_s.color AS from_stage_color,
    to_s.name AS to_stage_name,
    to_s.color AS to_stage_color,
    COALESCE(u.fullname, u.email) AS moved_by_name
   FROM flow_stage_history fsh
     LEFT JOIN flow_stages from_s ON from_s.id = fsh.from_stage_id
     JOIN flow_stages to_s ON to_s.id = fsh.to_stage_id
     LEFT JOIN iam_users u ON u.id = fsh.moved_by_iam_user_id;
