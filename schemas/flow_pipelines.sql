-- PostgreSQL
-- Named pipeline definitions. Generic — works with any object type in the system.

CREATE TABLE flow_pipelines (
    id              bigint NOT NULL DEFAULT nextval('flow_pipelines_id_seq'::regclass),
    uuid            uuid NOT NULL DEFAULT gen_random_uuid(),
    name            text NOT NULL,
    description     text,
    object_type     text, -- Advisory: the object type this pipeline is designed for, e.g. crm_opportunity, support_ticket.
    object_id       bigint, -- Reverse relation: id of the object_type row this pipeline belongs to (e.g. crm_campaigns.id).
    is_template     boolean NOT NULL DEFAULT false, -- If true, this pipeline is a reusable blueprint accounts can clone from.
    is_system       boolean NOT NULL DEFAULT false, -- If true, this pipeline is a system-level template not owned by any account.
    is_active       boolean NOT NULL DEFAULT true,
    iam_account_id  bigint,
    iam_user_id     bigint,
    created_at      timestamp with time zone NOT NULL DEFAULT now(),
    updated_at      timestamp with time zone NOT NULL DEFAULT now(),
    deleted_at      timestamp with time zone,
    CONSTRAINT flow_pipelines_pkey PRIMARY KEY (id)
);

CREATE INDEX idx_flow_pipelines_account ON public.flow_pipelines USING btree (iam_account_id);
CREATE INDEX idx_flow_pipelines_is_template ON public.flow_pipelines USING btree (is_template);
CREATE INDEX idx_flow_pipelines_object_type ON public.flow_pipelines USING btree (object_type);
CREATE INDEX idx_flow_pipelines_object_id ON public.flow_pipelines USING btree (object_id);
