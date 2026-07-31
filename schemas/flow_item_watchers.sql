-- PostgreSQL
-- Users who follow a pipeline item and receive notifications on stage changes.

CREATE TABLE flow_item_watchers (
    flow_item_id  bigint NOT NULL,
    iam_user_id   bigint NOT NULL,
    created_at    timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT flow_item_watchers_flow_item_id_fkey FOREIGN KEY (flow_item_id) REFERENCES flow_items(id) ON DELETE CASCADE,
    CONSTRAINT flow_item_watchers_pkey PRIMARY KEY (flow_item_id, iam_user_id)
);

CREATE INDEX idx_flow_item_watchers_user ON public.flow_item_watchers USING btree (iam_user_id);
