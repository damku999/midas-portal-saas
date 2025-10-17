<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Variable Categories
    |--------------------------------------------------------------------------
    |
    | Define categories for grouping variables in the UI
    |
    */
    'categories' => [
        'customer' => [
            'label' => 'Customer Information',
            'color' => 'primary',
            'icon' => 'fa-user',
            'order' => 1,
        ],
        'policy' => [
            'label' => 'Policy Details',
            'color' => 'success',
            'icon' => 'fa-file-contract',
            'order' => 2,
        ],
        'insurance' => [
            'label' => 'Insurance Company',
            'color' => 'info',
            'icon' => 'fa-shield-alt',
            'order' => 3,
        ],
        'dates' => [
            'label' => 'Important Dates',
            'color' => 'warning',
            'icon' => 'fa-calendar',
            'order' => 4,
        ],
        'vehicle' => [
            'label' => 'Vehicle Information',
            'color' => 'danger',
            'icon' => 'fa-car',
            'order' => 5,
        ],
        'quotation' => [
            'label' => 'Quotation Details',
            'color' => 'secondary',
            'icon' => 'fa-calculator',
            'order' => 6,
        ],
        'claim' => [
            'label' => 'Claim Information',
            'color' => 'dark',
            'icon' => 'fa-file-medical',
            'order' => 7,
        ],
        'company' => [
            'label' => 'Company Details',
            'color' => 'primary',
            'icon' => 'fa-building',
            'order' => 8,
        ],
        'attachments' => [
            'label' => 'File Attachments',
            'color' => 'light',
            'icon' => 'fa-paperclip',
            'order' => 9,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Variable Definitions
    |--------------------------------------------------------------------------
    |
    | All available variables with their metadata and data sources
    |
    */
    'variables' => [

        // =======================================================
        // CUSTOMER VARIABLES
        // =======================================================

        'customer_name' => [
            'label' => 'Customer Name',
            'description' => 'Full name of the customer',
            'category' => 'customer',
            'source' => 'customer.name',
            'type' => 'string',
            'format' => null,
            'sample' => 'Darshan Patel',
            'requires' => ['customer'],
            'available_for' => null, // null = available for all notification types
        ],

        'customer_email' => [
            'label' => 'Customer Email',
            'description' => 'Email address of the customer',
            'category' => 'customer',
            'source' => 'customer.email',
            'type' => 'string',
            'format' => null,
            'sample' => 'darshan@example.com',
            'requires' => ['customer'],
        ],

        'customer_mobile' => [
            'label' => 'Customer Mobile',
            'description' => 'Mobile number of the customer',
            'category' => 'customer',
            'source' => 'customer.mobile_number',
            'type' => 'string',
            'format' => null,
            'sample' => '9876543210',
            'requires' => ['customer'],
        ],

        'customer_whatsapp' => [
            'label' => 'Customer WhatsApp',
            'description' => 'WhatsApp number of the customer',
            'category' => 'customer',
            'source' => 'customer.mobile_number',
            'type' => 'string',
            'format' => null,
            'sample' => '919876543210',
            'requires' => ['customer'],
        ],

        'date_of_birth' => [
            'label' => 'Date of Birth',
            'description' => 'Customer\'s date of birth',
            'category' => 'customer',
            'source' => 'customer.date_of_birth',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '15-Jan-1990',
            'requires' => ['customer'],
        ],

        'wedding_anniversary' => [
            'label' => 'Wedding Anniversary',
            'description' => 'Customer\'s wedding anniversary date',
            'category' => 'customer',
            'source' => 'customer.wedding_anniversary_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '20-Feb-2015',
            'requires' => ['customer'],
        ],

        'engagement_anniversary' => [
            'label' => 'Engagement Anniversary',
            'description' => 'Customer\'s engagement anniversary date',
            'category' => 'customer',
            'source' => 'customer.engagement_anniversary_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '14-Feb-2014',
            'requires' => ['customer'],
        ],

        // =======================================================
        // POLICY VARIABLES
        // =======================================================

        'policy_number' => [
            'label' => 'Policy Number',
            'description' => 'Insurance policy number',
            'category' => 'policy',
            'source' => 'insurance.policy_no',
            'type' => 'string',
            'format' => null,
            'sample' => 'POL-2025-001234',
            'requires' => ['insurance'],
        ],

        'policy_no' => [
            'label' => 'Policy No',
            'description' => 'Insurance policy number (short form)',
            'category' => 'policy',
            'source' => 'insurance.policy_no',
            'type' => 'string',
            'format' => null,
            'sample' => 'POL-2025-001234',
            'requires' => ['insurance'],
        ],

        'policy_type' => [
            'label' => 'Policy Type',
            'description' => 'Type of insurance policy',
            'category' => 'policy',
            'source' => 'insurance.policyType.name',
            'type' => 'string',
            'format' => null,
            'sample' => '2 WHEELER',
            'requires' => ['insurance'],
        ],

        'premium_type' => [
            'label' => 'Premium Type',
            'description' => 'Type of premium payment',
            'category' => 'policy',
            'source' => 'insurance.premiumType.name',
            'type' => 'string',
            'format' => null,
            'sample' => 'Comprehensive',
            'requires' => ['insurance'],
        ],

        'premium_amount' => [
            'label' => 'Premium Amount',
            'description' => 'Total premium amount',
            'category' => 'policy',
            'source' => 'insurance.premium_amount',
            'type' => 'currency',
            'format' => 'currency',
            'sample' => '₹5,000',
            'requires' => ['insurance'],
        ],

        'net_premium' => [
            'label' => 'Net Premium',
            'description' => 'Net premium amount before taxes',
            'category' => 'policy',
            'source' => 'insurance.net_premium',
            'type' => 'currency',
            'format' => 'currency',
            'sample' => '₹4,500',
            'requires' => ['insurance'],
        ],

        'policy_tenure' => [
            'label' => 'Policy Tenure',
            'description' => 'Duration of policy in years',
            'category' => 'policy',
            'source' => 'computed:policy_tenure',
            'type' => 'computed',
            'format' => null,
            'sample' => '1 Year',
            'requires' => ['insurance'],
        ],

        'ncb_percentage' => [
            'label' => 'NCB Percentage',
            'description' => 'No Claim Bonus percentage',
            'category' => 'policy',
            'source' => 'insurance.ncb_percentage',
            'type' => 'percentage',
            'format' => 'percentage',
            'sample' => '20%',
            'requires' => ['insurance'],
        ],

        'plan_name' => [
            'label' => 'Plan Name',
            'description' => 'Name of the insurance plan',
            'category' => 'policy',
            'source' => 'insurance.plan_name',
            'type' => 'string',
            'format' => null,
            'sample' => 'Super Saver Plan',
            'requires' => ['insurance'],
        ],

        'policy_term' => [
            'label' => 'Policy Term',
            'description' => 'Term duration of the policy',
            'category' => 'policy',
            'source' => 'insurance.policy_term',
            'type' => 'string',
            'format' => null,
            'sample' => '5 Years',
            'requires' => ['insurance'],
        ],

        // =======================================================
        // INSURANCE COMPANY VARIABLES
        // =======================================================

        'insurance_company' => [
            'label' => 'Insurance Company',
            'description' => 'Name of the insurance company',
            'category' => 'insurance',
            'source' => 'insurance.insuranceCompany.name',
            'type' => 'string',
            'format' => null,
            'sample' => 'HDFC ERGO',
            'requires' => ['insurance'],
        ],

        'insurance_company_code' => [
            'label' => 'Insurance Company Code',
            'description' => 'Code of the insurance company',
            'category' => 'insurance',
            'source' => 'insurance.insuranceCompany.code',
            'type' => 'string',
            'format' => null,
            'sample' => 'HDFC',
            'requires' => ['insurance'],
        ],

        // =======================================================
        // DATE VARIABLES
        // =======================================================

        'start_date' => [
            'label' => 'Policy Start Date',
            'description' => 'Date when policy started',
            'category' => 'dates',
            'source' => 'insurance.start_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '01-Jan-2025',
            'requires' => ['insurance'],
        ],

        'expiry_date' => [
            'label' => 'Policy Expiry Date',
            'description' => 'Date when policy expires',
            'category' => 'dates',
            'source' => 'insurance.expired_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '31-Dec-2025',
            'requires' => ['insurance'],
        ],

        'expired_date' => [
            'label' => 'Policy Expired Date',
            'description' => 'Date when policy expired (past tense)',
            'category' => 'dates',
            'source' => 'insurance.expired_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '31-Dec-2024',
            'requires' => ['insurance'],
        ],

        'issue_date' => [
            'label' => 'Policy Issue Date',
            'description' => 'Date when policy was issued',
            'category' => 'dates',
            'source' => 'insurance.issue_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '15-Dec-2024',
            'requires' => ['insurance'],
        ],

        'maturity_date' => [
            'label' => 'Maturity Date',
            'description' => 'Date when policy matures',
            'category' => 'dates',
            'source' => 'insurance.maturity_date',
            'type' => 'date',
            'format' => 'd-M-Y',
            'sample' => '01-Jan-2030',
            'requires' => ['insurance'],
        ],

        'days_remaining' => [
            'label' => 'Days Remaining',
            'description' => 'Number of days until policy expires',
            'category' => 'dates',
            'source' => 'computed:days_remaining',
            'type' => 'computed',
            'format' => null,
            'sample' => '30',
            'requires' => ['insurance'],
        ],

        'current_date' => [
            'label' => 'Current Date',
            'description' => 'Today\'s date',
            'category' => 'dates',
            'source' => 'system:current_date',
            'type' => 'system',
            'format' => 'd-M-Y',
            'sample' => '07-Oct-2025',
            'requires' => [],
        ],

        // =======================================================
        // VEHICLE VARIABLES
        // =======================================================

        'vehicle_number' => [
            'label' => 'Vehicle Number',
            'description' => 'Vehicle registration number',
            'category' => 'vehicle',
            'source' => 'insurance.registration_no',
            'type' => 'string',
            'format' => null,
            'sample' => 'GJ-01-AB-1234',
            'requires' => ['insurance'],
        ],

        'registration_no' => [
            'label' => 'Registration Number',
            'description' => 'Vehicle registration number',
            'category' => 'vehicle',
            'source' => 'insurance.registration_no',
            'type' => 'string',
            'format' => null,
            'sample' => 'GJ-01-AB-1234',
            'requires' => ['insurance'],
        ],

        'vehicle_make_model' => [
            'label' => 'Vehicle Make & Model',
            'description' => 'Make and model of the vehicle',
            'category' => 'vehicle',
            'source' => 'insurance.make_model',
            'type' => 'string',
            'format' => null,
            'sample' => 'Honda City VX',
            'requires' => ['insurance'],
        ],

        'rto' => [
            'label' => 'RTO',
            'description' => 'Regional Transport Office code',
            'category' => 'vehicle',
            'source' => 'insurance.rto',
            'type' => 'string',
            'format' => null,
            'sample' => 'GJ-01',
            'requires' => ['insurance'],
        ],

        'mfg_year' => [
            'label' => 'Manufacturing Year',
            'description' => 'Year of vehicle manufacturing',
            'category' => 'vehicle',
            'source' => 'insurance.mfg_year',
            'type' => 'string',
            'format' => null,
            'sample' => '2020',
            'requires' => ['insurance'],
        ],

        'idv_amount' => [
            'label' => 'IDV Amount',
            'description' => 'Insured Declared Value',
            'category' => 'vehicle',
            'source' => 'insurance.sum_insured',
            'type' => 'currency',
            'format' => 'currency',
            'sample' => '₹8,50,000',
            'requires' => ['insurance'],
        ],

        'fuel_type' => [
            'label' => 'Fuel Type',
            'description' => 'Type of fuel vehicle uses',
            'category' => 'vehicle',
            'source' => 'insurance.fuelType.name',
            'type' => 'string',
            'format' => null,
            'sample' => 'Petrol',
            'requires' => ['insurance'],
        ],

        // =======================================================
        // QUOTATION VARIABLES
        // =======================================================

        'quotes_count' => [
            'label' => 'Number of Quotes',
            'description' => 'Total number of quotes generated',
            'category' => 'quotation',
            'source' => 'quotation.quotationCompanies.count',
            'type' => 'number',
            'format' => null,
            'sample' => '5',
            'requires' => ['quotation'],
        ],

        'best_company_name' => [
            'label' => 'Best Quote Company',
            'description' => 'Insurance company with lowest premium',
            'category' => 'quotation',
            'source' => 'computed:best_company',
            'type' => 'computed',
            'format' => null,
            'sample' => 'HDFC ERGO',
            'requires' => ['quotation'],
        ],

        'best_premium' => [
            'label' => 'Best Premium Amount',
            'description' => 'Lowest premium amount quoted',
            'category' => 'quotation',
            'source' => 'computed:best_premium',
            'type' => 'computed',
            'format' => 'currency',
            'sample' => '₹4,500',
            'requires' => ['quotation'],
        ],

        'comparison_list' => [
            'label' => 'Quotes Comparison',
            'description' => 'Formatted list of all quotes',
            'category' => 'quotation',
            'source' => 'computed:comparison_list',
            'type' => 'computed',
            'format' => 'html',
            'sample' => '1. HDFC ERGO - ₹4,500\n2. ICICI Lombard - ₹4,800',
            'requires' => ['quotation'],
        ],

        // =======================================================
        // CLAIM VARIABLES
        // =======================================================

        'claim_number' => [
            'label' => 'Claim Number',
            'description' => 'Unique claim reference number',
            'category' => 'claim',
            'source' => 'claim.claim_number',
            'type' => 'string',
            'format' => null,
            'sample' => 'CLM-2025-001',
            'requires' => ['claim'],
        ],

        'claim_status' => [
            'label' => 'Claim Status',
            'description' => 'Current status of the claim',
            'category' => 'claim',
            'source' => 'claim.status',
            'type' => 'string',
            'format' => null,
            'sample' => 'In Progress',
            'requires' => ['claim'],
        ],

        'stage_name' => [
            'label' => 'Current Stage',
            'description' => 'Current stage of claim processing',
            'category' => 'claim',
            'source' => 'claim.currentStage.stage_name',
            'type' => 'string',
            'format' => null,
            'sample' => 'Document Verification',
            'requires' => ['claim'],
        ],

        'notes' => [
            'label' => 'Stage Notes',
            'description' => 'Notes from current claim stage',
            'category' => 'claim',
            'source' => 'claim.currentStage.notes',
            'type' => 'text',
            'format' => null,
            'sample' => 'Awaiting additional documents from customer',
            'requires' => ['claim'],
        ],

        'pending_documents_list' => [
            'label' => 'Pending Documents',
            'description' => 'List of documents pending for claim',
            'category' => 'claim',
            'source' => 'computed:pending_documents',
            'type' => 'computed',
            'format' => 'html',
            'sample' => '- Vehicle RC Copy\n- Police FIR',
            'requires' => ['claim'],
        ],

        // =======================================================
        // COMPANY/ADVISOR VARIABLES
        // =======================================================

        'advisor_name' => [
            'label' => 'Advisor Name',
            'description' => 'Name of the insurance advisor',
            'category' => 'company',
            'source' => 'setting:company.advisor_name',
            'type' => 'setting',
            'format' => null,
            'sample' => 'Your Trusted Advisor',
            'requires' => [],
        ],

        'company_name' => [
            'label' => 'Company Name',
            'description' => 'Name of your company',
            'category' => 'company',
            'source' => 'setting:company.name',
            'type' => 'setting',
            'format' => null,
            'sample' => 'Your Insurance Advisor',
            'requires' => [],
        ],

        'company_phone' => [
            'label' => 'Company Phone',
            'description' => 'Company contact phone number',
            'category' => 'company',
            'source' => 'setting:company.phone',
            'type' => 'setting',
            'format' => null,
            'sample' => '+91 98765 43210',
            'requires' => [],
        ],

        'company_email' => [
            'label' => 'Company Email',
            'description' => 'Company contact email address',
            'category' => 'company',
            'source' => 'setting:company.email',
            'type' => 'setting',
            'format' => null,
            'sample' => 'info@yourcompany.com',
            'requires' => [],
        ],

        'company_website' => [
            'label' => 'Company Website',
            'description' => 'Company website URL',
            'category' => 'company',
            'source' => 'setting:company.website',
            'type' => 'setting',
            'format' => null,
            'sample' => 'https://www.yourcompany.com',
            'requires' => [],
        ],

        'company_address' => [
            'label' => 'Company Address',
            'description' => 'Company physical address',
            'category' => 'company',
            'source' => 'setting:company.address',
            'type' => 'setting',
            'format' => null,
            'sample' => '123 Main Street, City - 380001',
            'requires' => [],
        ],

        'portal_url' => [
            'label' => 'Customer Portal URL',
            'description' => 'URL to customer portal',
            'category' => 'company',
            'source' => 'setting:application.portal_url',
            'type' => 'setting',
            'format' => null,
            'sample' => 'https://portal.yourcompany.com',
            'requires' => [],
        ],

        'whatsapp_number' => [
            'label' => 'WhatsApp Number',
            'description' => 'Company WhatsApp contact number',
            'category' => 'company',
            'source' => 'setting:whatsapp.sender_id',
            'type' => 'setting',
            'format' => null,
            'sample' => '919876543210',
            'requires' => [],
        ],

        'support_email' => [
            'label' => 'Support Email',
            'description' => 'Customer support email address',
            'category' => 'company',
            'source' => 'setting:application.support_email',
            'type' => 'setting',
            'format' => null,
            'sample' => 'support@yourcompany.com',
            'requires' => [],
        ],

        // =======================================================
        // ATTACHMENT VARIABLES
        // =======================================================

        '@policy_document' => [
            'label' => 'Policy Document PDF',
            'description' => 'Attach policy document file',
            'category' => 'attachments',
            'source' => 'insurance.policy_document_path',
            'type' => 'attachment',
            'format' => null,
            'sample' => '[Policy Document Attached]',
            'requires' => ['insurance'],
            'variable_format' => '@policy_document',
        ],

        '@customer_pan' => [
            'label' => 'Customer PAN Card',
            'description' => 'Attach customer PAN card',
            'category' => 'attachments',
            'source' => 'customer.pan_card_path',
            'type' => 'attachment',
            'format' => null,
            'sample' => '[PAN Card Attached]',
            'requires' => ['customer'],
            'variable_format' => '@customer_pan',
        ],

        '@customer_aadhar' => [
            'label' => 'Customer Aadhar Card',
            'description' => 'Attach customer Aadhar card',
            'category' => 'attachments',
            'source' => 'customer.aadhar_card_path',
            'type' => 'attachment',
            'format' => null,
            'sample' => '[Aadhar Card Attached]',
            'requires' => ['customer'],
            'variable_format' => '@customer_aadhar',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Type Configurations
    |--------------------------------------------------------------------------
    |
    | Define context requirements and suggested variables per notification type
    |
    */
    'notification_types' => [
        'birthday_wish' => [
            'required_context' => ['customer'],
            'suggested_variables' => ['customer_name', 'date_of_birth', 'company_name', 'advisor_name', 'company_website'],
        ],
        'customer_welcome' => [
            'required_context' => ['customer'],
            'suggested_variables' => ['customer_name', 'portal_url', 'company_name', 'company_phone', 'support_email'],
        ],
        'policy_created' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'insurance_company', 'policy_type', 'premium_amount', 'start_date', 'expiry_date'],
        ],
        'renewal_reminder_30_days' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'vehicle_number', 'expiry_date', 'days_remaining', 'insurance_company', 'company_phone'],
        ],
        'renewal_reminder_15_days' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'vehicle_number', 'expiry_date', 'days_remaining', 'insurance_company', 'company_phone'],
        ],
        'renewal_reminder_7_days' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'vehicle_number', 'expiry_date', 'days_remaining', 'insurance_company', 'company_phone'],
        ],
        'renewal_reminder_expired' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'vehicle_number', 'expired_date', 'insurance_company', 'company_phone'],
        ],
        'quotation_ready' => [
            'required_context' => ['customer', 'quotation'],
            'suggested_variables' => ['customer_name', 'quotes_count', 'vehicle_make_model', 'best_company_name', 'best_premium', 'comparison_list'],
        ],
        'claim_initiated' => [
            'required_context' => ['customer', 'claim'],
            'suggested_variables' => ['customer_name', 'claim_number', 'policy_number', 'vehicle_number', 'company_phone'],
        ],
        'claim_stage_update' => [
            'required_context' => ['customer', 'claim'],
            'suggested_variables' => ['customer_name', 'claim_number', 'stage_name', 'notes', 'pending_documents_list'],
        ],
        'wedding_anniversary' => [
            'required_context' => ['customer'],
            'suggested_variables' => ['customer_name', 'wedding_anniversary', 'company_name', 'company_website'],
        ],
        'engagement_anniversary' => [
            'required_context' => ['customer'],
            'suggested_variables' => ['customer_name', 'engagement_anniversary', 'company_name', 'company_website'],
        ],
    ],

];
