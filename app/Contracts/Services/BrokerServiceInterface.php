<?php

namespace App\Contracts\Services;

use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface BrokerServiceInterface
{
    public function getBrokers(Request $request): LengthAwarePaginator;

    public function createBroker(array $data): Broker;

    public function updateBroker(Broker $broker, array $data): Broker;

    public function deleteBroker(Broker $broker): bool;

    public function updateStatus(int $brokerId, int $status): bool;

    public function exportBrokers(): \Symfony\Component\HttpFoundation\BinaryFileResponse;

    public function getActiveBrokers(): \Illuminate\Database\Eloquent\Collection;
}
