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
        CASE
            WHEN fs.sla_days IS NULL THEN false
            WHEN fs.is_won THEN false
            WHEN fs.is_lost THEN false
            WHEN (now() - fi.last_stage_changed_at) > ((fs.sla_days || ' days'::text)::interval) THEN true
            ELSE false
        END AS sla_breached
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
  WHERE fi.deleted_at IS NULL;
