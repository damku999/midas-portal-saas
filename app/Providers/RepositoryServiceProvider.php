<?php

namespace App\Providers;

use App\Contracts\Repositories\AddonCoverRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\BrokerRepositoryInterface;
use App\Contracts\Repositories\ClaimRepositoryInterface;
use App\Contracts\Repositories\CustomerInsuranceRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\FamilyGroupRepositoryInterface;
use App\Contracts\Repositories\FuelTypeRepositoryInterface;
use App\Contracts\Repositories\InsuranceCompanyRepositoryInterface;
use App\Contracts\Repositories\MarketingWhatsAppRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\PolicyRepositoryInterface;
use App\Contracts\Repositories\PolicyTypeRepositoryInterface;
use App\Contracts\Repositories\PremiumTypeRepositoryInterface;
use App\Contracts\Repositories\QuotationRepositoryInterface;
use App\Contracts\Repositories\ReferenceUserRepositoryInterface;
use App\Contracts\Repositories\RelationshipManagerRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Contracts\Services\AddonCoverServiceInterface;
use App\Contracts\Services\BranchServiceInterface;
use App\Contracts\Services\BrokerServiceInterface;
use App\Contracts\Services\ClaimServiceInterface;
use App\Contracts\Services\CustomerInsuranceServiceInterface;
use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\FamilyGroupServiceInterface;
use App\Contracts\Services\FuelTypeServiceInterface;
use App\Contracts\Services\InsuranceCompanyServiceInterface;
use App\Contracts\Services\MarketingWhatsAppServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\PolicyServiceInterface;
use App\Contracts\Services\PolicyTypeServiceInterface;
use App\Contracts\Services\PremiumTypeServiceInterface;
use App\Contracts\Services\QuotationServiceInterface;
use App\Contracts\Services\ReferenceUserServiceInterface;
use App\Contracts\Services\RelationshipManagerServiceInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\AddonCoverRepository;
use App\Repositories\BranchRepository;
use App\Repositories\BrokerRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\CustomerInsuranceRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\FamilyGroupRepository;
use App\Repositories\FuelTypeRepository;
use App\Repositories\InsuranceCompanyRepository;
use App\Repositories\MarketingWhatsAppRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\PolicyRepository;
use App\Repositories\PolicyTypeRepository;
use App\Repositories\PremiumTypeRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ReferenceUserRepository;
use App\Repositories\RelationshipManagerRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\LeadRepository;
use App\Services\AddonCoverService;
use App\Services\BranchService;
use App\Services\BrokerService;
use App\Services\ClaimService;
use App\Services\CustomerInsuranceService;
use App\Services\CustomerService;
use App\Services\FamilyGroupService;
use App\Services\FuelTypeService;
use App\Services\InsuranceCompanyService;
use App\Services\MarketingWhatsAppService;
use App\Services\PermissionService;
use App\Services\PolicyService;
use App\Services\PolicyTypeService;
use App\Services\PremiumTypeService;
use App\Services\QuotationService;
use App\Services\ReferenceUserService;
use App\Services\RelationshipManagerService;
use App\Services\ReportService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(AddonCoverRepositoryInterface::class, AddonCoverRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BrokerRepositoryInterface::class, BrokerRepository::class);
        $this->app->bind(ClaimRepositoryInterface::class, ClaimRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CustomerInsuranceRepositoryInterface::class, CustomerInsuranceRepository::class);
        $this->app->bind(FamilyGroupRepositoryInterface::class, FamilyGroupRepository::class);
        $this->app->bind(FuelTypeRepositoryInterface::class, FuelTypeRepository::class);
        $this->app->bind(InsuranceCompanyRepositoryInterface::class, InsuranceCompanyRepository::class);
        $this->app->bind(MarketingWhatsAppRepositoryInterface::class, MarketingWhatsAppRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(PolicyRepositoryInterface::class, PolicyRepository::class);
        $this->app->bind(PolicyTypeRepositoryInterface::class, PolicyTypeRepository::class);
        $this->app->bind(PremiumTypeRepositoryInterface::class, PremiumTypeRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, QuotationRepository::class);
        $this->app->bind(ReferenceUserRepositoryInterface::class, ReferenceUserRepository::class);
        $this->app->bind(RelationshipManagerRepositoryInterface::class, RelationshipManagerRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);

        // Service bindings
        $this->app->bind(AddonCoverServiceInterface::class, AddonCoverService::class);
        $this->app->bind(BranchServiceInterface::class, BranchService::class);
        $this->app->bind(BrokerServiceInterface::class, BrokerService::class);
        $this->app->bind(ClaimServiceInterface::class, ClaimService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(CustomerInsuranceServiceInterface::class, CustomerInsuranceService::class);
        $this->app->bind(FamilyGroupServiceInterface::class, FamilyGroupService::class);
        $this->app->bind(FuelTypeServiceInterface::class, FuelTypeService::class);
        $this->app->bind(InsuranceCompanyServiceInterface::class, InsuranceCompanyService::class);
        $this->app->bind(MarketingWhatsAppServiceInterface::class, MarketingWhatsAppService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(PolicyServiceInterface::class, PolicyService::class);
        $this->app->bind(PolicyTypeServiceInterface::class, PolicyTypeService::class);
        $this->app->bind(PremiumTypeServiceInterface::class, PremiumTypeService::class);
        $this->app->bind(QuotationServiceInterface::class, QuotationService::class);
        $this->app->bind(ReferenceUserServiceInterface::class, ReferenceUserService::class);
        $this->app->bind(RelationshipManagerServiceInterface::class, RelationshipManagerService::class);
        $this->app->bind(ReportServiceInterface::class, ReportService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
