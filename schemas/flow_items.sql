-- PostgreSQL
-- Polymorphic link between any object and a pipeline stage. One object can be in multiple pipelines but only one stage per pipeline.

CREATE TABLE flow_items (
    id                     bigint NOT NULL DEFAULT nextval('flow_items_id_seq'::regclass),
    uuid                   uuid NOT NULL DEFAULT gen_random_uuid(),
    flow_pipeline_id       bigint NOT NULL,
    flow_stage_id          bigint NOT NULL,
    object_type            text NOT NULL, -- The type of the linked object, e.g. crm_opportunity, support_ticket.
    object_id              bigint NOT NULL, -- The id of the linked object.
    position               integer NOT NULL DEFAULT 0, -- Kanban sort order within the stage.
    last_stage_changed_at  timestamp with time zone NOT NULL DEFAULT now(), -- Timestamp of the last stage transition. Used with flow_stages.sla_days to detect stale items.
    checklist_state        json, -- Completion state for the current stage checklist. Map of {key: {completed, completed_by, completed_at}}.
    iam_account_id         bigint,
    iam_user_id            bigint,
    created_at             timestamp with time zone NOT NULL DEFAULT now(),
    updated_at             timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at             timestamp with time zone,
    CONSTRAINT flow_items_flow_pipeline_id_fkey FOREIGN KEY (flow_pipeline_id) REFERENCES flow_pipelines(id) ON DELETE CASCADE,
    CONSTRAINT flow_items_flow_stage_id_fkey FOREIGN KEY (flow_stage_id) REFERENCES flow_stages(id),
    CONSTRAINT flow_items_pkey PRIMARY KEY (id),
    CONSTRAINT flow_items_flow_pipeline_id_object_type_object_id_key UNIQUE (flow_pipeline_id, object_type, object_id)
);

CREATE INDEX idx_flow_items_account ON public.flow_items USING btree (iam_account_id);
CREATE INDEX idx_flow_items_object ON public.flow_items USING btree (object_type, object_id);
CREATE INDEX idx_flow_items_pipeline ON public.flow_items USING btree (flow_pipeline_id);
CREATE INDEX idx_flow_items_stage ON public.flow_items USING btree (flow_stage_id);
CREATE INDEX idx_flow_items_stage_changed ON public.flow_items USING btree (last_stage_changed_at);
