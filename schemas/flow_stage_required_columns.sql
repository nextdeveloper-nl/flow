-- PostgreSQL
-- Declares which custom columns must be filled before an item can enter a given stage.

CREATE TABLE flow_stage_required_columns (
    id              bigint NOT NULL DEFAULT nextval('flow_stage_required_columns_id_seq'::regclass),
    flow_stage_id   bigint NOT NULL,
    flow_column_id  bigint NOT NULL,
    CONSTRAINT flow_stage_required_columns_flow_column_id_fkey FOREIGN KEY (flow_column_id) REFERENCES flow_columns(id) ON DELETE CASCADE,
    CONSTRAINT flow_stage_required_columns_flow_stage_id_fkey FOREIGN KEY (flow_stage_id) REFERENCES flow_stages(id) ON DELETE CASCADE,
    CONSTRAINT flow_stage_required_columns_pkey PRIMARY KEY (id),
    CONSTRAINT flow_stage_required_columns_flow_stage_id_flow_column_id_key UNIQUE (flow_stage_id, flow_column_id)
);
