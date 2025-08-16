<?php

namespace App\Livewire;

use App\Models\Affiliate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AffiliatePortal extends Component
{
    public $user;
    public $affiliate;
    public $showSignupForm = false;

    public function mount()
    {
        // Check if user is logged in
        if (!Auth::check()) {
            session(['url.intended' => request()->url()]);
            return $this->redirect(route('login'));
        }

        $this->user = Auth::user();
        $this->affiliate = $this->user->affiliate;

        // If affiliate is approved, redirect to main dashboard
        if ($this->affiliate && $this->affiliate->status === 'active') {
            return $this->redirect(route('affiliate.dashboard.main'));
        }
    }

    public function signupAsAffiliate()
    {
        if (!$this->user) {
            return redirect()->route('login');
        }

        // Create affiliate record with pending status
        Affiliate::create([
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'status' => 'pending',
            'rate' => 5.00, // Default 5% commission
            'rate_type' => 'percentage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Refresh the affiliate relationship
        $this->user->refresh();
        $this->affiliate = $this->user->affiliate;

        session()->flash('message', 'Your affiliate application has been submitted and is pending approval.');
    }

    public function render()
    {
        // If user is not an affiliate, show signup option
        if (!$this->affiliate) {
            return view('livewire.affiliate-portal.not-affiliate')->layout('layouts.guest');
        }

        // If affiliate status is pending, show pending message
        if ($this->affiliate->status === 'pending') {
            return view('livewire.affiliate-portal.pending')->layout('layouts.guest');
        }

        // If affiliate status is rejected or any other status
        return view('livewire.affiliate-portal.rejected')->layout('layouts.guest');
    }
}