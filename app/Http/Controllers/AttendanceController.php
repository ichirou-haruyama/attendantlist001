<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $members = Member::query()->orderBy('id')->get();

        $counts = [
            'total' => $members->count(),
            'present' => $members->where('attendance_status', Member::STATUS_PRESENT)->count(),
            'absent' => $members->where('attendance_status', Member::STATUS_ABSENT)->count(),
            'unconfirmed' => $members->where('attendance_status', Member::STATUS_UNCONFIRMED)->count(),
        ];

        return view('attendance.index', [
            'members' => $members,
            'counts' => $counts,
        ]);
    }

    public function updateStatus(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_status' => 'required|in:'.implode(',', [
                Member::STATUS_PRESENT,
                Member::STATUS_ABSENT,
            ]),
        ]);

        $member->update([
            'attendance_status' => $validated['attendance_status'],
        ]);

        return redirect()->route('attendance.index');
    }
}
