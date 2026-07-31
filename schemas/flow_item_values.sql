-- PostgreSQL
-- EAV table storing custom column values per pipeline item. All values stored as TEXT; field_type on flow_columns defines casting.

CREATE TABLE flow_item_values (
    id              bigint NOT NULL DEFAULT nextval('flow_item_values_id_seq'::regclass),
    flow_item_id    bigint NOT NULL,
    flow_column_id  bigint NOT NULL,
    value           text,
    created_at      timestamp with time zone NOT NULL DEFAULT now(),
    updated_at      timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT flow_item_values_flow_column_id_fkey FOREIGN KEY (flow_column_id) REFERENCES flow_columns(id) ON DELETE CASCADE,
    CONSTRAINT flow_item_values_flow_item_id_fkey FOREIGN KEY (flow_item_id) REFERENCES flow_items(id) ON DELETE CASCADE,
    CONSTRAINT flow_item_values_pkey PRIMARY KEY (id),
    CONSTRAINT flow_item_values_flow_item_id_flow_column_id_key UNIQUE (flow_item_id, flow_column_id)
);

CREATE INDEX idx_flow_item_values_column ON public.flow_item_values USING btree (flow_column_id);
CREATE INDEX idx_flow_item_values_item ON public.flow_item_values USING btree (flow_item_id);
