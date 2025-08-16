<?php

namespace App\Livewire\Components;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;

class CartView extends Component
{
    /**
     * The editable cart lines.
     */
    public array $lines = [];

    protected $listeners = [
        'cartUpdated' => 'refreshCart',
        'add-to-cart' => 'refreshCart',
    ];



    public function mount(): void
    {
        $this->mapLines();
    }

    /**
     * Get the current cart instance.
     */
    public function getCartProperty()
    {
        return CartSession::current();
    }

    /**
     * Return the cart lines from the cart.
     */
    public function getCartLinesProperty(): Collection
    {
        return $this->cart->lines ?? collect();
    }

    /**
     * Increase quantity of a cart line.
     */
    public function increaseQuantity(string $lineId): void
    {
        $cart = CartSession::current();
        if (!$cart) return;

        $cartLine = $cart->lines->where('id', $lineId)->first();
        if (!$cartLine) return;

        $newQuantity = $cartLine->quantity + 1;
        
        // Check stock availability
        $availableStock = $cartLine->purchasable->stock ?? 0;
        if ($newQuantity > $availableStock) {
            session()->flash('error', 'Cannot increase quantity. Not enough stock available.');
            return;
        }

        // Remove the current line and add with new quantity
        CartSession::remove($lineId);
        CartSession::manager()->add($cartLine->purchasable, $newQuantity, $cartLine->meta->toArray());
        
        $this->mapLines();
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Quantity updated successfully!');
    }

    /**
     * Decrease quantity of a cart line.
     */
    public function decreaseQuantity(string $lineId): void
    {
        $cart = CartSession::current();
        if (!$cart) return;

        $cartLine = $cart->lines->where('id', $lineId)->first();
        if (!$cartLine || $cartLine->quantity <= 1) return;

        $newQuantity = $cartLine->quantity - 1;
        
        // Remove the current line and add with new quantity
        CartSession::remove($lineId);
        CartSession::manager()->add($cartLine->purchasable, $newQuantity, $cartLine->meta->toArray());
        
        $this->mapLines();
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Quantity updated successfully!');
    }

    /**
     * Update quantity from direct input.
     */
    public function updateQuantity(string $lineId, int $newQuantity): void
    {
        if ($newQuantity < 1) {
            session()->flash('error', 'Quantity must be at least 1.');
            $this->mapLines(); // Reset to original values
            return;
        }

        $cart = CartSession::current();
        if (!$cart) return;

        $cartLine = $cart->lines->where('id', $lineId)->first();
        if (!$cartLine) return;

        // Check stock availability
        $availableStock = $cartLine->purchasable->stock ?? 0;
        if ($newQuantity > $availableStock) {
            session()->flash('error', "Cannot set quantity to {$newQuantity}. Only {$availableStock} items available in stock.");
            $this->mapLines(); // Reset to original values
            return;
        }

        // Remove the current line and add with new quantity
        CartSession::remove($lineId);
        CartSession::manager()->add($cartLine->purchasable, $newQuantity, $cartLine->meta->toArray());
        
        $this->mapLines();
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Quantity updated successfully!');
    }



    /**
     * Remove a line from the cart.
     */
    public function removeLine(string $id): void
    {
        CartSession::remove($id);
        $this->mapLines();
        $this->dispatch('cartUpdated');
        
        session()->flash('success', 'Item removed from cart!');
    }

    /**
     * Clear the entire cart.
     */
    public function clearCart(): void
    {
        CartSession::clear();
        $this->mapLines();
        $this->dispatch('cartUpdated');
        
        session()->flash('success', 'Cart cleared successfully!');
    }

    /**
     * Map the cart lines.
     */
    public function mapLines(): void
    {
        if (!$this->cart) {
            $this->lines = [];
            return;
        }

        $this->lines = $this->cartLines->map(function ($line) {
            return [
                'id' => $line->id,
                'identifier' => $line->purchasable->getIdentifier(),
                'quantity' => $line->quantity,
                'description' => $line->purchasable->getDescription(),
                'thumbnail' => $line->purchasable->getThumbnail()?->getUrl(),
                'option' => $line->purchasable->getOption(),
                'options' => $line->purchasable->getOptions()->implode(' / '),
                'sub_total' => $line->subTotal->formatted(),
                'unit_price' => $line->unitPrice->formatted(),
                'total_price' => $line->total->formatted(),
            ];
        })->toArray();
    }

    /**
     * Refresh cart data.
     */
    public function refreshCart(): void
    {
        $this->mapLines();
    }

    public function render(): View
    {
        return view('livewire.components.cart-view')
            ->layout('layouts.storefront');
    }
}