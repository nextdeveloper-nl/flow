<?php
return [
    'scopes'    =>  [
        'global' => [
            //  Dont do this here because it makes infinite loop with user object.
            '\NextDeveloper\IAM\Database\Scopes\AuthorizationScope',
            '\NextDeveloper\Commons\Database\GlobalScopes\LimitScope',
        ]
    ],

    //  Catalog of reusable pipeline templates for the "start with a template" flow.
    //  PipelinesService::createFromTemplate() reads this to create a real Pipeline + Stages
    //  for the current account. No DB column exists for icon/template metadata, so this
    //  config array is the single source of truth (also consumed by the frontend picker).
    'pipeline_templates' => [
        'b2b-sales' => [
            'name'          => 'B2B Sales',
            'description'   => 'Standard outbound / inbound sales flow from first contact to close.',
            'icon'          => '🤝',
            'campaign_type' => 'sales',
            'stages'        => [
                ['name' => 'Lead',          'color' => '#94a3b8', 'probability' => 10,  'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Initial inquiry or inbound contact. Not yet qualified.'],
                ['name' => 'Qualified',     'color' => '#60a5fa', 'probability' => 30,  'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Lead confirmed to match your ideal customer profile.'],
                ['name' => 'Proposal Sent', 'color' => '#f59e0b', 'probability' => 60,  'sla_days' => 10, 'is_won' => false, 'is_lost' => false, 'description' => 'Formal proposal or quote delivered to the prospect.'],
                ['name' => 'Negotiation',   'color' => '#f97316', 'probability' => 80,  'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Pricing and terms are actively being discussed.'],
                ['name' => 'Contract Sent', 'color' => '#a78bfa', 'probability' => 90,  'sla_days' => 5,  'is_won' => false, 'is_lost' => false, 'description' => 'Contract is out for signature, deal nearly closed.'],
                ['name' => 'Won',           'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Deal closed successfully.'],
                ['name' => 'Lost',          'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Opportunity did not convert.'],
            ],
        ],

        'customer-onboarding' => [
            'name'          => 'Customer Onboarding',
            'description'   => 'Post-sale activation flow from signed contract to fully live customer.',
            'icon'          => '🚀',
            'campaign_type' => null,
            'stages'        => [
                ['name' => 'Signed',            'color' => '#6366f1', 'probability' => 100, 'sla_days' => 3,  'is_won' => false, 'is_lost' => false, 'description' => 'Contract signed, onboarding begins.'],
                ['name' => 'Kickoff Scheduled', 'color' => '#60a5fa', 'probability' => 100, 'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Kickoff call has been booked with the customer.'],
                ['name' => 'Implementation',    'color' => '#f59e0b', 'probability' => 100, 'sla_days' => 30, 'is_won' => false, 'is_lost' => false, 'description' => 'Product is being set up and configured.'],
                ['name' => 'Training',          'color' => '#818cf8', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Customer team is being trained on the platform.'],
                ['name' => 'Live',              'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Customer is fully live and active.'],
                ['name' => 'Churned',           'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Customer cancelled or did not activate.'],
            ],
        ],

        'support-tracking' => [
            'name'          => 'Support / Issue Tracking',
            'description'   => 'Customer support and bug tracking workflow from report to resolution.',
            'icon'          => '🎧',
            'campaign_type' => null,
            'stages'        => [
                ['name' => 'New',              'color' => '#94a3b8', 'probability' => 0,   'sla_days' => 1, 'is_won' => false, 'is_lost' => false, 'description' => 'Ticket received, not yet assigned.'],
                ['name' => 'Assigned',         'color' => '#60a5fa', 'probability' => 20,  'sla_days' => 1, 'is_won' => false, 'is_lost' => false, 'description' => 'Ticket assigned to an agent or team.'],
                ['name' => 'In Progress',      'color' => '#f59e0b', 'probability' => 50,  'sla_days' => 7, 'is_won' => false, 'is_lost' => false, 'description' => 'Actively being worked on.'],
                ['name' => 'Pending Customer', 'color' => '#f97316', 'probability' => 60,  'sla_days' => 3, 'is_won' => false, 'is_lost' => false, 'description' => 'Waiting on a response or action from the customer.'],
                ['name' => 'Resolved',         'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Issue has been resolved.'],
                ['name' => 'Closed',           'color' => '#6b7280', 'probability' => 100, 'sla_days' => null, 'is_won' => false, 'is_lost' => false, 'description' => 'Ticket closed and archived.'],
            ],
        ],

        'email-marketing' => [
            'name'          => 'Email Marketing Campaign',
            'description'   => '3-email spaced playbook spread over two weeks to get replies without annoying prospects.',
            'icon'          => '📧',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Email 1 — Initial Outreach',    'color' => '#6366f1', 'probability' => 10, 'sla_days' => 4,  'is_won' => false, 'is_lost' => false, 'description' => 'Day 1. Lead with a strong value proposition, target a specific pain point, and end with a low-friction CTA.'],
                ['name' => 'Email 2 — Value-Add Follow-up', 'color' => '#f59e0b', 'probability' => 30, 'sla_days' => 6,  'is_won' => false, 'is_lost' => false, 'description' => 'Day 4–5. Add a case study, new resource, or relevant insight. Do not just "check in." Send in the same thread.'],
                ['name' => 'Email 3 — Soft Breakup',        'color' => '#f97316', 'probability' => 50, 'sla_days' => null, 'is_won' => false, 'is_lost' => false, 'description' => 'Day 9–11. Pivot the angle — ask if you should follow up next quarter or confirm you are speaking to the right person.'],
                ['name' => 'Cooldown',                      'color' => '#6b7280', 'probability' => 0,  'sla_days' => 90, 'is_won' => false, 'is_lost' => false, 'description' => 'Waiting period before re-engaging. Do not contact the prospect during this stage. Revisit after the SLA expires.'],
            ],
        ],

        'lead-nurture' => [
            'name'          => 'Lead Nurture / Drip',
            'description'   => 'Automated nurture sequence that warms up a new lead until they are sales-ready.',
            'icon'          => '🌱',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'New Lead',             'color' => '#94a3b8', 'probability' => 10,  'sla_days' => 1, 'is_won' => false, 'is_lost' => false, 'description' => 'Lead just entered the funnel via form fill, download, or signup.'],
                ['name' => 'Welcome / Delivered',  'color' => '#60a5fa', 'probability' => 20,  'sla_days' => 2, 'is_won' => false, 'is_lost' => false, 'description' => 'Deliver the requested content or welcome message immediately.'],
                ['name' => 'Nurture Content',      'color' => '#818cf8', 'probability' => 35,  'sla_days' => 7, 'is_won' => false, 'is_lost' => false, 'description' => 'Send related educational content to build trust.'],
                ['name' => 'Case Study / Proof',   'color' => '#f59e0b', 'probability' => 50,  'sla_days' => 7, 'is_won' => false, 'is_lost' => false, 'description' => 'Share a relevant case study or social proof.'],
                ['name' => 'Sales-Qualified',      'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Lead meets scoring threshold and is handed to sales.'],
                ['name' => 'Disqualified',         'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Lead unsubscribed, went cold, or does not fit ICP.'],
            ],
        ],

        'product-launch' => [
            'name'          => 'Product Launch',
            'description'   => 'Go-to-market flow for launching a new product or feature.',
            'icon'          => '📣',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Strategy & Brief',      'color' => '#94a3b8', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Define goal, audience, offer, channels, and KPIs.'],
                ['name' => 'Teaser / Pre-launch',   'color' => '#60a5fa', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Build anticipation across channels ahead of launch day.'],
                ['name' => 'Launch Day',            'color' => '#f59e0b', 'probability' => 100, 'sla_days' => 1,  'is_won' => false, 'is_lost' => false, 'description' => 'Publish assets and go live across all channels.'],
                ['name' => 'Post-launch Follow-up', 'color' => '#f97316', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Follow up with early users, press, and partners.'],
                ['name' => 'Optimization / Upsell', 'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Monitor performance and expand to upsell/cross-sell.'],
                ['name' => 'Shelved',               'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Launch postponed or cancelled.'],
            ],
        ],

        'win-back' => [
            'name'          => 'Win-back / Re-engagement',
            'description'   => 'Re-activate stalled deals or cold contacts before writing them off.',
            'icon'          => '🪃',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Stalled / Cold Detected',  'color' => '#94a3b8', 'probability' => 10,  'sla_days' => 30, 'is_won' => false, 'is_lost' => false, 'description' => 'Deal or contact inactive past SLA threshold, flagged for win-back.'],
                ['name' => 'Re-engagement Outreach',   'color' => '#60a5fa', 'probability' => 25,  'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Personalized email, exec touch, or incentive sent.'],
                ['name' => 'Response Check',           'color' => '#f59e0b', 'probability' => 40,  'sla_days' => 5,  'is_won' => false, 'is_lost' => false, 'description' => 'Waiting on an open/click/reply signal.'],
                ['name' => 'Reactivated',              'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Contact re-engaged and back in an active pipeline.'],
                ['name' => 'Closed Lost',              'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'No response after win-back attempts; archive.'],
            ],
        ],

        'webinar-campaign' => [
            'name'          => 'Webinar Campaign',
            'description'   => 'Promote, run, and follow up on a lead-generating webinar or online event.',
            'icon'          => '🎥',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Promotion',           'color' => '#94a3b8', 'probability' => 100, 'sla_days' => 21, 'is_won' => false, 'is_lost' => false, 'description' => 'Multi-channel promotion begins ~4 weeks out (email, social, ads, partners).'],
                ['name' => 'Registered',          'color' => '#60a5fa', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Prospect registered for the webinar.'],
                ['name' => 'Pre-event Nurture',   'color' => '#818cf8', 'probability' => 100, 'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Reminders and agenda teasers before the event.'],
                ['name' => 'Attended / No-show',  'color' => '#f59e0b', 'probability' => 100, 'sla_days' => 1,  'is_won' => false, 'is_lost' => false, 'description' => 'Event delivered live; engagement signals captured.'],
                ['name' => 'Hot Follow-up',       'color' => '#22c55e', 'probability' => 100, 'sla_days' => 3,  'is_won' => true,  'is_lost' => false, 'description' => 'High-intent attendee routed to sales within 24-48h.'],
                ['name' => 'Cold Follow-up',      'color' => '#6b7280', 'probability' => 0,   'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'No-show or low-engagement contact pushed into general nurture.'],
            ],
        ],

        'cart-abandonment' => [
            'name'          => 'Cart Abandonment Recovery',
            'description'   => 'Recover lost e-commerce sales from shoppers who abandoned checkout.',
            'icon'          => '🛒',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Cart Abandoned',  'color' => '#94a3b8', 'probability' => 10,  'sla_days' => 1, 'is_won' => false, 'is_lost' => false, 'description' => 'Checkout started but not completed; wait period before first touch.'],
                ['name' => 'Reminder 1',      'color' => '#60a5fa', 'probability' => 20,  'sla_days' => 1, 'is_won' => false, 'is_lost' => false, 'description' => 'Cart reminder with item details and social proof.'],
                ['name' => 'Reminder 2',      'color' => '#f59e0b', 'probability' => 35,  'sla_days' => 2, 'is_won' => false, 'is_lost' => false, 'description' => 'Follow-up for opens/clicks that did not convert.'],
                ['name' => 'Incentive Offer', 'color' => '#f97316', 'probability' => 50,  'sla_days' => 2, 'is_won' => false, 'is_lost' => false, 'description' => 'Discount or loyalty-aware incentive, capped at 3 emails total.'],
                ['name' => 'Recovered',       'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Customer completed the purchase.'],
                ['name' => 'Expired',         'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Cart expired unrecovered.'],
            ],
        ],

        'abm-campaign' => [
            'name'          => 'Account-Based Marketing',
            'description'   => 'Coordinated sales + marketing push targeting a specific named account.',
            'icon'          => '🎯',
            'campaign_type' => 'marketing',
            'stages'        => [
                ['name' => 'Target Account Selected',    'color' => '#94a3b8', 'probability' => 10,  'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Account identified and added to the target list.'],
                ['name' => 'Awareness',                  'color' => '#60a5fa', 'probability' => 25,  'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Personalized ads and content introduce the account to your brand.'],
                ['name' => 'Consideration / Engagement', 'color' => '#818cf8', 'probability' => 45,  'sla_days' => 21, 'is_won' => false, 'is_lost' => false, 'description' => 'Direct outreach, webinars, and targeted content build trust.'],
                ['name' => 'Pipeline Acceleration',      'color' => '#f59e0b', 'probability' => 70,  'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Sales and marketing coordinate to move an open opportunity forward.'],
                ['name' => 'Closed Won / Expansion',     'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Account converted; expansion or upsell motion begins.'],
                ['name' => 'Closed Lost',                'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Account disengaged or chose a competitor.'],
            ],
        ],

        'event-planning' => [
            'name'          => 'Event Planning',
            'description'   => 'Operational flow for planning and running a company event, from concept to wrap-up.',
            'icon'          => '🎪',
            'campaign_type' => null,
            'stages'        => [
                ['name' => 'Concept & Budget',        'color' => '#94a3b8', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Define event goals, format, and budget approval.'],
                ['name' => 'Venue & Vendors Booked',  'color' => '#60a5fa', 'probability' => 100, 'sla_days' => 21, 'is_won' => false, 'is_lost' => false, 'description' => 'Venue, catering, and key vendors confirmed.'],
                ['name' => 'Invitations & Promotion', 'color' => '#818cf8', 'probability' => 100, 'sla_days' => 14, 'is_won' => false, 'is_lost' => false, 'description' => 'Invites sent and event promoted across channels.'],
                ['name' => 'Logistics Confirmed',     'color' => '#f59e0b', 'probability' => 100, 'sla_days' => 7,  'is_won' => false, 'is_lost' => false, 'description' => 'Run-of-show, staffing, and materials finalized.'],
                ['name' => 'Event Day',               'color' => '#f97316', 'probability' => 100, 'sla_days' => 1,  'is_won' => false, 'is_lost' => false, 'description' => 'Event is live; on-site execution and coordination.'],
                ['name' => 'Wrapped Up',              'color' => '#22c55e', 'probability' => 100, 'sla_days' => null, 'is_won' => true,  'is_lost' => false, 'description' => 'Event complete; post-event report and follow-ups sent.'],
                ['name' => 'Cancelled',               'color' => '#ef4444', 'probability' => 0,   'sla_days' => null, 'is_won' => false, 'is_lost' => true,  'description' => 'Event postponed or cancelled before execution.'],
            ],
        ],
    ],
];
