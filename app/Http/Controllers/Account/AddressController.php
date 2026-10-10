<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.addresses.index', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
            'seo' => Seo::make('My addresses')->noindex(),
        ]);
    }

    public function create(): View
    {
        return view('account.addresses.form', ['address' => new Address, 'seo' => Seo::make('Add address')->noindex()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, $request->user()->addresses()->make());

        return redirect()->route('account.addresses.index')->with('success', 'Address saved.');
    }

    public function edit(Request $request, Address $address): View
    {
        $this->authorizeOwner($request, $address);

        return view('account.addresses.form', ['address' => $address, 'seo' => Seo::make('Edit address')->noindex()]);
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeOwner($request, $address);
        $this->save($request, $address);

        return redirect()->route('account.addresses.index')->with('success', 'Address updated.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeOwner($request, $address);
        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    private function save(Request $request, Address $address): void
    {
        $request->merge(['phone' => normalize_phone($request->input('phone'))]);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'address' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ], ['phone.regex' => 'Please enter a valid Bangladeshi mobile number.']);

        DB::transaction(function () use ($request, $address, $data) {
            $isDefault = $request->boolean('is_default') || $request->user()->addresses()->doesntExist();
            if ($isDefault) {
                $request->user()->addresses()->update(['is_default' => false]);
            }
            $address->fill([...collect($data)->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all(), 'is_default' => $isDefault])->save();
        });
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 404);
    }
}
