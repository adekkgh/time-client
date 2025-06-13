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
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->input("per_page", 10);

        $timeZone = 'Europe/Moscow';

        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->start_date, $timeZone)
            ->timezone('UTC')
            ->startOfMinute();
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->end_date, $timeZone)
            ->timezone('UTC')
            ->startOfMinute();

        $entries = Time::whereBetween('server_time', [$start, $end])
            ->orderBy('server_time', 'desc')
            ->paginate($perPage);

        $entries->getCollection()->transform(function ($entry) use ($timeZone) {
            $entry->server_time = Carbon::parse($entry->server_time)->timezone($timeZone)->format('Y-m-d H:i:s');

            return $entry;
        });

        return response()->json($entries);
    }
}
