<?php

namespace Themicly\Shopcrafty\Modules\Customers\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Themicly\Shopcrafty\Modules\Customers\Services\CustomerService;

class CustomerAuthController
{
    public function showLogin()
    {
        return View::make('theme::auth.login');
    }

    public function login(Request $request, CustomerService $customers)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], attributes: [
            'identifier' => __('auth.phone_or_email'),
            'password' => __('storefront.password'),
        ]);

        $customer = $customers->findByIdentifier($data['identifier']);

        if (! $customer) {
            // Constant-time: always run a hash comparison so a missing identifier
            // isn't measurably faster than a wrong password (CUS-07).
            static $dummy;
            $dummy ??= Hash::make('timing-safe-placeholder');
            Hash::check($data['password'], $dummy);

            throw ValidationException::withMessages(['identifier' => __('These credentials do not match our records.')]);
        }

        if (! Hash::check($data['password'], (string) $customer->password)) {
            throw ValidationException::withMessages(['identifier' => __('These credentials do not match our records.')]);
        }

        if (! $customer->isActive()) {
            throw ValidationException::withMessages(['identifier' => __('This account has been disabled.')]);
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('storefront.account.index'));
    }

    public function showRegister()
    {
        return View::make('theme::auth.register');
    }

    public function register(Request $request, CustomerService $customers)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identifier' => ['required', 'string', 'max:190'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], attributes: [
            'name' => __('storefront.full_name'),
            'identifier' => __('auth.phone_or_email'),
            'password' => __('storefront.password'),
        ]);

        // Validate the identifier by type so malformed emails / phones don't land
        // in the customers table (CUS-05).
        $identifier = trim($data['identifier']);
        $validIdentifier = str_contains($identifier, '@')
            ? filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false
            : (bool) preg_match('/^[0-9+()\-\s]{6,}$/', $identifier);

        if (! $validIdentifier) {
            throw ValidationException::withMessages(['identifier' => __('Enter a valid email address or phone number.')]);
        }

        if ($customers->identifierTaken($data['identifier'])) {
            throw ValidationException::withMessages(['identifier' => __('An account with this identifier already exists.')]);
        }

        $customer = $customers->register($data);
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('storefront.account.index');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shopcrafty.storefront');
    }
}
