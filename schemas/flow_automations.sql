-- PostgreSQL
-- Declares events to fire when pipeline transitions occur. The events system handles execution.

CREATE TABLE flow_automations (
    id                bigint NOT NULL DEFAULT nextval('flow_automations_id_seq'::regclass),
    uuid              uuid NOT NULL DEFAULT gen_random_uuid(),
    flow_pipeline_id  bigint NOT NULL,
    flow_stage_id     bigint, -- NULL means the automation applies to all stages in the pipeline.
    trigger           text NOT NULL, -- When to fire: stage_entered, stage_exited, sla_breached, item_created.
    event_name        text, -- The event name to fire into the events system.
    payload_template  json, -- Optional static payload merged into the event. Dynamic fields resolved by the events system.
    is_active         boolean NOT NULL DEFAULT true,
    iam_account_id    bigint,
    created_at        timestamp with time zone NOT NULL DEFAULT now(),
    updated_at        timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at        timestamp with time zone,
    common_pusher_id  bigint,
    CONSTRAINT flow_automations_common_pusher_id_fkey FOREIGN KEY (common_pusher_id) REFERENCES common_pushers(id) ON DELETE SET NULL,
    CONSTRAINT flow_automations_flow_pipeline_id_fkey FOREIGN KEY (flow_pipeline_id) REFERENCES flow_pipelines(id) ON DELETE CASCADE,
    CONSTRAINT flow_automations_flow_stage_id_fkey FOREIGN KEY (flow_stage_id) REFERENCES flow_stages(id) ON DELETE CASCADE,
    CONSTRAINT flow_automations_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_flow_automations_pipeline ON public.flow_automations USING btree (flow_pipeline_id);
CREATE INDEX idx_flow_automations_pusher ON public.flow_automations USING btree (common_pusher_id);
CREATE INDEX idx_flow_automations_stage ON public.flow_automations USING btree (flow_stage_id);
CREATE INDEX idx_flow_automations_trigger ON public.flow_automations USING btree (trigger);
