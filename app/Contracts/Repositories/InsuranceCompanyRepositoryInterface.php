<?php

namespace App\Contracts\Repositories;

use App\Models\InsuranceCompany;

/**
 * Insurance Company Repository Interface
 *
 * Extends base repository functionality for InsuranceCompany-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface InsuranceCompanyRepositoryInterface extends BaseRepositoryInterface
{
    // All common methods inherited from BaseRepositoryInterface
    // Add insurance-company-specific methods here if needed in the future
}
