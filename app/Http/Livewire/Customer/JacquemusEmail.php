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

    public function mount()
    {
        // Get qlink by eventid hardcoded to a Jacquemus known IOWR and RLWR combo
        // If current qlink usage_count then find the first three qlink with same criteria plus usage_count != current usage_count
        // If you can't find 3 then find the first equal to current usage_count and update usage_count++ and use that
        //
        // $link_one = $qlink->iowr_access_link, etc. up to _three

        $qlinkNew = Qlink::find(401);
        $qlinkNew->usage_count = 1;
        $qlinkNew->save();
        $qlinkNew = Qlink::find(402);
        $qlinkNew->usage_count = 2;
        $qlinkNew->save();

        $qlinkIndex = Qlink::where('event_id', 'jacquemusxnike001')->orderBy('usage_count', 'desc')->limit(1)->get(['usage_count', 'event_id', 'id']);
        //dd($qlinkIndex);
        $qlinksAll = Qlink::where('event_id', 'jacquemusxnike001')->limit(10)->get(['usage_count', 'event_id', 'id', 'access_link']);
        $qlinks = $qlinksAll->where('usage_count', '!=', $qlinkIndex[0]['usage_count'])->sortBy('usage_count');
        
        if ($qlinks) {
            $i=1;
            foreach ($qlinks as $qlink) {
                if ($qlink->usage_count || $qlink->usage_count < $qlinkIndex[0]['usage_count']) {
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
