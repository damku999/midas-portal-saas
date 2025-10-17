<?php

namespace App\Services;

use App\Contracts\Repositories\BrokerRepositoryInterface;
use App\Contracts\Services\BrokerServiceInterface;
use App\Exports\BrokerExport;
use App\Models\Broker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Broker Service
 *
 * Handles Broker business logic operations.
 * Inherits transaction management from BaseService.
 */
class BrokerService extends BaseService implements BrokerServiceInterface
{
    public function __construct(
        private readonly BrokerRepositoryInterface $brokerRepository
    ) {}

    public function getBrokers(Request $request): LengthAwarePaginator
    {
        return $this->brokerRepository->getPaginated($request);
    }

    public function createBroker(array $data): Broker
    {
        return $this->createInTransaction(
            fn (): Model => $this->brokerRepository->create($data)
        );
    }

    public function updateBroker(Broker $broker, array $data): Broker
    {
        return $this->updateInTransaction(
            fn (): Model => $this->brokerRepository->update($broker, $data)
        );
    }

    public function deleteBroker(Broker $broker): bool
    {
        return $this->deleteInTransaction(
            fn (): bool => $this->brokerRepository->delete($broker)
        );
    }

    public function updateStatus(int $brokerId, int $status): bool
    {
        return $this->executeInTransaction(
            fn (): bool => $this->brokerRepository->updateStatus($brokerId, $status)
        );
    }

    public function exportBrokers(): BinaryFileResponse
    {
        return Excel::download(new BrokerExport, 'brokers.xlsx');
    }

    public function getActiveBrokers(): Collection
    {
        return $this->brokerRepository->getActive();
    }
}
