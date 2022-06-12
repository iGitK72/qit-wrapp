<?php

namespace App\Http\Livewire\Qlink;

use App\Models\QlinkConfiguration;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ConfigQlinkForm extends Component
{
    /**
     * The qconfig instance.
     *
     * @var mixed
     */
    protected $request;
    public $read_only;
    public $hide_key=true;
    public $qconfig;
    public $customer_id;
    public $api_access_key;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    protected $rules = [
        'customer_id' => 'required',
        'api_access_key' => 'required',
        ];


    /**
     * Mount the component.
     *
     *
     * @return void
     */
    public function mount($read_only=false)
    {
        $this->read_only = $read_only;

        $this->qconfig = QlinkConfiguration::where('user_id', auth()->user()->id)
            ->where('team_id', auth()->user()->current_team_id)
            ->first();

        if (!is_null($this->qconfig)) {
            $this->customer_id = $this->qconfig->customer_id;
            $this->api_access_key = Crypt::decryptString($this->qconfig->api_access_key);
            $this->state = $this->qconfig->withoutRelations()->toArray();
        }
    }

    /**
    * Reveal/Hide the API Access Key
    *
    * @return mixed
    */
    public function hideKey()
    {
        return $this->hide_key = !$this->hide_key;
    }

    /**
     * Update the customer's configuration.
     *
     * @return void
     */
    public function update(Request $request)
    {
        $this->request = $request;

        if ($this->isValidCredentials()) {
            $accesskeyEnc = Crypt::encryptString($this->api_access_key);

            $this->qconfig = QlinkConfiguration::updateOrCreate(
                ['user_id' => auth()->user()->id, 'team_id' => auth()->user()->current_team_id],
                ['customer_id' => $this->customer_id, 'api_access_key' => $accesskeyEnc]
            );
            
            $this->resetErrorBag();
        
            $this->emit('saved');
            $this->emit('refresh-navigation-menu');
        } else {
            session()->flash('alert-status', 'Invalid customer and access key combination!');
        }
    }

    /**
     * Validate the current config values.
     *
     * @return mixed
     */
    public function isValidCredentials()
    {
        // If either field is blank then return validate errors
        $this->validate();

        // Check if this combination returns a valid response for authorization to API
        if ($this->customer_id && $this->api_access_key) {
            $dateNow = Carbon::now();
            $qryDate = urlencode($dateNow->toIso8601ZuluString());
            $apiUrl = "https://" . $this->customer_id . ".api2.queue-it.net/2_0/changes?from=" . $qryDate . "&to=" . $qryDate;

            try {
                $response = Http::withHeaders([
                    'accept' => 'text/plain',
                    'api-key' => $this->api_access_key
                ])->get($apiUrl);
            } catch (Exception $e) {
                // dd($e, $e->getMessage());
                return false;
            }

            return $response->successful();
        }
    }

    /**
     * Super Admin:  Download new integrationconfig.json
     *
     * @return mixed
     */
    public function getNewIntConfig()
    {
        if (auth()->user()->email == 'kevinlhall72@gmail.com') {

        // Get local and get from server.  If newer version then upload the new.
            $configText = Storage::disk('local')->get('integrationconfig.json');

            $getUrl = "https://kehatest.queue-it.net/status/integrationconfig/secure/kehatest";

            $response = Http::withHeaders([
                            'api-key' => $this->api_access_key,
                        ])->get($getUrl);

            $jsonDecoded = json_decode($response, false);
            $jsonDecoded2 = json_decode($configText, false);

            if ($jsonDecoded && $jsonDecoded2) {
                if ($jsonDecoded->Version != $jsonDecoded2->Version) {
                    Storage::disk('local')->put('integrationconfig.json', $response);
                }
            }

            $this->emit('downloaded');
            return true;
        }

        return false;
    }

    public function render()
    {
        if (!is_null($this->qconfig)) {
            $this->emit('qconfigLoaded', $this->qconfig);
        }

        return view('livewire.qlink.config-qlink-form');
    }
}
