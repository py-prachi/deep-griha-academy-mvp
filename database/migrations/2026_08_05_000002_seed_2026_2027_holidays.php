<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Seeds the official 2026-2027 holiday list (schoolDocs/Holiday List 2026-2027.docx).
// Multi-day ranges (Diwali, Christmas) are expanded into one row per date.
class Seed20262027Holidays extends Migration
{
    public function up()
    {
        $session = DB::table('school_sessions')->where('session_name', '2026-2027')->first();
        if (!$session) {
            return;
        }
        $sessionId = $session->id;

        $holidays = [
            ['2026-05-27', 'Bakrid'],
            ['2026-07-05', 'DGS Foundation Day'],
            ['2026-08-15', 'Independence Day'],
            ['2026-08-26', 'Eid-e-Milad'],
            ['2026-08-28', 'Raksha Bandhan'],
            ['2026-09-04', 'Janmashtami'],
            ['2026-09-14', 'Ganesh Chaturthi'],
            ['2026-10-02', 'Gandhi Jayanti (Shramdaan by staff till half day)'],
            ['2026-10-20', 'Dusshera'],
            ['2027-01-15', 'Makar Sankranti'],
            ['2027-01-26', 'Republic Day'],
            ['2027-02-19', 'Shivaji Maharaj Jayanti'],
            ['2027-03-06', 'Maha Shivratri'],
            ['2027-03-10', 'Eid'],
            ['2027-03-22', 'Holi'],
            ['2027-03-26', 'Good Friday'],
            ['2027-04-07', 'Gudi Padwa'],
            ['2027-04-14', 'Ambedkar Jayanti'],
            ['2027-05-01', 'Maharashtra Day (Flag hoisting by staff)'],
        ];

        // Diwali Holidays: 04.11.2026 to 13.11.2026 (staff report back 14th Nov)
        $cursor = \Carbon\Carbon::parse('2026-11-04');
        $end    = \Carbon\Carbon::parse('2026-11-13');
        while ($cursor->lte($end)) {
            $holidays[] = [$cursor->toDateString(), 'Diwali Holidays'];
            $cursor->addDay();
        }

        // Christmas Holidays: 24.12.2026 to 01.01.2027 (staff report back 2nd Jan)
        $cursor = \Carbon\Carbon::parse('2026-12-24');
        $end    = \Carbon\Carbon::parse('2027-01-01');
        while ($cursor->lte($end)) {
            $holidays[] = [$cursor->toDateString(), 'Christmas Holidays'];
            $cursor->addDay();
        }

        $now = now();
        foreach ($holidays as [$date, $name]) {
            DB::table('holidays')->updateOrInsert(
                ['session_id' => $sessionId, 'date' => $date],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down()
    {
        $session = DB::table('school_sessions')->where('session_name', '2026-2027')->first();
        if ($session) {
            DB::table('holidays')->where('session_id', $session->id)->delete();
        }
    }
}
