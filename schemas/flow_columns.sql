-- PostgreSQL
-- Custom field definitions per pipeline.

CREATE TABLE flow_columns (
    id                bigint NOT NULL DEFAULT nextval('flow_columns_id_seq'::regclass),
    uuid              uuid NOT NULL DEFAULT gen_random_uuid(),
    flow_pipeline_id  bigint NOT NULL,
    name              text NOT NULL, -- Internal key used in flow_item_values.
    label             text NOT NULL, -- Human-readable display label.
    field_type        text NOT NULL DEFAULT 'text'::text, -- Field type: text, number, date, boolean, select, multi_select, json.
    options           json, -- For select/multi_select types: array of allowed values, e.g. ["High","Medium","Low"].
    default_value     text,
    is_required       boolean NOT NULL DEFAULT false,
    position          integer NOT NULL DEFAULT 0,
    is_active         boolean NOT NULL DEFAULT true,
    created_at        timestamp with time zone NOT NULL DEFAULT now(),
    updated_at        timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at        timestamp with time zone,
    CONSTRAINT flow_columns_flow_pipeline_id_fkey FOREIGN KEY (flow_pipeline_id) REFERENCES flow_pipelines(id) ON DELETE CASCADE,
    CONSTRAINT flow_columns_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_flow_columns_pipeline ON public.flow_columns USING btree (flow_pipeline_id);
