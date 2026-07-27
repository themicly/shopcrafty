<?php

namespace Themicly\Shopcrafty\Modules\Customers\Services;

use Themicly\Shopcrafty\Modules\Customers\Events\CustomerRegistered;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;

class CustomerService
{
    /** Resolve a customer by an email or (normalized) mobile identifier. */
    public function findByIdentifier(string $identifier): ?Customer
    {
        if (str_contains($identifier, '@')) {
            return Customer::where('email', mb_strtolower(trim($identifier)))->first();
        }

        return Customer::where('mobile', Customer::normalizeMobile($identifier))->first();
    }

    public function identifierIsEmail(string $identifier): bool
    {
        return str_contains($identifier, '@');
    }

    /**
     * @param  array{name:string, identifier:string, password:string}  $data
     */
    public function register(array $data): Customer
    {
        $isEmail = $this->identifierIsEmail($data['identifier']);

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $isEmail ? mb_strtolower(trim($data['identifier'])) : null,
            'mobile' => $isEmail ? null : Customer::normalizeMobile($data['identifier']),
            'password' => $data['password'],
            'status' => 'active',
        ]);

        CustomerRegistered::dispatch($customer);

        return $customer;
    }

    public function identifierTaken(string $identifier): bool
    {
        return $this->findByIdentifier($identifier) !== null;
    }
}
