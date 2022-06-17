<?php

namespace App\Http\Livewire\Qlink;

use Livewire\Component;

class InviteOnlyView extends Component
{
    public $show_auth;

    public function mount($show_auth=true)
    {
        if ($show_auth) {
            $this->show_auth = auth()->check();
        } else {
            $this->show_auth = false;
        }
    }

    public function render()
    {
        return view('livewire.qlink.invite-only-view', ['show_auth_content' => $this->show_auth]);
    }
}
