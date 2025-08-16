<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Index extends Component
{
    public string $currentTab = 'referrals';

    protected $queryString = ['currentTab'];

    public function selectTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function render()
    {
        return view('livewire.reports.index')->layout('layouts.app');
    }
}