<?php

namespace App\Http\Livewire\Qlink;

use App\Models\Qlink as ModelsQlink;
use App\Models\QlinkConfiguration;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class EditQlinkForm extends Component
{
    public ModelsQlink $qlink;

    public $show_info_by_ti=false;
    public $queue_item_details;

    protected $rules = [
        'qlink.event_id' => 'required|string',
        'qlink.visitor_identity_key' => 'required|string',
        'qlink.token_identifier' => 'required|string',
        'qlink.access_link' => 'required|string',
        'qlink.rlwr_event_id' => 'string',
        'qlink.visitor_id' => 'nullable|string',
        "qlink.lock" => '',
        "qlink.key" => '',
        "qlink.usage_count" => '',
        "qlink.queue_id" => '',
        "qlink.redirect_time_utc" => '',
        "qlink.queue_number" => '',
        "qlink.rlwr_queue_id" => '',
        "qlink.rlwr_queue_id_used" => ''
    ];

    public function mount($qlink)
    {
        $this->qlink = $qlink;

        $this->qconfig = QlinkConfiguration::where('user_id', auth()->user()->id)
        ->where('team_id', auth()->user()->current_team_id)
        ->first();

        if (!is_null($this->qconfig)) {
            $this->customer_id = $this->qconfig->customer_id;
            $this->api_access_key = Crypt::decryptString($this->qconfig->api_access_key);
        }

        $this->getTokenInfo();
    }
    
    public function render()
    {
        return view('livewire.qlink.edit-qlink-form');
    }

    public function update()
    {
        $validatedData = $this->validate();
        $this->qlink->save();

        $this->resetErrorBag();
            
        $this->emit('saved');
    
        $this->emit('refresh-navigation-menu');
    }

    public function getTokenInfo()
    {
        $apiUrl = "https://" . $this->customer_id . ".queue-it.net/api/queue/queueitem/" . $this->customer_id . "/token/" . $this->qlink->token_identifier;
        try {
            $response = Http::withHeaders([
                    'accept' => 'text/plain',
                    'api-key' => $this->api_access_key
                ])->get($apiUrl);
        } catch (Exception $e) {
            dd($e, $e->getMessage());
            $this->alert_status = 'Invalid WR id for customer info.';
            return false;
        }
        
        $tokenDetails = Arr::dot($response->json());
        if ($tokenDetails['status'] == '404') {
            // Override type to message that link has not been used so no queue id exists yet and therefore no info
            // QueueItem info is saved for 7 days only
            $tokenDetails['type'] = 'Link has not been used.  No Queue ID info available.';
        }
        $this->queue_item_details = $tokenDetails;
    }
}
