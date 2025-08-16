<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AccountPage extends Component
{
    public function mount()
    {
        if (!Auth::check()) {
            return $this->redirect('/', navigate: true);
        }
    }

    public function render(): View
    {
        $user = Auth::user();
        $customer = $user->customers()->first();
        $orders = $customer ? $customer->orders()->latest()->get() : collect();
        
        return view('livewire.account-page', [
            'user' => $user,
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }
}