<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickView extends Component
{
    public ?int $productId = null;

    #[On('quick-view')]
    public function open(int $id): void
    {
        $this->productId = Product::visible()->whereKey($id)->value('id');
    }

    public function close(): void
    {
        $this->productId = null;
    }

    public function render()
    {
        return view('livewire.quick-view', [
            'product' => $this->productId ? Product::with(['media', 'category:id,name,slug'])->find($this->productId) : null,
        ]);
    }
}
