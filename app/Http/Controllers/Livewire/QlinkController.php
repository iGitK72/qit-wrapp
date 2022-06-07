<?php

namespace App\Http\Controllers\Livewire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QlinkController extends Controller
{
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
     * Show the qlink management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $team = auth()->user()->current_team_id;

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        return view('qlink.show', [
            'user' => auth()->user(),
            'team' => $team,
        ]);
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
     * Show the qlink configuration screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function configure(Request $request)
    {
        //Gate::authorize('create', Jetstream::newTeamModel());

        if ($request->user()) {
            return view('qlink.configure', [
                'user' => $request->user(),
            ]);
        } else {
            abort(403);
        }
    }
}
