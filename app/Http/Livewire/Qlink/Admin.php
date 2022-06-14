<?php

namespace App\Http\Livewire\Qlink;

use Livewire\Component;

class Admin extends Component
{
    public $qlinks_all;
    public $qlinks;
    public $search;
    
    public function mount($qlinks)
    {
        $this->qlinks_all = $qlinks;
    }

    public function render()
    {
        $this->qlinks = $this->qlinks_all;

        if ($this->search) {
            $filtered = $this->qlinks->filter(function ($value, $key) {
                return (
                    false !== stripos($value->event_id, $this->search) ||
                    false !== stripos($value->rlwr_event_id, $this->search) ||
                    false !== stripos($value->visitor_id, $this->search) ||
                    false !== stripos($value->token_identifier, $this->search)
                );
            });

            $this->qlinks = $filtered;
        }

        return view('livewire.qlink.admin');
    }
}
