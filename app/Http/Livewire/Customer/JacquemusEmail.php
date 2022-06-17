<?php

namespace App\Http\Livewire\Customer;

use App\Models\Qlink;
use Livewire\Component;

class JacquemusEmail extends Component
{
    public $links=[];
    public $link_one;
    public $link_two;
    public $link_three;
    public $use_test_wr;

    public function mount($use_test_wr=false)
    {
        // Get qlink by eventid hardcoded to a Jacquemus known IOWR and a Test IOWR for preprod testing
        $this->use_test_wr = $use_test_wr;

        if ($this->use_test_wr) {
            $qlinkIndex = Qlink::where('event_id', 'jacquemusxniketest')->orderBy('usage_count', 'desc')->limit(1)->get(['usage_count', 'event_id', 'id']);
            $qlinksAll = Qlink::where('event_id', 'jacquemusxniketest')->get(['usage_count', 'event_id', 'id', 'access_link']);
        } else {
            $qlinkIndex = Qlink::where('event_id', 'jacquemusxnike001')->orderBy('usage_count', 'desc')->limit(1)->get(['usage_count', 'event_id', 'id']);
            $qlinksAll = Qlink::where('event_id', 'jacquemusxnike001')->get(['usage_count', 'event_id', 'id', 'access_link']);
        }

        $qlinks = (is_null($qlinkIndex[0]['usage_count']))
            ? $qlinksAll->sortBy('usage_count')
            : $qlinksAll->where('usage_count', '<=', $qlinkIndex[0]['usage_count'])->sortBy('usage_count')->take(10);
           
        if (!$qlinks->isEmpty()) {
            $i=1;
            foreach ($qlinks as $qlink) {
                if ((is_null($qlinkIndex[0]['usage_count'])) || $qlink->usage_count || $qlink->usage_count < $qlinkIndex[0]['usage_count']) {
                    $this->links[$i] = $qlink->access_link;
                    $qlink->usage_count = is_null($qlink->usage_count) ? 1 : $qlink->usage_count + 1;
                    $qlink->save();
                    $i++;
                }
                if ($i == 4) {
                    break;
                }
            }
            if ($i < 3) {
                for ($x = $i; $x < 4; $x++) {
                    $this->links[$x] = $this->links[1];
                }
            }
        } else {
            abort(404);
        }
            
        $this->link_one = $this->links[1];
        $this->link_two = $this->links[2];
        $this->link_three = $this->links[3];
    }
    
    public function render()
    {
        return view('livewire.customer.jacquemus-email');
    }
}
