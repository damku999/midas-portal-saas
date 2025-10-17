<?php

namespace App\Repositories;

use App\Contracts\Repositories\InsuranceCompanyRepositoryInterface;
use App\Models\InsuranceCompany;

/**
 * Insurance Company Repository
 *
 * Extends base repository functionality for InsuranceCompany-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class InsuranceCompanyRepository extends AbstractBaseRepository implements InsuranceCompanyRepositoryInterface
{
    protected string $modelClass = InsuranceCompany::class;

    protected array $searchableFields = ['name', 'email', 'mobile_number'];
}
