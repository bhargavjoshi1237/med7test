<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Collection;

class Navigation extends Component
{
    /**
     * The search term for the search input.
     *
     * @var string
     */
    public $term = null;

    /**
     * {@inheritDoc}
     */
    protected $queryString = [
        'term',
    ];

    /**
     * Return the collections in a tree.
     */
    public function getCollectionsProperty()
    {
        return Collection::with(['defaultUrl'])->get()->toTree();
    }

    /**
     * Open the authentication modal.
     */
    public function openAuthModal($tab = 'login')
    {
        $this->dispatch('openAuthModal', $tab);
    }

    /**
     * Logout the current user.
     */
    public function logout()
    {
        Auth::logout();
        session()->flash('message', 'You have been logged out successfully.');
        return $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.components.navigation');
    }
}
