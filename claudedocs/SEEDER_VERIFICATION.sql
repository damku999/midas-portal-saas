-- ============================================================================
-- DATABASE SEEDER VERIFICATION SCRIPT
-- ============================================================================
-- Purpose: Verify all seeders ran successfully and data integrity is maintained
-- Run this after: php artisan migrate:fresh --seed
-- ============================================================================

-- 1. MASTER DATA TABLE COUNTS
-- ============================================================================
SELECT
    'MASTER DATA COUNTS' as verification_type,
    '' as table_name,
    NULL as count,
    NULL as expected,
    NULL as status
UNION ALL
SELECT
    '',
    'branches',
    CAST(COUNT(*) as CHAR),
    '10',
    CASE WHEN COUNT(*) = 10 THEN 'PASS' ELSE 'FAIL' END
FROM branches
UNION ALL
SELECT
    '',
    'brokers',
    CAST(COUNT(*) as CHAR),
    '6',
    CASE WHEN COUNT(*) >= 6 THEN 'PASS' ELSE 'FAIL' END
FROM brokers
UNION ALL
SELECT
    '',
    'relationship_managers',
    CAST(COUNT(*) as CHAR),
    '8',
    CASE WHEN COUNT(*) >= 8 THEN 'PASS' ELSE 'FAIL' END
FROM relationship_managers
UNION ALL
SELECT
    '',
    'reference_users',
    CAST(COUNT(*) as CHAR),
    '14',
    CASE WHEN COUNT(*) >= 14 THEN 'PASS' ELSE 'FAIL' END
FROM reference_users
UNION ALL
SELECT
    '',
    'insurance_companies',
    CAST(COUNT(*) as CHAR),
    '20',
    CASE WHEN COUNT(*) >= 20 THEN 'PASS' ELSE 'FAIL' END
FROM insurance_companies
UNION ALL
SELECT
    '',
    'fuel_types',
    CAST(COUNT(*) as CHAR),
    '4',
    CASE WHEN COUNT(*) = 4 THEN 'PASS' ELSE 'FAIL' END
FROM fuel_types
UNION ALL
SELECT
    '',
    'policy_types',
    CAST(COUNT(*) as CHAR),
    '3',
    CASE WHEN COUNT(*) = 3 THEN 'PASS' ELSE 'FAIL' END
FROM policy_types
UNION ALL
SELECT
    '',
    'premium_types',
    CAST(COUNT(*) as CHAR),
    '35',
    CASE WHEN COUNT(*) >= 35 THEN 'PASS' ELSE 'FAIL' END
FROM premium_types
UNION ALL
SELECT
    '',
    'addon_covers',
    CAST(COUNT(*) as CHAR),
    '9',
    CASE WHEN COUNT(*) >= 9 THEN 'PASS' ELSE 'FAIL' END
FROM addon_covers
UNION ALL
SELECT
    '',
    'customer_types',
    CAST(COUNT(*) as CHAR),
    '2+',
    CASE WHEN COUNT(*) >= 2 THEN 'PASS' ELSE 'FAIL' END
FROM customer_types;

-- ============================================================================
-- 2. AUDIT TRAIL INTEGRITY
-- ============================================================================
SELECT
    'AUDIT TRAIL INTEGRITY' as verification_type,
    '' as table_name,
    NULL as missing_created_by,
    NULL as missing_timestamps,
    NULL as status
UNION ALL
SELECT
    '',
    'branches',
    CAST((SELECT COUNT(*) FROM branches WHERE created_by IS NULL) as CHAR),
    CAST((SELECT COUNT(*) FROM branches WHERE created_at IS NULL OR updated_at IS NULL) as CHAR),
    CASE
        WHEN (SELECT COUNT(*) FROM branches WHERE created_by IS NULL OR created_at IS NULL) = 0
        THEN 'PASS' ELSE 'FAIL'
    END
UNION ALL
SELECT
    '',
    'brokers',
    CAST((SELECT COUNT(*) FROM brokers WHERE created_by IS NULL) as CHAR),
    CAST((SELECT COUNT(*) FROM brokers WHERE created_at IS NULL OR updated_at IS NULL) as CHAR),
    CASE
        WHEN (SELECT COUNT(*) FROM brokers WHERE created_by IS NULL OR created_at IS NULL) = 0
        THEN 'PASS' ELSE 'FAIL'
    END
UNION ALL
SELECT
    '',
    'relationship_managers',
    CAST((SELECT COUNT(*) FROM relationship_managers WHERE created_by IS NULL) as CHAR),
    CAST((SELECT COUNT(*) FROM relationship_managers WHERE created_at IS NULL OR updated_at IS NULL) as CHAR),
    CASE
        WHEN (SELECT COUNT(*) FROM relationship_managers WHERE created_by IS NULL OR created_at IS NULL) = 0
        THEN 'PASS' ELSE 'FAIL'
    END
UNION ALL
SELECT
    '',
    'reference_users',
    CAST((SELECT COUNT(*) FROM reference_users WHERE created_by IS NULL) as CHAR),
    CAST((SELECT COUNT(*) FROM reference_users WHERE created_at IS NULL OR updated_at IS NULL) as CHAR),
    CASE
        WHEN (SELECT COUNT(*) FROM reference_users WHERE created_by IS NULL OR created_at IS NULL) = 0
        THEN 'PASS' ELSE 'FAIL'
    END;

-- ============================================================================
-- 3. STATUS FIELD VERIFICATION (All Active)
-- ============================================================================
SELECT
    'STATUS VERIFICATION' as verification_type,
    '' as table_name,
    NULL as total_records,
    NULL as active_records,
    NULL as status
UNION ALL
SELECT
    '',
    'branches',
    CAST(COUNT(*) as CHAR),
    CAST(SUM(status) as CHAR),
    CASE WHEN COUNT(*) = SUM(status) THEN 'PASS' ELSE 'FAIL' END
FROM branches
UNION ALL
SELECT
    '',
    'brokers',
    CAST(COUNT(*) as CHAR),
    CAST(SUM(status) as CHAR),
    CASE WHEN COUNT(*) = SUM(status) THEN 'PASS' ELSE 'FAIL' END
FROM brokers
UNION ALL
SELECT
    '',
    'relationship_managers',
    CAST(COUNT(*) as CHAR),
    CAST(SUM(status) as CHAR),
    CASE WHEN COUNT(*) = SUM(status) THEN 'PASS' ELSE 'FAIL' END
FROM relationship_managers
UNION ALL
SELECT
    '',
    'reference_users',
    CAST(COUNT(*) as CHAR),
    CAST(SUM(status) as CHAR),
    CASE WHEN COUNT(*) = SUM(status) THEN 'PASS' ELSE 'FAIL' END
FROM reference_users;

-- ============================================================================
-- 4. SOFT DELETE VERIFICATION (None Should Be Deleted)
-- ============================================================================
SELECT
    'SOFT DELETE VERIFICATION' as verification_type,
    '' as table_name,
    NULL as deleted_records,
    NULL as expected,
    NULL as status
UNION ALL
SELECT
    '',
    'branches',
    CAST((SELECT COUNT(*) FROM branches WHERE deleted_at IS NOT NULL) as CHAR),
    '0',
    CASE WHEN (SELECT COUNT(*) FROM branches WHERE deleted_at IS NOT NULL) = 0 THEN 'PASS' ELSE 'FAIL' END
UNION ALL
SELECT
    '',
    'brokers',
    CAST((SELECT COUNT(*) FROM brokers WHERE deleted_at IS NOT NULL) as CHAR),
    '0',
    CASE WHEN (SELECT COUNT(*) FROM brokers WHERE deleted_at IS NOT NULL) = 0 THEN 'PASS' ELSE 'FAIL' END
UNION ALL
SELECT
    '',
    'relationship_managers',
    CAST((SELECT COUNT(*) FROM relationship_managers WHERE deleted_at IS NOT NULL) as CHAR),
    '0',
    CASE WHEN (SELECT COUNT(*) FROM relationship_managers WHERE deleted_at IS NOT NULL) = 0 THEN 'PASS' ELSE 'FAIL' END
UNION ALL
SELECT
    '',
    'reference_users',
    CAST((SELECT COUNT(*) FROM reference_users WHERE deleted_at IS NOT NULL) as CHAR),
    '0',
    CASE WHEN (SELECT COUNT(*) FROM reference_users WHERE deleted_at IS NOT NULL) = 0 THEN 'PASS' ELSE 'FAIL' END;

-- ============================================================================
-- 5. SYSTEM DATA VERIFICATION
-- ============================================================================
SELECT
    'SYSTEM DATA VERIFICATION' as verification_type,
    '' as table_name,
    NULL as count,
    NULL as expected,
    NULL as status
UNION ALL
SELECT
    '',
    'users (admin)',
    CAST(COUNT(*) as CHAR),
    '1+',
    CASE WHEN COUNT(*) >= 1 THEN 'PASS' ELSE 'FAIL' END
FROM users WHERE role_id = 1
UNION ALL
SELECT
    '',
    'roles',
    CAST(COUNT(*) as CHAR),
    '2',
    CASE WHEN COUNT(*) >= 2 THEN 'PASS' ELSE 'FAIL' END
FROM roles
UNION ALL
SELECT
    '',
    'permissions',
    CAST(COUNT(*) as CHAR),
    '50+',
    CASE WHEN COUNT(*) >= 50 THEN 'PASS' ELSE 'FAIL' END
FROM permissions;

-- ============================================================================
-- 6. DATA QUALITY CHECKS
-- ============================================================================

-- Check for duplicate names in branches
SELECT
    'DATA QUALITY - Duplicates' as check_type,
    'branches' as table_name,
    name,
    COUNT(*) as duplicate_count,
    CASE WHEN COUNT(*) > 1 THEN 'WARNING' ELSE 'OK' END as status
FROM branches
GROUP BY name
HAVING COUNT(*) > 1;

-- Check for missing names
SELECT
    'DATA QUALITY - Missing Names' as check_type,
    'branches' as table_name,
    COUNT(*) as records_with_null_name,
    CASE WHEN COUNT(*) > 0 THEN 'FAIL' ELSE 'PASS' END as status
FROM branches
WHERE name IS NULL OR TRIM(name) = '';

-- ============================================================================
-- 7. FOREIGN KEY READINESS CHECK
-- ============================================================================
-- Verify that customer_insurances can reference all master data

SELECT
    'FOREIGN KEY READINESS' as verification_type,
    '' as foreign_key,
    NULL as target_table,
    NULL as available_records,
    NULL as status
UNION ALL
SELECT
    '',
    'branch_id',
    'branches',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM branches WHERE status = 1
UNION ALL
SELECT
    '',
    'broker_id',
    'brokers',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM brokers WHERE status = 1
UNION ALL
SELECT
    '',
    'relationship_manager_id',
    'relationship_managers',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM relationship_managers WHERE status = 1
UNION ALL
SELECT
    '',
    'insurance_company_id',
    'insurance_companies',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM insurance_companies WHERE status = 1
UNION ALL
SELECT
    '',
    'fuel_type_id',
    'fuel_types',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM fuel_types WHERE status = 1
UNION ALL
SELECT
    '',
    'policy_type_id',
    'policy_types',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM policy_types WHERE status = 1
UNION ALL
SELECT
    '',
    'premium_type_id',
    'premium_types',
    CAST(COUNT(*) as CHAR),
    CASE WHEN COUNT(*) > 0 THEN 'PASS' ELSE 'FAIL' END
FROM premium_types WHERE status = 1;

-- ============================================================================
-- 8. SAMPLE DATA PREVIEW
-- ============================================================================

-- Preview new seeded data
SELECT 'NEW BRANCHES' as data_type, id, name, status FROM branches ORDER BY id LIMIT 5;
SELECT 'NEW BROKERS' as data_type, id, name, email FROM brokers ORDER BY id LIMIT 5;
SELECT 'NEW RMs' as data_type, id, name, email FROM relationship_managers ORDER BY id LIMIT 5;
SELECT 'NEW REFERENCES' as data_type, id, name FROM reference_users ORDER BY id LIMIT 5;

-- ============================================================================
-- SUMMARY REPORT
-- ============================================================================
SELECT
    'SUMMARY' as report_section,
    'Total Master Data Tables' as metric,
    '14' as value
UNION ALL
SELECT
    '',
    'Total Seed Records',
    CAST((
        (SELECT COUNT(*) FROM branches) +
        (SELECT COUNT(*) FROM brokers) +
        (SELECT COUNT(*) FROM relationship_managers) +
        (SELECT COUNT(*) FROM reference_users) +
        (SELECT COUNT(*) FROM insurance_companies) +
        (SELECT COUNT(*) FROM fuel_types) +
        (SELECT COUNT(*) FROM policy_types) +
        (SELECT COUNT(*) FROM premium_types) +
        (SELECT COUNT(*) FROM addon_covers) +
        (SELECT COUNT(*) FROM customer_types)
    ) as CHAR)
UNION ALL
SELECT
    '',
    'Active Branches',
    CAST((SELECT COUNT(*) FROM branches WHERE status = 1) as CHAR)
UNION ALL
SELECT
    '',
    'Active Brokers',
    CAST((SELECT COUNT(*) FROM brokers WHERE status = 1) as CHAR)
UNION ALL
SELECT
    '',
    'Active Relationship Managers',
    CAST((SELECT COUNT(*) FROM relationship_managers WHERE status = 1) as CHAR)
UNION ALL
SELECT
    '',
    'Active Reference Sources',
    CAST((SELECT COUNT(*) FROM reference_users WHERE status = 1) as CHAR);
