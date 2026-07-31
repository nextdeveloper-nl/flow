-- PostgreSQL
-- Ordered stages within a pipeline.

CREATE TABLE flow_stages (
    id                bigint NOT NULL DEFAULT nextval('flow_stages_id_seq'::regclass),
    uuid              uuid NOT NULL DEFAULT gen_random_uuid(),
    flow_pipeline_id  bigint NOT NULL,
    name              text NOT NULL,
    color             text,
    position          integer NOT NULL DEFAULT 0, -- Sort order of the stage within its pipeline.
    probability       smallint NOT NULL DEFAULT 0, -- Default win probability % assigned to items entering this stage.
    sla_days          integer, -- Max days an item should stay in this stage before being flagged as stale.
    is_won            boolean NOT NULL DEFAULT false, -- Marks this stage as the closed-won terminal stage.
    is_lost           boolean NOT NULL DEFAULT false, -- Marks this stage as the closed-lost terminal stage.
    checklist         json, -- Checklist definition for this stage. Array of {key, label, required}. Completion state is tracked on flow_items.checklist_state.
    is_active         boolean NOT NULL DEFAULT true,
    created_at        timestamp with time zone NOT NULL DEFAULT now(),
    updated_at        timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at        timestamp with time zone,
    CONSTRAINT flow_stages_flow_pipeline_id_fkey FOREIGN KEY (flow_pipeline_id) REFERENCES flow_pipelines(id) ON DELETE CASCADE,
    CONSTRAINT flow_stages_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_flow_stages_pipeline ON public.flow_stages USING btree (flow_pipeline_id);
CREATE INDEX idx_flow_stages_position ON public.flow_stages USING btree (flow_pipeline_id, "position");
