<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TimeSlot;

class TimeSlotController extends Controller
{
    public function index()
    {
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        return view('admin.timeslots.index', compact('timeSlots'));
    }

    public function create()
    {
        return view('admin.time-slots.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required',
        ]);
        $data['display_text'] = $data['start_time'] . ' - ' . $data['end_time'];
        TimeSlot::create($data);
        return redirect()->route('admin.time-slots.index')->with('success', 'Time slot created successfully.');
    }

    public function edit(TimeSlot $timeSlot)
    {
        return view('admin.time-slots.edit', compact('timeSlot'));
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $data = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required',
        ]);
        $data['display_text'] = $data['start_time'] . ' - ' . $data['end_time'];
        $timeSlot->update($data);
        return redirect()->route('admin.time-slots.index')->with('success', 'Time slot updated successfully.');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return redirect()->route('admin.time-slots.index')->with('success', 'Time slot deleted successfully.');
    }
}
