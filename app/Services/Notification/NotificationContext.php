<?php

namespace App\Services\Notification;

use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\Quotation;

/**
 * Notification Context
 *
 * Holds all entities and data needed for variable resolution
 */
class NotificationContext
{
    public ?Customer $customer = null;

    public ?CustomerInsurance $insurance = null;

    public ?Quotation $quotation = null;

    public ?Claim $claim = null;

    public array $settings = [];

    public array $customData = [];

    /**
     * Create new context instance
     *
     * @param  array  $data  Associative array with 'customer', 'insurance', 'quotation', 'claim', 'settings', 'customData'
     */
    public function __construct(array $data = [])
    {
        if (isset($data['customer'])) {
            $this->customer = $data['customer'];
        }

        if (isset($data['insurance'])) {
            $this->insurance = $data['insurance'];
        }

        if (isset($data['quotation'])) {
            $this->quotation = $data['quotation'];
        }

        if (isset($data['claim'])) {
            $this->claim = $data['claim'];
        }

        if (isset($data['settings'])) {
            $this->settings = $data['settings'];
        }

        if (isset($data['customData'])) {
            $this->customData = $data['customData'];
        }
    }

    /**
     * Check if context has customer
     */
    public function hasCustomer(): bool
    {
        return $this->customer instanceof Customer;
    }

    /**
     * Check if context has insurance
     */
    public function hasInsurance(): bool
    {
        return $this->insurance instanceof CustomerInsurance;
    }

    /**
     * Check if context has quotation
     */
    public function hasQuotation(): bool
    {
        return $this->quotation instanceof Quotation;
    }

    /**
     * Check if context has claim
     */
    public function hasClaim(): bool
    {
        return $this->claim instanceof Claim;
    }

    /**
     * Check if context has required entities
     *
     * @param  array  $required  Array of required entity names (e.g., ['customer', 'insurance'])
     */
    public function hasRequiredContext(array $required): bool
    {
        foreach ($required as $entity) {
            $method = 'has'.ucfirst((string) $entity);
            if (method_exists($this, $method) && ! $this->$method()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get setting value by key
     *
     * @param  string  $key  Setting key (supports dot notation: 'company.name')
     */
    public function getSetting(string $key): mixed
    {
        // Support dot notation
        $keys = explode('.', $key);
        $value = $this->settings;

        foreach ($keys as $k) {
            if (! isset($value[$k])) {
                return null;
            }

            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set setting value
     *
     * @param  string  $key  Setting key
     * @param  mixed  $value  Setting value
     */
    public function setSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    /**
     * Get custom data by key
     *
     * @param  string  $key  Custom data key
     * @param  mixed  $default  Default value if key not found
     */
    public function getCustomData(string $key, mixed $default = null): mixed
    {
        return $this->customData[$key] ?? $default;
    }

    /**
     * Set custom data
     *
     * @param  string  $key  Custom data key
     * @param  mixed  $value  Custom data value
     */
    public function setCustomData(string $key, mixed $value): void
    {
        $this->customData[$key] = $value;
    }

    /**
     * Create context from customer ID
     *
     * @param  int  $customerId  Customer ID
     * @param  int|null  $insuranceId  Optional insurance ID
     */
    public static function fromCustomerId(int $customerId, ?int $insuranceId = null): static
    {
        $customer = Customer::with([
            'familyGroup',
            'customerType',
        ])->find($customerId);

        $insurance = null;
        if ($insuranceId !== null && $insuranceId !== 0) {
            $insurance = CustomerInsurance::with([
                'insuranceCompany',
                'policyType',
                'premiumType',
                'fuelType',
                'branch',
                'broker',
            ])->find($insuranceId);
        } elseif ($customer) {
            // Get first active insurance if no specific insurance ID
            $insurance = $customer->insurance()
                ->with([
                    'insuranceCompany',
                    'policyType',
                    'premiumType',
                    'fuelType',
                    'branch',
                    'broker',
                ])
                ->where('status', true)
                ->first();
        }

        return new static([
            'customer' => $customer,
            'insurance' => $insurance,
        ]);
    }

    /**
     * Create context from insurance ID
     *
     * @param  int  $insuranceId  Insurance ID
     */
    public static function fromInsuranceId(int $insuranceId): static
    {
        $insurance = CustomerInsurance::with([
            'customer',
            'insuranceCompany',
            'policyType',
            'premiumType',
            'fuelType',
            'branch',
            'broker',
        ])->find($insuranceId);

        return new static([
            'customer' => $insurance?->customer,
            'insurance' => $insurance,
        ]);
    }

    /**
     * Create context from quotation ID
     *
     * @param  int  $quotationId  Quotation ID
     */
    public static function fromQuotationId(int $quotationId): static
    {
        $quotation = Quotation::with([
            'customer',
            'quotationCompanies.insuranceCompany',
        ])->find($quotationId);

        return new static([
            'customer' => $quotation?->customer,
            'quotation' => $quotation,
        ]);
    }

    /**
     * Create context from claim ID
     *
     * @param  int  $claimId  Claim ID
     */
    public static function fromClaimId(int $claimId): static
    {
        $claim = Claim::with([
            'customer',
            'customerInsurance.insuranceCompany',
            'stages',
        ])->find($claimId);

        return new static([
            'customer' => $claim?->customer,
            'insurance' => $claim?->customerInsurance,
            'claim' => $claim,
        ]);
    }

    /**
     * Create sample context with random real data
     */
    public static function sample(): static
    {
        $customer = Customer::query()->where('status', true)
            ->with(['familyGroup', 'customerType'])
            ->inRandomOrder()
            ->first();

        $insurance = CustomerInsurance::query()->where('status', true)
            ->with([
                'insuranceCompany',
                'policyType',
                'premiumType',
                'fuelType',
                'branch',
                'broker',
            ])
            ->inRandomOrder()
            ->first();

        return new static([
            'customer' => $customer,
            'insurance' => $insurance,
        ]);
    }

    /**
     * Convert context to array for debugging
     */
    public function toArray(): array
    {
        return [
            'customer' => $this->customer?->toArray(),
            'insurance' => $this->insurance?->toArray(),
            'quotation' => $this->quotation?->toArray(),
            'claim' => $this->claim?->toArray(),
            'settings' => $this->settings,
            'customData' => $this->customData,
        ];
    }
}
