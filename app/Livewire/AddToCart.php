<?php

namespace App\Livewire;

use App\Enums\EngravingType;
use App\Models\EngravingRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Variant / colour picker, engraving options, quantity and Add-to-cart / Buy-now.
 * Used on the product page and inside the quick-view modal.
 */
class AddToCart extends Component
{
    #[Locked]
    public int $productId;

    #[Locked]
    public bool $compact = false;

    public ?int $colorId = null;

    public ?int $materialId = null;

    public ?int $sizeId = null;

    public int $quantity = 1;

    public bool $engrave = false;

    public string $engravingType = 'initials';

    public string $engravingText = '';

    public string $engravingFont = 'Classic Serif';

    public string $engravingPosition = 'Front bottom corner';

    public string $engravingLogo = '';

    public function mount(Product $product, bool $compact = false): void
    {
        $this->productId = $product->id;
        $this->compact = $compact;

        $first = $this->variants->firstWhere('stock_quantity', '>', 0) ?? $this->variants->first();
        if ($first) {
            $this->colorId = $first->color_id;
            $this->materialId = $first->material_id;
            $this->sizeId = $first->size_id;
        }
    }

    #[Computed]
    public function product(): Product
    {
        return Product::with('media')->findOrFail($this->productId);
    }

    /** @return Collection<int, ProductVariant> */
    #[Computed]
    public function variants(): Collection
    {
        return ProductVariant::where('product_id', $this->productId)->where('is_active', true)
            ->with(['color', 'material', 'size'])->orderBy('sort_order')->get();
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        if ($this->variants->isEmpty()) {
            return null;
        }

        return $this->variants->first(fn (ProductVariant $v) => $v->color_id === $this->colorId
            && $v->material_id === $this->materialId
            && $v->size_id === $this->sizeId)
            ?? $this->variants->first(fn (ProductVariant $v) => $v->color_id === $this->colorId);
    }

    public function selectColor(int $id): void
    {
        $this->colorId = $id;
        $match = $this->variants->first(fn ($v) => $v->color_id === $id && $v->stock_quantity > 0) ?? $this->variants->firstWhere('color_id', $id);
        if ($match) {
            $this->materialId = $match->material_id;
            $this->sizeId = $match->size_id;
        }
        $this->quantity = 1;
        $this->dispatch('variant-selected', productId: $this->productId, color: $this->variants->firstWhere('color_id', $id)?->color?->name);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['materialId', 'sizeId'], true)) {
            $this->quantity = 1;
        }
    }

    public function increment(): void
    {
        $this->quantity = min($this->quantity + 1, max(1, $this->stock()));
    }

    public function decrement(): void
    {
        $this->quantity = max(1, $this->quantity - 1);
    }

    public function stock(): int
    {
        return $this->variants->isNotEmpty()
            ? (int) ($this->selectedVariant?->stock_quantity ?? 0)
            : (int) $this->product->stock_quantity;
    }

    public function unitPrice(): float
    {
        return $this->selectedVariant ? $this->selectedVariant->setRelation('product', $this->product)->currentPrice() : $this->product->currentPrice();
    }

    public function regularPrice(): float
    {
        return $this->selectedVariant ? $this->selectedVariant->setRelation('product', $this->product)->regularPrice() : (float) $this->product->regular_price;
    }

    public function add(CartService $cart): void
    {
        $this->addToCart($cart);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: 'Added to cart', action: route('cart'), actionLabel: 'View cart');
        $this->reset('engrave', 'engravingText', 'engravingLogo');
    }

    public function buyNow(CartService $cart)
    {
        $this->addToCart($cart);

        return $this->redirectRoute('checkout', navigate: false);
    }

    private function addToCart(CartService $cart): void
    {
        $this->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'engravingType' => [Rule::enum(EngravingType::class)],
            'engravingText' => [$this->engrave && $this->engravingType !== 'logo' ? 'required' : 'nullable', 'string', 'max:30', 'regex:/^[\pL\pN\s\.\,\'\-&!]*$/u'],
            'engravingFont' => [Rule::in(EngravingRequest::FONTS)],
            'engravingPosition' => [Rule::in(EngravingRequest::POSITIONS)],
            'engravingLogo' => [$this->engrave && $this->engravingType === 'logo' ? 'required' : 'nullable', 'string', 'max:500'],
        ], [
            'engravingText.required' => 'Please enter the text to engrave.',
            'engravingText.regex' => 'Engraving supports letters, numbers and basic punctuation only.',
            'engravingLogo.required' => 'Please describe the logo (we will contact you for the file).',
        ]);

        $engraving = $this->engrave && $this->product->engraving_available ? array_filter([
            'type' => $this->engravingType,
            'text' => $this->engravingType === 'logo' ? null : trim(strip_tags($this->engravingText)),
            'font' => $this->engravingFont,
            'position' => $this->engravingPosition,
            'logo_instruction' => $this->engravingType === 'logo' ? trim(strip_tags($this->engravingLogo)) : null,
        ]) : null;

        try {
            $cart->add($this->product, $this->selectedVariant, $this->quantity, $engraving);
        } catch (ValidationException $e) {
            $this->addError('cart', collect($e->errors())->flatten()->first());
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.add-to-cart', [
            'colors' => $this->variants->pluck('color')->filter()->unique('id')->values(),
            'materials' => $this->variants->where('color_id', $this->colorId)->pluck('material')->filter()->unique('id')->values(),
            'sizes' => $this->variants->where('color_id', $this->colorId)->pluck('size')->filter()->unique('id')->values(),
            'fonts' => EngravingRequest::FONTS,
            'positions' => EngravingRequest::POSITIONS,
        ]);
    }
}
