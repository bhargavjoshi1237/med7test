<?php

namespace App\Livewire\Affiliates;

use Livewire\Component;
use App\Models\Affiliate; // Make sure you have an Affiliate model
use Livewire\WithPagination;

class ManageAffiliates extends Component
{
    use WithPagination;

    public $filterStatus = 'pending';

    public function render()
    {
        $affiliates = Affiliate::query()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->paginate(20);

        return view('livewire.affiliates.manage-affiliates', [
            'affiliates' => $affiliates
        ])->layout('layouts.app');
    }
    
    public function setStatus(Affiliate $affiliate, $status)
    {
        if (!in_array($status, ['active', 'inactive', 'rejected'])) {
            return;
        }

        $affiliate->status = $status;
        $affiliate->save();

        // Optional: Send an email notification
        // Mail::to($affiliate->email)->send(new AffiliateStatusChanged($affiliate, $status));

        session()->flash('message', "Affiliate {$affiliate->name} has been set to {$status}.");
    }
}