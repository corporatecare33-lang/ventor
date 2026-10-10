<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Checkout extends Component
{
    public string $customer_name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $area = '';

    public string $city = 'Dhaka';

    public string $district = '';

    public string $postal_code = '';

    public ?int $shipping_zone_id = null;

    public string $payment_method = 'cod';

    public string $transaction_id = '';

    public string $sender_number = '';

    public string $customer_note = '';

    public bool $save_address = false;

    public ?int $address_id = null;

    public bool $accept_terms = true;

    public function mount(CartService $cart)
    {
        if ($cart->count() === 0) {
            return $this->redirectRoute('cart');
        }

        $this->shipping_zone_id = ShippingZone::active()->value('id');
        $this->payment_method = PaymentMethod::active()->value('code') ?? 'cod';

        if ($user = auth()->user()) {
            $this->customer_name = $user->name;
            $this->email = $user->email;
            $this->phone = (string) $user->phone;

            if ($default = $user->addresses()->orderByDesc('is_default')->first()) {
                $this->useAddress($default->id);
            }
        }
    }

    public function useAddress(int $id): void
    {
        $address = auth()->user()?->addresses()->find($id);
        if (! $address) {
            return;
        }

        $this->address_id = $address->id;
        $this->customer_name = $address->name;
        $this->phone = $address->phone;
        $this->address = $address->address;
        $this->area = (string) $address->area;
        $this->city = $address->city;
        $this->district = (string) $address->district;
        $this->postal_code = (string) $address->postal_code;
    }

    protected function rules(): array
    {
        $method = PaymentMethod::active()->where('code', $this->payment_method)->first();

        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_zone_id' => ['required', Rule::exists('shipping_zones', 'id')->where('is_active', true)],
            'payment_method' => ['required', Rule::exists('payment_methods', 'code')->where('is_active', true)],
            'transaction_id' => [$method?->requires_transaction_id ? 'required' : 'nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9]*$/'],
            'sender_number' => ['nullable', 'string', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'accept_terms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Bangladeshi mobile number (01XXXXXXXXX).',
            'transaction_id.required' => 'Please enter the transaction ID from your payment.',
            'transaction_id.regex' => 'Transaction ID should contain letters and numbers only.',
            'accept_terms.accepted' => 'Please accept the terms to continue.',
        ];
    }

    public function updatedPhone(): void
    {
        $this->phone = normalize_phone($this->phone);
    }

    public function placeOrder(CheckoutService $checkout, CartService $cart)
    {
        $this->phone = normalize_phone($this->phone);
        $data = $this->validate();

        $clean = collect($data)->except(['accept_terms'])
            ->map(fn ($v) => is_string($v) ? trim(strip_tags($v)) : $v)
            ->all();

        try {
            $order = $checkout->placeOrder($clean);
        } catch (ValidationException $e) {
            $this->addError('checkout', collect($e->errors())->flatten()->first());

            return null;
        }

        if ($this->save_address && ($user = auth()->user()) && ! $this->address_id) {
            $user->addresses()->create([
                'label' => 'Home', 'name' => $clean['customer_name'], 'phone' => $clean['phone'], 'address' => $clean['address'],
                'area' => $clean['area'] ?: null, 'city' => $clean['city'], 'district' => $clean['district'] ?: null,
                'postal_code' => $clean['postal_code'] ?: null, 'is_default' => $user->addresses()->doesntExist(),
            ]);
        }

        $this->dispatch('cart-updated');

        return redirect()->to(URL::temporarySignedRoute('orders.confirmation', now()->addDays(7), ['order' => $order->order_number]));
    }

    public function render(CartService $cart)
    {
        $zone = $this->shipping_zone_id ? ShippingZone::active()->find($this->shipping_zone_id) : null;

        return view('livewire.checkout', [
            'totals' => $cart->totals($zone),
            'zones' => ShippingZone::active()->get(),
            'methods' => PaymentMethod::active()->get(),
            'selectedMethod' => PaymentMethod::active()->where('code', $this->payment_method)->first(),
            'addresses' => auth()->user()?->addresses()->orderByDesc('is_default')->get() ?? collect(),
        ]);
    }
}
