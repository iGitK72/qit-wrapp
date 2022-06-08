<?php

namespace App\Http\Livewire\Qlink;

use App\Models\Qlink;
use App\Models\QlinkConfiguration;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateQlinkForm extends Component
{
    use WithFileUploads;

    public $csvfile;

    /**
    * The qconfig instance.
    *
    * @var mixed
    */
    public $read_only;
    public $upload_success_count=0;
    public $upload_error_count=0;
    public $alert_status;
    public $qconfig;
    public $customer_id;
    public $api_access_key;
    public $visitor_id;
    public $iowr_event_id;
    public $rlwr_event_id;
    public $rlwr_event_id_allow_overwrite=false;
    public $visitor_identity_key;
    public $token_identifier;
    public $iowr_access_link;
    public $iowr_csv_value;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    protected $listeners = ['qconfigLoaded'];

    protected $rules = [
        'rlwr_event_id' => 'required',
        'iowr_access_link' => 'required_without:iowr_csv_value',
        'iowr_csv_value' => 'required_without:iowr_access_link',
    ];

    protected $messages = [
        'rlwr_event_id.required' => 'The Request Link Waiting Room ID is required.',
        'iowr_access_link.required_without' => 'The IOWR Access Link is required.',
        'iowr_csv_value.required_without' => 'The IOWR CSV value or Access Link is required.'
    ];

    protected $validationAttributes =[
        'visitorId' => 'Visitor User ID',
        'rlwr_event_id' => 'Request Link WR ID',
    ];

    public function qconfigLoaded($qconfig)
    {
        // Note:  This does not load on the initial mount and render of the ConfigQlinkForm
        $this->qconfig = $qconfig;
    }

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
     * Add reference info to IOWR link.
     *
     * @return void
     */
    public function add()
    {
        dd($this->rlwr_event_id_allow_overwrite);
        if ($this->iowr_csv_value) {
            $this->getCsvParts();
            $this->getAccessLinkParts();
        } else {
            if ($this->iowr_access_link) {
                $this->getAccessLinkParts();
            }
        }

        $validatedData = $this->validate();

        if ($this->isValidAccessLink()) {
            // Add/update the record to the Qlink table
            $newQlink = Qlink::updateOrCreate(
                ['token_identifier' => $this->token_identifier],
                ['event_id' => $this->iowr_event_id,
                'rlwr_event_id'  => $this->rlwr_event_id,
                'user_id' => auth()->user()->id,
                'visitor_id' => $this->visitor_id,
                'access_link' => $this->iowr_access_link,
                'visitor_identity_key' => $this->visitor_identity_key]
            );
            
            $this->resetErrorBag();
            
            $this->emit('saved');
    
            $this->emit('refresh-navigation-menu');
        } else {
            if ($this->alert_status == null) {
                $this->alert_status = 'Customer and event id mismatch.';
            }
            session()->flash('alert-status', $this->alert_status);
        }

        return;
    }

    
    /**
     * Update the customer's configuration.
     *
     * @return void
     */
    public function save()
    {
        $this->resetErrorBag();     // Clear any errors manually set on forms

        if ($this->rlwr_event_id === null || $this->rlwr_event_id == '') {
            $this->alert_status = 'The Request Link Waiting Room ID is required.';
            session()->flash('upload-status', $this->alert_status);
        }

        $validatedData = $this->validateOnly('rlwr_event_id');

        if ($this->csvfile === null) {
            $this->alert_status = 'You must add a file to upload.';
            session()->flash('upload-status', $this->alert_status);
            return;
        }

        $filepath = $this->csvfile->path();
        
        $file = fopen($filepath, "r");
        $importData_arr = array(); // Read through the file and store the contents as an array
        $i = 0;
        //Read the contents of the uploaded file
        while (($filedata = fgetcsv($file, 1000, ",")) !== false) {
            $num = count($filedata);
            // Skip first row (Remove below comment if you want to skip the first row)
            if ($i == 0) {
                $i++;
                continue;
            }
            for ($c = 0; $c < $num; $c++) {
                $importData_arr[$i][] = $filedata[$c];
            }
            $i++;
        }
        fclose($file); //Close after reading

        // Reset error counts
        $this->upload_success_count=0;
        $this->upload_error_count=0;
        $previousCustomerId = null;
        $previousIowrId = null;

        foreach ($importData_arr as $key => $data) {
            $this->visitor_identity_key = $data[0];
            $this->iowr_access_link = $data[1];
            $this->visitor_id = array_key_exists(2, $data) ? trim($data[2]) : null;
            $this->iowr_access_link = $data[1];

            $this->getAccessLinkParts();

            $newCustomer = $previousCustomerId == null || $previousCustomerId != $this->customer_id;
            $newIowr = $previousIowrId == null || $previousIowrId != $this->customer_id;

            if ($previousCustomerId == null || $previousCustomerId != $this->customer_id) {
                $isValidAccessLink = $this->isValidAccessLink();
            } else {
                $isValidAccessLink = $this->isValidAccessLink(null, true);
            }

            $previousCustomerId = $this->customer_id;
            $previousIowrId = $this->iowr_event_id;

            if ($isValidAccessLink) {
                $newQlink = Qlink::updateOrCreate(
                    ['token_identifier' => $this->token_identifier],
                    ['event_id' => $this->iowr_event_id,
                    'rlwr_event_id'  => $this->rlwr_event_id,
                    'user_id' => auth()->user()->id,
                    'visitor_id' => $this->visitor_id,
                    'access_link' => $this->iowr_access_link,
                    'visitor_identity_key' => $this->visitor_identity_key]
                );

                $this->upload_success_count++;
            } else {
                $this->upload_error_count++;
            }
        }

        $this->resetForm();

        $this->alert_status = 'Uploaded: ' . $this->upload_success_count . '. Error count: ' . $this->upload_error_count;
        session()->flash('upload-status', $this->alert_status);

        $this->resetErrorBag();
        $this->emit('uploaded');
        $this->emit('refresh-navigation-menu');

        return;
    }

    /**
     * Check the access link is valid.
     *
     * @return mixed
     */
    public function isValidAccessLink($eventId=null, $suppressApiCheck=null)
    {
        $this->alert_status = null;
        if ($eventId === null) {

            // Link can be updated if visitor id is not stored yet
            $linkExists = Qlink::where('access_link', $this->iowr_access_link)->first();
            if ($linkExists) {
                if ($linkExists->rlwr_event_id) {
                    $this->rlwr_event_id = ($this->rlwr_event_id_allow_overwrite) ? $this->rlwr_event_id : $linkExists->rlwr_event_id;
                }

                if ($linkExists->visitor_id === null) {
                    return true;
                } else {
                    $this->visitor_id = $linkExists->visitor_id;
                    $this->addError('iowr_access_link', 'This Invite Only Access Link is in use.');
                    $this->alert_status = 'Access link has already been assigned. It is not possible to change.';
                    return false;
                }
            }

            // Is the domain customer id matching
            $validDomainCustomerId = "https://".$this->customer_id.".queue-it.net?";
            if (stripos($this->iowr_access_link, $validDomainCustomerId) !== 0) {
                $this->alert_status = 'Access link domain info does not match GO Customer info.';
                $this->addError('iowr_access_link', 'Invalid link.');
                return false;
            }

            if (!$suppressApiCheck) {
                // Is the iowr_event_id valid for the qconfigLoaded
                // Check combination returns a valid response for authorization to API and waiting room
                $validate_event_id = $this->iowr_event_id;
                if ($this->customer_id && $this->api_access_key &&  $validate_event_id) {
                    $apiUrl = "https://" . $this->customer_id . ".api2.queue-it.net/2_0/event/" . $validate_event_id;
                    try {
                        $response = Http::withHeaders([
                    'accept' => 'text/plain',
                    'api-key' => $this->api_access_key
                ])->get($apiUrl);
                    } catch (Exception $e) {
                        //dd($e, $e->getMessage());
                        $this->alert_status = 'Invalid IOWR id for customer info.';
                        return false;
                    }
                    return $response->successful();
                }
            } else {
                return true;
            }
        }

        if (!$suppressApiCheck) {
            // Is the rlwr_event_id or passed event id valid for the qconfigLoaded
            // Check combination returns a valid response for authorization to API and waiting room
            $validate_event_id = ($eventId) ?: $this->rlwr_event_id;
            if ($this->customer_id && $this->api_access_key &&  $validate_event_id) {
                $apiUrl = "https://" . $this->customer_id . ".api2.queue-it.net/2_0/event/" . $validate_event_id;
                try {
                    $response = Http::withHeaders([
                    'accept' => 'text/plain',
                    'api-key' => $this->api_access_key
                ])->get($apiUrl);
                } catch (Exception $e) {
                    //dd($e, $e->getMessage());
                    $this->alert_status = 'Invalid WR id for customer info.';
                    return false;
                }
                return $response->successful();
            } else {
                return true;
            }
        }
    }

    /**
     * Parse the IOWR access link.
     *
     * @return mixed
     */
    public function getAccessLinkParts()
    {
        if (!filter_var($this->iowr_access_link, FILTER_VALIDATE_URL) === false) {
            try {
                $parts = parse_url($this->iowr_access_link);
                if (array_key_exists('query', $parts)) {
                    parse_str($parts['query'], $query);
                    if (array_key_exists('c', $query)) {
                        if ($this->customer_id != $query['c']) {
                            session()->flash('alert-status', 'Access link customer id does not match the GO Customer Info.');
                            return;
                        }
                    } else {
                        session()->flash('alert-status', 'Access link customer id does not match the GO Customer Info.');
                        return;
                    }
                    if (array_key_exists('e', $query)) {
                        $this->iowr_event_id = $query['e'];
                    }
                    if (array_key_exists('ti', $query)) {
                        $this->token_identifier = $query['ti'];
                    }
                } else {
                    session()->flash('alert-status', 'Access link is invalid.');
                    return;
                }
            } catch (Exception $e) {
                session()->flash('alert-status', 'Access link is invalid.');
                // dd($e, $e->getMessage());
                return;
            }
        } else {
            session()->flash('alert-status', 'URL is not valid.');
            return;
        }

        return true;
    }

    /**
     * Parse the IOWR csv value.
     *
     * @return mixed
     */
    public function getCsvParts()
    {
        try {
            // Parse csv parts
            $str_arr = explode(",", $this->iowr_csv_value);
            if (count($str_arr) > 1) {
                $this->visitor_identity_key = trim($str_arr[0]);
                $this->iowr_access_link = trim($str_arr[1]);
                if (count($str_arr) > 2) {
                    $this->visitor_id = trim($str_arr[2]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            // dd($e, $e->getMessage());
            return false;
        }
    }

    public function resetForm()
    {
        $this->visitor_id = '';
        $this->rlwr_event_id = '';
        $this->iowr_event_id = '';
        $this->iowr_access_link = '';
        $this->iowr_csv_value = '';
        $this->visitor_identity_key = '';

        return true;
    }

    public function render()
    {
        return view('livewire.qlink.create-qlink-form');
    }
}
