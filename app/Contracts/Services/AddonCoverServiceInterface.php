<?php

namespace App\Contracts\Services;

use App\Models\AddonCover;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface AddonCoverServiceInterface
{
    public function getAddonCovers(Request $request): LengthAwarePaginator;

    public function createAddonCover(array $data): AddonCover;

    public function updateAddonCover(AddonCover $addonCover, array $data): AddonCover;

    public function deleteAddonCover(AddonCover $addonCover): bool;

    public function updateStatus(int $addonCoverId, int $status): bool;

    public function exportAddonCovers(): \Symfony\Component\HttpFoundation\BinaryFileResponse;

    public function getActiveAddonCovers(): \Illuminate\Database\Eloquent\Collection;

    public function getStoreValidationRules(): array;

    public function getUpdateValidationRules(AddonCover $addonCover): array;
}
