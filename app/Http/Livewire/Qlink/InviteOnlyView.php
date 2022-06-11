<?php

namespace App\Http\Livewire\Qlink;

use Livewire\Component;

class InviteOnlyView extends Component
{
    public $show_auth_content;

    public function mount($show_auth=false)
    {
        $this->show_auth_content = $show_auth;
    }

    public function render()
    {
        return view('livewire.qlink.invite-only-view');
    }
}
