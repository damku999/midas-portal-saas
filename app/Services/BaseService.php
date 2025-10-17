<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Base Service Class
 *
 * Provides common service functionality including transaction management.
 * Eliminates duplicate transaction wrapper code across all service classes.
 */
abstract class BaseService
{
    /**
     * Execute a callback within a database transaction
     *
     * This method provides a standardized way to handle database transactions
     * across all service classes, ensuring consistent error handling and rollback behavior.
     *
     * @param  callable  $callback  The operation to execute within the transaction
     * @return mixed The result of the callback execution
     *
     * @throws \Throwable Any exception thrown by the callback will be re-thrown after rollback
     */
    protected function executeInTransaction(callable $callback)
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();

            return $result;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

    /**
     * Execute a create operation within a transaction
     *
     * Convenience method for creation operations that need transaction safety.
     *
     * @param  callable  $createCallback  The create operation to execute
     * @return mixed The created entity
     *
     * @throws \Throwable
     */
    protected function createInTransaction(callable $createCallback)
    {
        return $this->executeInTransaction($createCallback);
    }

    /**
     * Execute an update operation within a transaction
     *
     * Convenience method for update operations that need transaction safety.
     *
     * @param  callable  $updateCallback  The update operation to execute
     * @return mixed The updated entity
     *
     * @throws \Throwable
     */
    protected function updateInTransaction(callable $updateCallback)
    {
        return $this->executeInTransaction($updateCallback);
    }

    /**
     * Execute a delete operation within a transaction
     *
     * Convenience method for delete operations that need transaction safety.
     *
     * @param  callable  $deleteCallback  The delete operation to execute
     * @return mixed The result of the delete operation
     *
     * @throws \Throwable
     */
    protected function deleteInTransaction(callable $deleteCallback)
    {
        return $this->executeInTransaction($deleteCallback);
    }

    /**
     * Execute multiple operations within a single transaction
     *
     * Useful for complex operations that require multiple repository calls
     * to be atomic.
     *
     * @param  array  $callbacks  Array of callbacks to execute sequentially
     * @return array Array of results from each callback
     *
     * @throws \Throwable
     */
    protected function executeMultipleInTransaction(array $callbacks): array
    {
        return $this->executeInTransaction(static function () use ($callbacks) {
            $results = [];
            foreach ($callbacks as $callback) {
                $results[] = $callback();
            }

            return $results;
        });
    }
}
