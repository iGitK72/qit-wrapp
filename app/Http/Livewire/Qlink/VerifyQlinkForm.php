<?php

namespace App\Http\Livewire\Qlink;

use App\Models\Qlink;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class VerifyQlinkForm extends Component
{
    public function mount($customer=null, $team=null)
    {
        dd($customer, $team);

        if ($team) {
            $teamId = $team;
        } else {
            $teamId = 1;
            $this->noteam = true;
        }

        //$this->team = Jetstream::newTeamModel()->findOrFail($teamId);
        // dd($this->noteam, $teamId, $this->team, $team, $customer);
        
        /*if (!$this->noteam) {
            if (Gate::denies('view', $this->team)) {
                abort(403);
            }
        }*/

        $arr_cookie_options = array(
                'expires' => time() + 60*60*24*30,
                'path' => '/',
                'domain' => '.example.com', // leading dot for compatibility or use subdomain
                'secure' => true,     // or false
                'httponly' => true,    // or false
                'samesite' => 'None' // None || Lax  || Strict
                );
        $arr_cookie_options = array(
                    'expires' => time() + 60*60*24*30,
                    'path' => '/',
                    'secure' => true,     // or false
                    'httponly' => true,    // or false
                    'samesite' => 'None' // None || Lax  || Strict
                    );

        setcookie('name', 'some value', $arr_cookie_options);
        setcookie('QueueITAccepted-SDFrts345E-V3_kehatest', 'correct kehatest', $arr_cookie_options);
        setcookie('QueueITAccepted-SDFrts345E-V3_kehatest4', 'kajfæajfdjækehatest', $arr_cookie_options);
        setcookie('QueueITAccepted-SDFrts345E-V3_anotherone', 'anotherone', $arr_cookie_options);

        if ($customer === null) {
            $url = URL::full();
            $parts = parse_url($url);
            if (array_key_exists('query', $parts)) {
                parse_str($parts['query'], $query);
                if (array_key_exists('qitc', $query)) {
                    $customer = $query['qitc'];
                }
                if (array_key_exists('qitq', $query)) {
                    $queueId = $query['qitq'];
                }
            } else {
                // Should error but defaulting KEHATEST
            }

            //dd(url()->full());
        }

        //a8c4820d-7a82-4b58-a816-88f31428c5c0
        //dd($queueId);
        // make this the Qlink Config table that stores only customer_id and api_key
        //dd(auth()->user()->password);
        $apiAccessKey = Qlink::where('customer_id', $customer)->get();
        $apiAccessKey = 'auth()->user()->password';

        $accesskeyEnc = Crypt::encryptString($apiAccessKey);

        $accesskeyDec = Crypt::decryptString($accesskeyEnc);

        //dd($accesskeyDec, $accesskeyEnc);
        $apiUrl = "https://kehatest.queue-it.net/api/queue/queueitem/" . $customer . "/queueid/" . $queueId;

        $response = Http::withHeaders([
                'accept' => 'text/plain',
                'api-key' => config('app.qit_api')
            ])->get($apiUrl);

        $queueInfo = $response->json();
        //dd($response->json()['eventId']);
        // $queueInfo['eventId'] == a valid Qlink record for the Customer ID then return an IOWR access link

        $set = false;
        $cookie_name = 'QueueITAccepted-SDFrts345E-V3_';
        $qcookie = $cookie_name . $customer;

        foreach ($_COOKIE as $name => $value) {
            echo $value.'<br>';
            if ((stripos($name, $cookie_name) === 0) && (stripos($value, $customer) !== false)) {
                echo "A Cookie named '$name' is set with value '$value'!".'<br>';
                if ($name === $qcookie) {
                    echo "The Cookie named '$name' is set with value '$value'!".'<br>';
                }
                // check qlink table to see if this exists with the customer id and the
                $set = true;
            }
        }
        if (!$set) {
            echo 'No cookie found :(';
        }
    }
        
    public function render()
    {
        return view('livewire.qlink.verify-qlink-form');
    }
}
