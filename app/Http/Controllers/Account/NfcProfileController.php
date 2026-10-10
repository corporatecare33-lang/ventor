<?php

namespace App\Http\Controllers\Account;

use App\Enums\NfcProfileStatus;
use App\Http\Controllers\Controller;
use App\Models\NfcProfile;
use App\Models\OrderItem;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customers manage the digital profile their LeatherX Tap chip points to.
 * Creating a profile requires having ordered an NFC-enabled product.
 */
class NfcProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.nfc.index', [
            'profiles' => $request->user()->nfcProfiles()->latest()->get(),
            'canCreate' => $this->nfcOrderItems($request)->isNotEmpty(),
            'seo' => Seo::make('My NFC profiles')->noindex(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->nfcOrderItems($request)->isEmpty()) {
            return redirect()->route('account.nfc.index')->with('error', 'Order a LeatherX Tap product to create an NFC profile.');
        }

        return view('account.nfc.form', [
            'profile' => new NfcProfile(['name' => $request->user()->name, 'email' => $request->user()->email, 'phone' => $request->user()->phone]),
            'items' => $this->nfcOrderItems($request),
            'seo' => Seo::make('Create NFC profile')->noindex(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $items = $this->nfcOrderItems($request);
        abort_if($items->isEmpty(), 403);

        $data = $this->validated($request);
        $item = $items->firstWhere('id', (int) $request->input('order_item_id')) ?? $items->first();

        $request->user()->nfcProfiles()->create([
            ...$data,
            'product_id' => $item->product_id,
            'order_id' => $item->order_id,
            'status' => NfcProfileStatus::Active,
        ]);

        return redirect()->route('account.nfc.index')->with('success', 'Your NFC profile is live. Program your chip with the link below.');
    }

    public function edit(Request $request, NfcProfile $profile): View
    {
        $this->authorizeOwner($request, $profile);

        return view('account.nfc.form', ['profile' => $profile, 'items' => collect(), 'seo' => Seo::make('Edit NFC profile')->noindex()]);
    }

    public function update(Request $request, NfcProfile $profile): RedirectResponse
    {
        $this->authorizeOwner($request, $profile);
        abort_if($profile->status === NfcProfileStatus::Suspended, 403, 'This profile has been suspended. Please contact support.');

        $profile->update($this->validated($request));

        return redirect()->route('account.nfc.index')->with('success', 'Profile updated — your NFC chip now shows the new details.');
    }

    public function destroy(Request $request, NfcProfile $profile): RedirectResponse
    {
        $this->authorizeOwner($request, $profile);
        $profile->delete();

        return redirect()->route('account.nfc.index')->with('success', 'Profile deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'facebook' => ['nullable', 'url:http,https', 'max:255'],
            'instagram' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin' => ['nullable', 'url:http,https', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ]);

        return collect($data)->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all();
    }

    /** NFC-enabled items from this customer's non-cancelled orders. */
    private function nfcOrderItems(Request $request)
    {
        return OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->whereNotIn('status', ['cancelled', 'returned']))
            ->whereHas('product', fn ($q) => $q->withTrashed()->where('nfc_enabled', true))
            ->with('order:id,order_number')
            ->get();
    }

    private function authorizeOwner(Request $request, NfcProfile $profile): void
    {
        abort_unless($profile->user_id === $request->user()->id, 404);
    }
}
