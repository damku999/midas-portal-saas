<?php

namespace App\Services;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Services\BranchServiceInterface;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchService extends BaseService implements BranchServiceInterface
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository
    ) {}

    public function getBranches(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        return $this->branchRepository->getBranchesWithFilters($request, $perPage);
    }

    public function createBranch(array $data): Branch
    {
        return $this->createInTransaction(fn (): Model => $this->branchRepository->create($data));
    }

    public function updateBranch(Branch $branch, array $data): bool
    {
        return $this->updateInTransaction(fn (): Model => $this->branchRepository->update($branch, $data));
    }

    public function deleteBranch(Branch $branch): bool
    {
        return $this->deleteInTransaction(fn (): bool => $this->branchRepository->delete($branch));
    }

    public function updateBranchStatus(int $branchId, bool $status): bool
    {
        return $this->updateInTransaction(function () use ($branchId, $status): false|Model {
            $branch = $this->branchRepository->findById($branchId);
            if (! $branch instanceof Model) {
                return false;
            }

            return $this->branchRepository->update($branch, ['status' => $status]);
        });
    }

    public function getActiveBranches(): Collection
    {
        return $this->branchRepository->getActiveBranches();
    }

    public function getBranchWithInsurancesCount(int $branchId): ?Branch
    {
        return $this->branchRepository->getBranchWithInsurancesCount($branchId);
    }

    public function searchBranches(string $searchTerm, int $limit = 20): Collection
    {
        return $this->branchRepository->searchBranches($searchTerm, $limit);
    }

    public function getBranchStatistics(): array
    {
        return $this->branchRepository->getBranchStatistics();
    }

    public function getAllBranchesForExport(): Collection
    {
        return $this->branchRepository->getAllBranchesForExport();
    }
}
