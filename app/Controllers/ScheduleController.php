<?php

namespace App\Controllers;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Config\Database;

class ScheduleController extends BaseController
{

    protected $db;
    protected $table = 'tb_it';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* =========================
       LIST RECORDS
    ========================== */
    public function index()
    {
         $this->updateDutyStatus();

        $data['records'] = $this->db
            ->table($this->table)
            ->whereIn('status', ['active', 'inactive'])
            ->orderBy('status','status', 'ASC')
            ->get()
            ->getResultArray();

        $data['schedules'] = $this->db->table('tb_schedule s')
            ->select('s.schedule_date, s.start_time, s.end_time, t.name, t.location')
            ->join('tb_it t', 't.id = s.tech_id')
            ->get()
            ->getResultArray();

        return view('admin/schedule', $data);
    }



private function updateDutyStatus()
{
    $db = \Config\Database::connect();

    date_default_timezone_set('Asia/Manila');

    $nowDate = date('Y-m-d');
    $nowTime = date('H:i:s');

    // 1️⃣ First set everyone to inactive
    $db->table('tb_it')->update(['status' => 'inactive']);

    // 2️⃣ Get technicians scheduled today
    $schedules = $db->table('tb_schedule')
        ->where('schedule_date', $nowDate)
        ->get()
        ->getResult();

    foreach ($schedules as $schedule) {

        $start = $schedule->start_time;
        $end   = $schedule->end_time;

        $isOnDuty = false;

        // Normal shift (ex: 08:00 - 17:00)
        if ($start < $end) {
            if ($nowTime >= $start && $nowTime <= $end) {
                $isOnDuty = true;
            }
        }
        // Night shift (ex: 23:00 - 07:00)
        else {
            if ($nowTime >= $start || $nowTime <= $end) {
                $isOnDuty = true;
            }
        }

        if ($isOnDuty) {
            $db->table('tb_it')
                ->where('id', $schedule->tech_id)
                ->update(['status' => 'active']);
        }
    }
}




    public function import()
{
    $file = $this->request->getFile('excel_file');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'Invalid file.');
    }

    $spreadsheet = IOFactory::load($file->getTempName());
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray();

    if (count($rows) < 3) {
        return redirect()->back()->with('error', 'Excel format is invalid.');
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $dateRow  = $rows[1];
    $year     = date('Y');
    $month    = date('m');
    $inserted = 0;
    $skippedNoTech = 0;

    foreach ($rows as $rowIndex => $row) {

        if ($rowIndex < 2) continue;
        if (empty($row[1])) continue;

        // ✅ CLEAN TECH NAME (FIXED)
        $excelName = trim($row[1]);

        $excelName = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $excelName);
        $excelName = preg_replace('/\s+/', ' ', $excelName);
        $excelName = mb_strtolower($excelName);

        // match tech
        $tech = $db->query("
            SELECT * FROM tb_it
            WHERE LOWER(REPLACE(REPLACE(name, CHAR(160), ''), '  ', ' ')) = ?
            LIMIT 1
        ", [$excelName])->getRow();

        if (!$tech) {
            $skippedNoTech++;
            continue;
        }

        for ($col = 1; $col < count($row); $col++) {

            $shift = isset($row[$col]) ? trim($row[$col]) : '';
            $shift = strtoupper(str_replace(' ', '', $shift));

            $dayNumber = isset($dateRow[$col]) ? trim($dateRow[$col]) : '';

            if ($shift === '' || $shift === 'OFF') continue;
            if ($dayNumber === '') continue;

            $dayNumber = (int) filter_var($dayNumber, FILTER_SANITIZE_NUMBER_INT);
            if ($dayNumber <= 0) continue;

            $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $dayNumber);

            switch ($shift) {

                case '8-5':
                    $start_time = '08:00:00';
                    $end_time   = '17:00:00';
                    break;

                case '7-4':
                    $start_time = '07:00:00';
                    $end_time   = '16:00:00';
                    break;

                case '7-6':   // ✅ ADDED
                    $start_time = '07:00:00';
                    $end_time   = '18:00:00';
                    break;

                case '3-11':
                    $start_time = '15:00:00';
                    $end_time   = '23:00:00';
                    break;

                case '11-7':
                    $start_time = '23:00:00';
                    $end_time   = '07:00:00';
                    break;

                default:
                    continue 2;
            }

            // duplicate check
            $exists = $db->query("
                SELECT id FROM tb_schedule
                WHERE tech_id = ?
                AND schedule_date = ?
                LIMIT 1
            ", [$tech->id, $fullDate])->getRow();

            if ($exists) continue;

            $db->table('tb_schedule')->insert([
                'tech_id'       => $tech->id,
                'schedule_date' => $fullDate,
                'start_time'    => $start_time,
                'end_time'      => $end_time
            ]);

            $inserted++;
        }
    }

    $db->transComplete();

    return redirect()->back()->with(
        'success',
        "Schedule imported. Rows inserted: {$inserted} | Skipped (no tech match): {$skippedNoTech}"
    );
}

public function downloadTemplate()
{
    $filePath = WRITEPATH . 'templates/technician_schedule_template.xlsx';

    return $this->response->download($filePath, null);
}

}
