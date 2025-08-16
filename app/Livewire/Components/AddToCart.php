<?php

namespace App\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Base\Purchasable;
use Lunar\Facades\CartSession;

class AddToCart extends Component
{
    /**
     * The purchasable model we want to add to the cart.
     */
    public ?Purchasable $purchasable = null;

    /**
     * The quantity to add to cart.
     */
    public int $quantity = 1;
    public ?string $refferer = null;


    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric|min:1|max:10000',
        ];
    }

    public function addToCart(): void
    {
        $this->validate();

        // Get current quantity of this variant in the cart
        $cart = CartSession::current();
        $currentQty = 0;
        if ($cart) {
            $currentQty = $cart->lines
                ->where('purchasable_id', $this->purchasable->id)
                ->sum('quantity');
        }

        $totalRequested = $currentQty + $this->quantity;
        $availableStock = $this->purchasable->stock ?? 0;

        if ($totalRequested > $availableStock) {
            $this->addError('quantity', "The quantity exceeds the available stock ($availableStock).");
            return;
        }

        CartSession::manager()->add($this->purchasable, $this->quantity, $meta=[
            'refferer' => $this->refferer,
        ]);
        $this->dispatch('add-to-cart');
    }

    public function render(): View
    {
        return view('livewire.components.add-to-cart');
    }
}
