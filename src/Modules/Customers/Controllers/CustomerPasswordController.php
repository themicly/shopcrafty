<?php

namespace Themicly\Shopcrafty\Modules\Customers\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Storefront password reset. Uses the dedicated "customers" broker so tokens
 * never collide with admin users; the reset link is delivered through the
 * store's own notification pipeline (see Customer::sendPasswordResetNotification).
 */
class CustomerPasswordController
{
    protected function broker(): PasswordBroker
    {
        return Password::broker('customers');
    }

    public function requestForm()
    {
        return View::make('theme::auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']], attributes: ['email' => __('storefront.email')]);

        $this->broker()->sendResetLink($request->only('email'));

        // Never reveal whether the address exists.
        return back()->with('status', __('If that email is registered, a reset link is on its way.'));
    }

    public function resetForm(Request $request, string $token)
    {
        return View::make('theme::auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], attributes: [
            'email' => __('storefront.email'),
            'password' => __('storefront.password'),
        ]);

        $status = $this->broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer, $password) {
                $customer->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return redirect()->route('storefront.login')->with('status', __('Your password has been reset. Please sign in.'));
    }
}
