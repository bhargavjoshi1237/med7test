<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Affiliate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $signup_as_affiliate = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // If user wants to signup as affiliate, create affiliate record
        if ($this->signup_as_affiliate) {
            Affiliate::create([
                'user_id' => $user->id,
                'name' => $this->name,
                'email' => $this->email,
                'status' => 'pending',
                'rate' => 5.00, // Default 5% commission
                'rate_type' => 'percentage',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Auth::login($user);

        session()->regenerate();

        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}