<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Enums\WarrantyStatus;
use App\Enums\WarrantyType;
use App\Models\Order;
use App\Models\WarrantyRegistration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Two-step warranty form: (1) find the order by number + phone, (2) choose the product
 * and register it or file a claim. Matching a real order prevents spam submissions.
 */
class WarrantyForm extends Component
{
    public string $type = 'registration';

    public string $order_number = '';

    public string $phone = '';

    public string $customer_name = '';

    public string $email = '';

    public ?int $order_item_id = null;

    public string $issue_details = '';

    #[Locked]
    public ?int $orderId = null;

    public ?string $reference = null;

    public function mount(): void
    {
        if ($user = auth()->user()) {
            $this->customer_name = $user->name;
            $this->email = $user->email;
            $this->phone = (string) $user->phone;
        }

        $this->type = request()->query('type') === 'claim' ? 'claim' : 'registration';
    }

    public function findOrder(): void
    {
        $this->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $key = 'warranty-lookup:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('order_number', 'Too many attempts. Please try again in a minute.');

            return;
        }
        RateLimiter::hit($key, 60);

        $order = Order::where('order_number', strtoupper(trim($this->order_number)))
            ->where('phone', normalize_phone($this->phone))
            ->first();

        if (! $order) {
            $this->addError('order_number', 'We could not find an order with this number and phone.');

            return;
        }

        if ($order->status !== OrderStatus::Delivered) {
            $this->addError('order_number', 'Warranty can be registered once your order has been delivered.');

            return;
        }

        $this->orderId = $order->id;
        $this->customer_name = $this->customer_name ?: $order->customer_name;
        $this->email = $this->email ?: (string) $order->email;
    }

    public function resetOrder(): void
    {
        $this->reset('orderId', 'order_item_id');
    }

    public function submit(): void
    {
        abort_unless($this->orderId, 422);

        $order = Order::with('items')->findOrFail($this->orderId);

        $this->validate([
            'type' => ['required', Rule::enum(WarrantyType::class)],
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'order_item_id' => ['required', Rule::in($order->items->pluck('id')->all())],
            'issue_details' => [$this->type === 'claim' ? 'required' : 'nullable', 'string', 'max:2000'],
        ], ['order_item_id.required' => 'Please choose a product.', 'issue_details.required' => 'Please describe the issue.']);

        $item = $order->items->firstWhere('id', $this->order_item_id);
        $purchase = Carbon::parse($order->delivered_at ?? $order->created_at)->startOfDay();

        if ($this->type === 'claim' && $item->warranty_months && $purchase->copy()->addMonths($item->warranty_months)->isPast()) {
            $this->addError('order_item_id', 'The warranty for this product has expired.');

            return;
        }

        $record = WarrantyRegistration::create([
            'type' => $this->type,
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'customer_name' => strip_tags($this->customer_name),
            'phone' => $order->phone,
            'email' => $this->email ?: $order->email,
            'order_number' => $order->order_number,
            'product_name' => $item->product_name,
            'purchase_date' => $purchase,
            'expires_at' => $item->warranty_months ? $purchase->copy()->addMonths($item->warranty_months) : null,
            'issue_details' => $this->issue_details ? strip_tags($this->issue_details) : null,
            'status' => WarrantyStatus::Pending,
        ]);

        $this->reference = $record->reference;
    }

    public function render()
    {
        return view('livewire.warranty-form', [
            'items' => $this->orderId ? Order::find($this->orderId)?->items()->get(['id', 'product_name', 'variant_name', 'warranty_months']) : collect(),
        ]);
    }
}
