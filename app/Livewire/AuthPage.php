<?php

namespace App\Livewire;

use Livewire\Component;

class AuthPage extends Component
{
    public $showLogin = true;

    public function toggleForm()
    {
        $this->showLogin = !$this->showLogin;
    }

    public function render()
    {
        return view('livewire.auth-page')->layout('layouts.guest');
    }
}