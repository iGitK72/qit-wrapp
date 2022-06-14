<?php

namespace App\Http\Controllers\Livewire;

use App\Http\Controllers\Controller;
use App\Models\Qlink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QlinkController extends Controller
{
    /**
     * Show all links.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $myQlinks = Qlink::where('visitor_id', auth()->user()->email)->get();
        $myAdminQlinks = Qlink::where('user_id', auth()->user()->id)
        ->where('team_id', auth()->user()->current_team_id)
        ->get();

        $qlinks = $myQlinks->merge($myAdminQlinks);
        
        return view('qlink.show', [
            'user' => auth()->user(),
            'qlinks' => $qlinks,
        ]);
    }
    
    /**
     * Show the qlink admin management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function admin()
    {
        if (auth()->user()->email != 'kevinlhall72@gmail.com') {
            abort(403);
        }

        return view('qlink.show', [
            'user' => auth()->user(),
            'qlinks' => Qlink::all(),
        ]);
    }
    
    /**
     * Show the qlink management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // todo: Refactor to Gate policy
        $qlink = Qlink::find($id);
        if ($qlink->user_id == auth()->user()->id || 'kevinlhall72@gmail.com' == auth()->user()->email) {
            return view('qlink.edit', [
                'qlink' => $qlink,
            ]);
        } else {
            abort(403);
        }
    }
    
    /**
     * Request a link for IOWR.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function getLink()
    {
        return view('qlink.request');
    }

    /**
     * Show the qlink creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        if ($request->user()) {
            return view('qlink.create', [
                'user' => $request->user(),
            ]);
        } else {
            abort(403);
        }
    }

    /**
     * Show the qlink configuration page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function configure(Request $request)
    {
        if ($request->user()) {
            return view('qlink.configure', [
                'user' => $request->user(),
            ]);
        } else {
            abort(403);
        }
    }

    /**
     * Show the qlink verification page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function verify(Request $request)
    {
        if ($request->user()) {
            return view('qlink.verify', [
                'user' => $request->user(),
            ]);
        } else {
            abort(403);
        }
    }

    /**
     * Show the invite only page.
     *
     * @return \Illuminate\View\View
     */
    public function inviteOnly()
    {
        return view('qlink.invite-only');
    }
    /**
     * Show the authenticated invite only page.
     *
     * @return \Illuminate\View\View
     */
    public function inviteOnlyAuth()
    {
        return view('qlink.invite-only-auth');
    }
}
