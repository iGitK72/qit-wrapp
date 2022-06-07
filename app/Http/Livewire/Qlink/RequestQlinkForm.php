<?php

namespace App\Http\Livewire\Qlink;

use App\Models\Qlink;
use App\Models\QlinkConfiguration;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use NumberFormatter;

class RequestQlinkForm extends Component
{
    public $qlink;
    public $access_link_exists;
    public $visitor_id;
    public $rlwr_event_id;
    public $status_messages = [];
    public $iowr_access_link;
    public $lock;
    public $key;
    public $challenge_key;
    public $params_valid;
    public $show_copy_link;
    public $show_claim_link;
    
    protected $rules = [
        'visitor_id' => 'required',
        'key' => 'required',
      ];
    protected $messages = [
        'visitor_id.required' => 'You must enter your unique Visitor User ID ie. email.',
            ];

    public function mount()
    {
        // Get info from URL to retrieve Link Challenge info; default 20 minutes validity
        // Get record based on Customer and Event if present otherwise ERROR
        // Validation of url for tampering queue-id and event-id

        $this->params_valid = false;
        $this->validateRlwrUrl();
    }

    public function validateRlwrUrl()
    {
        // Example full URL
        // http://localhost/qlink/request?qitq=43b6d619-f902-471a-a477-ad39be770970&qitp=29a0b770-3100-4f70-b0d7-3b37c50f05b4&qitts=1654476657&qitc=kehatest&qite=test009&qitrt=Queue&qith=92567a57ea66804cd8fc65fc5e4375a5
        $url = URL::full(); // Same as url()->full(); BUT VScode complains about this and it is annoying enough to just be more verbose
        $parts = parse_url($url);

        if (array_key_exists('query', $parts)) {
            parse_str($parts['query'], $query);

            if (array_key_exists('qitts', $query)) {
                $qitts = Carbon::createFromTimestamp($query['qitts']);
                $validityDateTime = Carbon::now()->subMinutes(20);
                if ($validityDateTime->lessThanOrEqualTo($qitts)) {
                    $this->params_valid = true;
                }
            }

            if ($this->params_valid) {
                if (array_key_exists('qith', $query)) {
                    $this->params_valid = ($query['qith'] === '') ? false : true ;
                } else {
                    $this->params_valid = false;
                    $this->status_messages[] = 'Link has been tampered with.  Invalid hash.';
                    return false;
                }

                if (array_key_exists('qitc', $query) && $this->params_valid) {
                    $this->params_valid = ($query['qitc'] === '') ? false : true ;
                } else {
                    $this->params_valid = false;
                    $this->status_messages[] = 'Invalid customer.';
                    return false;
                }

                if (array_key_exists('qite', $query) && $this->params_valid) {
                    $this->params_valid = ($query['qite'] === '') ? false : true ;
                    $this->rlwr_event_id = $query['qite'];
                } else {
                    $this->params_valid = false;
                    $this->status_messages[] = 'Invalid event.';
                    return false;
                }
            }

            if ($this->params_valid === false) {
                $this->status_messages[] = 'Invalid or expired request link used.';
                return false;
            }
        } else {
            $this->params_valid = false;
            $this->status_messages[] = 'Invalid request.';
            return false;
        }

        //Get qitc from parts and then get qlink config for that customer and validate qid has valid redirect time
        $api_access_key = QlinkConfiguration::where('customer_id', $query['qitc'])->pluck('api_access_key');

        try {
            $decrypted = Crypt::decryptString($api_access_key);
        } catch (DecryptException $e) {
            //dd($e, $e->getMessage());
            $this->params_valid = false;
            $this->status_messages[] = 'Sorry. Invalid Customer info.';
            return false;
        }

        $apiUrl = "https://" . $query['qitc'] . ".queue-it.net/api/queue/queueitem/". $query['qitc'] ."/queueid/" . $query['qitq'];
        try {
            $response = Http::withHeaders([
                'accept' => 'text/plain',
                'api-key' => $decrypted,
            ])->get($apiUrl);
        } catch (Exception $e) {
            //dd($e, $e->getMessage());
            $this->params_valid = false;
            $this->status_messages[] = 'Unexpected error. Check your link and try again. - Customer info mismatch.';
            return false;
        }

        $this->rlwr_queue_id = $query['qitq'];
        $this->rlwr_queue_id_used = false;

        if (!$response->successful()) {
            $this->params_valid = false;
            $this->status_messages[] = 'Invalid request. You must go back to the waiting room and get a new place in line. - 1x01';   // Invalid Queue-id or Link tampered with
            return false;
        } else {
            $redirectCount = count($response->json()['redirectDetails']) - 1;
            $haystack = $response->json()['redirectDetails'][$redirectCount]['redirectTimeUtc'];

            $needle = substr($qitts->toJson(), 0, 20);
            if (stripos($haystack, $needle)===0 && $this->rlwr_event_id === $response->json()['eventId']) {
                $this->isRlwrQidUsed();
            } else {
                $this->params_valid = false;
                $this->status_messages[] = 'Invalid request. You must go back to the waiting room and get a new place in line. - 1x02';   // Event ID tampered with OR Link is expired past 20 minutes.
                return false;
            }
        }
        
        return $response->successful();
    }

    public function next()
    {
        $this->show_copy_link = false;

        $this->validateOnly('visitor_id');
        $this->key = '';
        
        if ($this->isRlwrQidUsed()) {
            if ($this->visitor_id === $this->qlink->visitor_id) {
                $accessLink = $this->qlink;
            } else {
                $this->show_claim_link = false;
                $this->params_valid = false;
                $this->status_messages[] = 'Queue id has been previously used or you typed in the wrong visitor id. Refresh your browser to try again.';
                return;
            }
        } else {
            $accessLink = Qlink::where('visitor_id', $this->visitor_id)
            ->where('rlwr_event_id', $this->rlwr_event_id)
            ->first();
        }
        
        $this->access_link_exists = !is_null($accessLink);    // set boolean
        if (!$this->access_link_exists) {
            // Find an available IOWR link
            $this->qlink = Qlink::where('rlwr_event_id', $this->rlwr_event_id)->where('visitor_id', null)->first();
            
            $accessLink = $this->qlink;
            if (!$accessLink) {
                $this->show_claim_link = false;
                $this->params_valid = false;
                $this->status_messages[] = 'Sorry.  No valid access links are available for this waiting room.';

                return;
            }

            $this->show_claim_link = true;
        } else {
            $this->qlink = $accessLink;
            
            $this->show_claim_link = true;
        }

        // Get a valid lock and then set values
        $this->getLockAndKey();
        $this->iowr_access_link = $this->qlink->access_link;
        $this->rlwr_queue_id_used = $this->qlink->rlwr_queue_id_used;

        $this->emit('validated');
    }

    public function unlock()
    {
        $this->show_copy_link = false;
        $this->validateOnly('key');

        if ($this->checkLockAndKey()) {
            $this->setLockAndKey();
        } else {
            $this->addError('key', 'The entered key does not match.');
        }
    }
    
    public function isRlwrQidUsed()
    {
        $qid_qlink = $this->getQlinkByQid();
        if ($qid_qlink) {
            $this->qlink = $qid_qlink;
            $this->rlwr_queue_id_used = $qid_qlink->rlwr_queue_id_used;
            return true;
        }
        return false;
    }
    
    // Returns collection of first qlink
    public function getQlinkByQid()
    {
        return Qlink::where('rlwr_event_id', $this->rlwr_event_id)
            ->where('rlwr_queue_id', $this->rlwr_queue_id)->first();
    }

    public function getLock()
    {
        return ($this->qlink->lock) ? : Inspiring::quote();
    }
    
    public function setLock($key=null)
    {
        // Get the Key word from the db key or by passing the ordinal number digit ie. pass 9 for the 9th word
        $key = $key ? $key - 1 : $this->key_index - 1;
        $lockparts = explode(" ", $this->lock);

        return rtrim($lockparts[$key], ". ,");
    }

    public function getLockAndKey()
    {
        $this->lock = $this->getLock();

        if ($this->access_link_exists && $this->qlink->key) {
            $this->key_index = $this->qlink->key;
            
            // Override key message for existing Lock so ask for the Key
            $this->key_message = 'To claim your link enter your key to the lock phrase above.';
        } else {
            $lockPhraseLen = Str::of($this->lock)->wordCount();
            $this->key_index = rand(1, $lockPhraseLen);
            $this->key_index = $this->key_index ? : 1;

            $locale = 'en_US';
            $nf = new NumberFormatter($locale, NumberFormatter::ORDINAL);
            if ($this->key_index > Str::of($this->lock)->wordCount()) {
                $this->key_index = 1;
            }

            $this->key_message = "Enter the ". $nf->format($this->key_index) . " word of the lock phrase as your Key. RESET to get a new lock phrase. NOTE: The \"-\" symbol is also counted as a word.";
        }
    }

    public function checkLockAndKey()
    {
        return (strcasecmp($this->key, $this->setLock())===0);
    }

    public function setLockAndKey()
    {
        $this->qlink->lock = $this->qlink->lock ? : $this->lock;
        $this->qlink->key = $this->qlink->key ? : $this->key_index;

        $this->qlink->visitor_id = $this->visitor_id;
        $this->qlink->rlwr_queue_id = $this->rlwr_queue_id;
        $this->qlink->rlwr_queue_id_used = true;

        if ($this->qlink->save()) {
            $this->show_copy_link = true;
            $this->emit('unlocked');
            return;
        } else {
            $this->show_copy_link = false;
            $this->rlwr_queue_id_used = false;
            $this->addError('key', 'Unexpected error.  Please try again.');
            return;
        }

        $this->show_copy_link = true;
        return;
    }

    public function copy()
    {
        $this->emit('copied');
        return;
    }

    public function resetForm()
    {
        $this->visitor_id = '';
        $this->show_claim_link = false;
        $this->show_copy_link = false;

        $this->isRlwrQidUsed();

        return true;
    }

    public function render()
    {
        return view('livewire.qlink.request-qlink-form');
    }
}
