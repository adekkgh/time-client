<?php

namespace App\Http\Controllers;

use App\Models\Time;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeController extends Controller
{

    // temporal mock imitating receiving time for external server
    public function imitateReceivingTime(Request $request) {

        if (!isset($request['data']['timestamp'])) {
            return response()->json(['error' => 'timestamp not founf'], 400);
        }

        $timestamp = Carbon::parse($request['data']['timestamp']);

        $time = new Time();
        $time->server_time = $timestamp;
        $time->save();

        return response()->json([
           'message' => 'Время получено и успешно записано',
           'server_time' => $timestamp->toDateTimeString()
        ]);
    }

    // get list of received from server time entries within requested time period
    public function getTimeEntries(Request $request) {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $per_page = $request->input("per_page", 10);

        $entries = Time::where('server_time', '>=', $request->start_date)
            ->where('server_time', '<=', $request->end_date)
            ->orderBy('server_time', 'desc')
            ->paginate($per_page);

        return response()->json($entries);
    }
}
