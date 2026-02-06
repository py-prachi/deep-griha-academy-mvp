<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Interfaces\AttendanceInterface;

class AttendanceRepository implements AttendanceInterface {
   public function saveAttendance($request)
{
    try {
        // Handle both array and Request safely
        $attendanceDate = is_array($request)
            ? ($request['attendance_date'] ?? date('Y-m-d'))
            : ($request->input('attendance_date', date('Y-m-d')));

        $studentIds = is_array($request)
            ? $request['student_ids']
            : $request->input('student_ids');

        $statuses = is_array($request)
            ? ($request['status'] ?? [])
            : ($request->input('status', []));

        foreach ($studentIds as $student_id) {
            Attendance::updateOrCreate(
                [
                    'student_id'      => $student_id,
                    'class_id'        => is_array($request) ? $request['class_id'] : $request->input('class_id'),
                    'section_id'      => is_array($request) ? $request['section_id'] : $request->input('section_id'),
                    'course_id'       => is_array($request) ? $request['course_id'] : $request->input('course_id'),
                    'session_id'      => is_array($request) ? $request['session_id'] : $request->input('session_id'),
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status'     => $statuses[$student_id] ?? 'off',
                    'updated_at' => now(),
                ]
            );
        }
    } catch (\Exception $e) {
        throw new \Exception('Failed to save attendance. ' . $e->getMessage());
    }
}



   public function updateAttendance($attendance_id, $status)
{
    try {
        $attendance = Attendance::findOrFail($attendance_id);
        $attendance->status = $status;   // ✅ correct column
        $attendance->save();
    } catch (\Exception $e) {
        throw new \Exception('Failed to update attendance. '.$e->getMessage());
    }
}


public function prepareInput($request)
{
    $input = [];
    $now = Carbon::now()->toDateTimeString();

    // ✅ Use selected date if provided (admin), else today (teacher)
    $date = isset($request['attendance_date'])
    ? Carbon::parse($request['attendance_date'])->setTime(12, 0, 0)->toDateTimeString()
    : Carbon::now()->setTime(12, 0, 0)->toDateTimeString();

    for ($i = 0; $i < count($request['student_ids']); $i++) {
        $student_id = $request['student_ids'][$i];

        $input[] = [
            'status'           => $request['status'][$student_id] ?? 'off',
            'class_id'         => $request['class_id'],
            'student_id'       => $student_id,
            'section_id'       => $request['section_id'],
            'course_id'        => $request['course_id'],
            'session_id'       => $request['session_id'],
            'attendance_date'  => $attendanceDate,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];
    }

    return $input;
}



    public function getSectionAttendance($class_id, $section_id, $session_id, $date = null)
{
    try {
        return Attendance::with('student')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->when($date, function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            }, function ($q) {
                $q->whereDate('created_at', Carbon::today());
            })
            ->get();
    } catch (\Exception $e) {
        throw new \Exception('Failed to get attendances. ' . $e->getMessage());
    }
}


    
    public function getCourseAttendance($class_id, $course_id, $session_id, $date = null)
{
    try {
        return Attendance::with('student')
            ->where('class_id', $class_id)
            ->where('course_id', $course_id)
            ->where('session_id', $session_id)
            ->when($date, function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            }, function ($q) {
                $q->whereDate('created_at', Carbon::today());
            })
            ->get();
    } catch (\Exception $e) {
        throw new \Exception('Failed to get attendances. ' . $e->getMessage());
    }
}


    public function getStudentAttendance($session_id, $student_id) {
        try {
            return Attendance::with(['section','course'])
                            ->where('student_id', $student_id)
                            ->where('session_id', $session_id)
                            ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get attendances. '.$e->getMessage());
        }
    }

    public function getStudentAttendanceByDate($session_id, $student_id, $date)
    {
    return Attendance::with(['section', 'course'])
        ->where('student_id', $student_id)
        ->where('session_id', $session_id)
        ->whereDate('created_at', $date)
        ->get();
    }

}