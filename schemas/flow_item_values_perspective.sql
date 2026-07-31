-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW flow_item_values_perspective AS
SELECT fi.id,
    fi.uuid,
    fi.flow_pipeline_id,
    fi.flow_stage_id,
    fi.object_type,
    fi.object_id,
    fi."position",
    fi.last_stage_changed_at,
    fi.checklist_state,
    fi.iam_account_id,
    fi.iam_user_id,
    fi.created_at,
    fi.updated_at,
    fi.deleted_at,
    fs.name AS stage_name,
    fs.color AS stage_color,
    fp.name AS pipeline_name,
    fp.object_type AS pipeline_object_type,
    COALESCE(jsonb_object_agg(fc.name,
        CASE fc.field_type
            WHEN 'number'::text THEN to_jsonb(fiv.value::numeric)
            WHEN 'boolean'::text THEN to_jsonb(fiv.value::boolean)
            ELSE to_jsonb(fiv.value)
        END) FILTER (WHERE fc.id IS NOT NULL), '{}'::jsonb) AS field_values,
    COALESCE(jsonb_object_agg(fc.name, fc.field_type) FILTER (WHERE fc.id IS NOT NULL), '{}'::jsonb) AS field_types
   FROM flow_items fi
     JOIN flow_stages fs ON fs.id = fi.flow_stage_id
     JOIN flow_pipelines fp ON fp.id = fi.flow_pipeline_id
     LEFT JOIN flow_stage_required_columns fsrc ON fsrc.flow_stage_id = fi.flow_stage_id
     LEFT JOIN flow_columns fc ON fc.id = fsrc.flow_column_id AND fc.deleted_at IS NULL
     LEFT JOIN flow_item_values fiv ON fiv.flow_item_id = fi.id AND fiv.flow_column_id = fc.id
  WHERE fi.deleted_at IS NULL
  GROUP BY fi.id, fs.name, fs.color, fp.name, fp.object_type;
