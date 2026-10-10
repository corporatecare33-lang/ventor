<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', ['seo' => Seo::make('Sign in')->noindex()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register', ['seo' => Seo::make('Create an account')->noindex()]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['phone' => normalize_phone($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], ['phone.regex' => 'Please enter a valid Bangladeshi mobile number (01XXXXXXXXX).']);

        $user = User::create([...$data, 'name' => strip_tags($data['name'])]);
        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'))->with('success', 'Welcome to LeatherX!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
