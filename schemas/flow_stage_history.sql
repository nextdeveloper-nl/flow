-- PostgreSQL
-- Immutable audit trail of every stage transition for a pipeline item.

CREATE TABLE flow_stage_history (
    id                    bigint NOT NULL DEFAULT nextval('flow_stage_history_id_seq'::regclass),
    uuid                  uuid NOT NULL DEFAULT gen_random_uuid(),
    flow_item_id          bigint NOT NULL,
    flow_pipeline_id      bigint NOT NULL,
    from_stage_id         bigint, -- NULL when the item is first added to the pipeline.
    to_stage_id           bigint NOT NULL,
    moved_by_iam_user_id  bigint, -- [alias:iam_user_id]
    moved_at              timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT flow_stage_history_flow_item_id_fkey FOREIGN KEY (flow_item_id) REFERENCES flow_items(id) ON DELETE CASCADE,
    CONSTRAINT flow_stage_history_from_stage_id_fkey FOREIGN KEY (from_stage_id) REFERENCES flow_stages(id) ON DELETE SET NULL,
    CONSTRAINT flow_stage_history_to_stage_id_fkey FOREIGN KEY (to_stage_id) REFERENCES flow_stages(id),
    CONSTRAINT flow_stage_history_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_flow_stage_history_item ON public.flow_stage_history USING btree (flow_item_id);
CREATE INDEX idx_flow_stage_history_pipeline ON public.flow_stage_history USING btree (flow_pipeline_id);
