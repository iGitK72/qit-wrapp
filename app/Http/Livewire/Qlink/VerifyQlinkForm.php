<?php

namespace App\Http\Livewire\Qlink;

use App\Models\Qlink;
use App\Models\QlinkConfiguration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class VerifyQlinkForm extends Component
{
    protected $rules = [
        'visitor_id' => 'required',
    ];

    protected $messages = [];

    protected $validationAttributes =[
        'visitor_id' => 'user id/e-mail',
    ];

    public $visitor_id;
    public $confirmed=false;
    public $showIowrWithAuth=false;

    public function mount()
    {
    }
        
    public function render()
    {
        return view('livewire.qlink.verify-qlink-form');
    }

    public function confirm()
    {
        if (!auth()->check()) {
            $this->showIowrWithAuth = true;
            $this->alert_status = 'You must login/register to start a spree.';
            session()->flash('alert-status', $this->alert_status);
            $this->confirmed = true;
            $this->confirmed = false;
            return;
        }

        $this->validate();

        if ($this->confirmed === true) {
            $this->confirmed = !$this->confirmed;
            return;
        }

        $verified = false;
        $queueIds = [];

        foreach ($_COOKIE as $name => $value) {
            if ((stripos($value, 'QueueId=') !== false)) {
                parse_str($value, $output);
                $queueIds[] = $output['QueueId'];
            }
        }

        $qconfig = QlinkConfiguration::where('user_id', auth()->user()->id)
        ->where('team_id', auth()->user()->current_team_id)
        ->first();

        if (!is_null($qconfig)) {
            $customer_id = $qconfig->customer_id;
            $api_access_key = Crypt::decryptString($qconfig->api_access_key);
        } else {
            $this->showIowrWithAuth = true;
            $this->alert_status = 'You are missing QConfiguration credentials. Go to Admin or switch Teams.';
            session()->flash('alert-status', $this->alert_status);
            return;
        }

        foreach ($queueIds as $queueId) {
            $apiUrl = "https://kehatest.queue-it.net/api/queue/queueitem/" . $customer_id . "/queueid/" . $queueId;

            $response = Http::withHeaders([
                'accept' => 'text/plain',
                'api-key' => config('app.qit_api')
            ])->get($apiUrl);

            $queueInfo = $response->json();

            if ($response->successful()) {
                $qlinkFound = Qlink::where('token_identifier', $queueInfo['tokenIdentifier'])->first();

                if (is_null($qlinkFound->visitor_id)) {
                    $qlinkFound->visitor_id = $this->visitor_id;
                    $qlinkFound->save();
                }

                $verified = ($qlinkFound->visitor_id == $this->visitor_id);
                if ($verified) {
                    $this->confirmed = $verified;
                    break;
                }
            }
        }

        if (!$verified) {
            $this->addError('visitor_id', 'Sorry. The user id/email does not match this access link. Try again.');
            return;
        }
    }
}
