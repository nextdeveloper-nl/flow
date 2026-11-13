-- PostgreSQL
-- VIEW (read-only; re-run this file with CREATE OR REPLACE VIEW whenever the SELECT needs to change)

CREATE OR REPLACE VIEW flow_items_perspective AS
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
    fs.sla_days AS stage_sla_days,
        CASE fi.object_type
            WHEN 'NextDeveloper\CRM\Opportunities'::text THEN opp.name
            WHEN 'NextDeveloper\CRM\Accounts'::text THEN ia_crm.name
            WHEN 'NextDeveloper\Support\Tickets'::text THEN st.title
            WHEN 'NextDeveloper\IAAS\VirtualMachines'::text THEN vm.name
            ELSE fi.object_id::text
        END AS object_name,
        CASE fi.object_type
            WHEN 'NextDeveloper\CRM\Opportunities'::text THEN ia_opp.name
            WHEN 'NextDeveloper\CRM\Accounts'::text THEN NULL::text
            WHEN 'NextDeveloper\Support\Tickets'::text THEN ia_st.name
            WHEN 'NextDeveloper\IAAS\VirtualMachines'::text THEN vm.status
            ELSE NULL::text
        END AS object_subtitle,
        CASE fi.object_type
            WHEN 'NextDeveloper\CRM\Opportunities'::text THEN opp.income
            ELSE NULL::numeric
        END AS object_value,
        -- flow_items.sla_breached_at is stored at stage entry (entered + sla_days,
        -- NULL for stages without SLA or won/lost), so the UI and the hourly SLA check
        -- (ItemsService::checkSlaBreaches) use exactly the same deadline.
        COALESCE(fi.sla_breached_at <= now(), false) AS sla_breached,
    -- New columns must stay at the end: CREATE OR REPLACE VIEW can only append.
    fi.sla_breached_at,
    fi.sla_actioned_at,
    -- Owner of the item for the board avatar and the table Owner columns: the opportunity's owner for an
    -- opportunity item, the item's own user for everything else.
    ou.fullname AS owner_name,
    ou.email AS owner_email,
    ou.uuid AS owner_uuid,
    -- Latest non-deleted quote total of the opportunity (the board value is the quote total, object_value stays the
    -- deal size); NULL for items that are not opportunities.
    CASE fi.object_type
        WHEN 'NextDeveloper\CRM\Opportunities'::text THEN COALESCE(lq.total_amount, 0)
        ELSE NULL::numeric
    END AS object_quote_amount
   FROM flow_items fi
     JOIN flow_stages fs ON fs.id = fi.flow_stage_id
     LEFT JOIN crm_opportunities opp ON fi.object_type = 'NextDeveloper\CRM\Opportunities'::text AND opp.id = fi.object_id
     LEFT JOIN crm_accounts crm_opp_acc ON fi.object_type = 'NextDeveloper\CRM\Opportunities'::text AND crm_opp_acc.id = opp.crm_account_id
     LEFT JOIN iam_accounts ia_opp ON fi.object_type = 'NextDeveloper\CRM\Opportunities'::text AND ia_opp.id = crm_opp_acc.iam_account_id
     LEFT JOIN crm_accounts crm_acc ON fi.object_type = 'NextDeveloper\CRM\Accounts'::text AND crm_acc.id = fi.object_id
     LEFT JOIN iam_accounts ia_crm ON fi.object_type = 'NextDeveloper\CRM\Accounts'::text AND ia_crm.id = crm_acc.iam_account_id
     LEFT JOIN support_tickets st ON fi.object_type = 'NextDeveloper\Support\Tickets'::text AND st.id = fi.object_id
     LEFT JOIN iam_accounts ia_st ON fi.object_type = 'NextDeveloper\Support\Tickets'::text AND ia_st.id = st.iam_account_id
     LEFT JOIN iaas_virtual_machines vm ON fi.object_type = 'NextDeveloper\IAAS\VirtualMachines'::text AND vm.id = fi.object_id
     LEFT JOIN iam_users ou ON ou.id = CASE WHEN fi.object_type = 'NextDeveloper\CRM\Opportunities'::text THEN opp.iam_user_id ELSE fi.iam_user_id END
     LEFT JOIN ( SELECT DISTINCT ON (crm_quotes.crm_opportunity_id) crm_quotes.crm_opportunity_id,
            crm_quotes.total_amount
           FROM crm_quotes
          WHERE crm_quotes.deleted_at IS NULL
          ORDER BY crm_quotes.crm_opportunity_id, crm_quotes.created_at DESC) lq ON fi.object_type = 'NextDeveloper\CRM\Opportunities'::text AND lq.crm_opportunity_id = fi.object_id
  WHERE fi.deleted_at IS NULL;
