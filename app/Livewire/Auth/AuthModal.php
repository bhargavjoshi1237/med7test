<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class AuthModal extends Component
{
    public $showModal = false;
    public $activeTab = 'login'; // 'login' or 'register'

    protected $listeners = [
        'openAuthModal' => 'openModal',
        'closeAuthModal' => 'closeModal',
    ];

    public function openModal($tab = 'login')
    {
        $this->activeTab = $tab;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['activeTab']);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.auth.auth-modal');
    }
}