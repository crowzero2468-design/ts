<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard2Controller extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // ====== Technicians ======
        $totalTech = $db->table('tb_it')
            ->where('status', 'active')
            ->whereIn('role', ['user', 'admin', '3'])
            ->countAllResults();

        $offDuty = $db->table('tb_it')
            ->where('status', 'inactive')
            ->whereIn('role', ['user', 'admin', '3'])
            ->countAllResults();

        // ====== Average Ping ======
        $avgPing = $db->table('tb_speedtest')
            ->selectAvg('ping')
            ->get()
            ->getRow();

        $avgPingValue = $avgPing ? round($avgPing->ping, 2) : 0;

        // ====== Average Temp ======
        $avgTemp = $db->table('tb_temp')
            ->selectAvg('temp')
            ->get()
            ->getRow();

        $avgTempValue = $avgTemp ? round($avgTemp->temp, 2) : 0;

        // ====== LINE CHART ======
        $troubles = $db->table('tbtrouble')
            ->select('ts_type, COUNT(*) as total')
            ->groupBy('ts_type')
            ->get()
            ->getResult();

        $labels = [];
        $troubleData = [];

        foreach ($troubles as $row) {
            $labels[] = $row->ts_type;
            $troubleData[] = (int) $row->total;
        }

        // ====== BAR CHART ======
        $barQuery = $db->table('tbtrouble')
            ->select('description, COUNT(*) as total')
            ->groupBy('description')
            ->having('COUNT(*) >=', 10)
            ->orderBy('total', 'DESC')
            ->get()
            ->getResult();

        $barLabels = [];
        $barData = [];

        foreach ($barQuery as $row) {
            $barLabels[] = $row->description;
            $barData[]   = (int) $row->total;
        }

        // ====== MOST ACTIVE TECHNICIANS ======
           $techActivitiesUser = $db->query("
    SELECT 
        i.id AS tech_id,
        i.name,
        SUM(x.cnt) AS total,
        MAX(x.latest_time) AS latest_time
    FROM (
        SELECT 
            t.person AS tech_id,
            t.time AS latest_time,
            1 AS cnt
        FROM tbtrouble t
        WHERE t.status = 'Done'

        UNION ALL

        SELECT 
            i2.id AS tech_id,
            t.time AS latest_time,
            1 AS cnt
        FROM tbtrouble t
        LEFT JOIN tb_it i2 ON i2.name = t.personnel
        WHERE t.status = 'Done'
    ) x
    JOIN tb_it i ON i.id = x.tech_id
    WHERE i.role IN ('user', '3')
    AND i.name != 'admin'
    GROUP BY i.id, i.name
    ORDER BY total DESC
")->getResult();

$techActivitiesAdmin = $db->query("
    SELECT 
        i.id AS tech_id,
        i.name,
        SUM(x.cnt) AS total,
        MAX(x.latest_time) AS latest_time
    FROM (
        SELECT 
            t.person AS tech_id,
            t.time AS latest_time,
            1 AS cnt
        FROM tbtrouble t
        WHERE t.status = 'Done'

        UNION ALL

        SELECT 
            i2.id AS tech_id,
            t.time AS latest_time,
            1 AS cnt
        FROM tbtrouble t
        LEFT JOIN tb_it i2 ON i2.name = t.personnel
        WHERE t.status = 'Done'
    ) x
    JOIN tb_it i ON i.id = x.tech_id
    WHERE i.role = 'admin'
    GROUP BY i.id, i.name
    ORDER BY total DESC
")->getResult();

        // ====== TECH TROUBLE BREAKDOWN (FIXED) ======
$techTroublesRaw = $db->query("
    SELECT 
        i.id AS tech_id,
        i.name,
        x.description,
        COUNT(*) AS total
    FROM (

        -- PERSON (handler)
        SELECT 
            t.person AS tech_id,
            t.description
        FROM tbtrouble t
        WHERE t.status = 'Done'

        UNION ALL

        -- PERSONNEL (encoder → name to ID)
        SELECT 
            i2.id AS tech_id,
            t.description
        FROM tbtrouble t
        JOIN tb_it i2 ON i2.name = t.personnel
        WHERE t.status = 'Done'

    ) x

    JOIN tb_it i ON i.id = x.tech_id

    GROUP BY i.id, i.name, x.description
    HAVING COUNT(*) > 10
    ORDER BY total DESC
")->getResult();

       $techTroubleMap = [];

foreach ($techTroublesRaw as $row) {

    $key = $row->tech_id;

    if (!isset($techTroubleMap[$key])) {
        $techTroubleMap[$key] = [];
    }

    $techTroubleMap[$key][] =
        $row->description . ' (' . $row->total . ')';
}

        // ====== OTHER STATS ======
        $totalTroubleshoots = $db->table('tbtrouble')->where('status', 'Done')->countAllResults();
        $totalInspected = $db->table('tb_tools')->countAllResults();

        $firstTrouble = $db->table('tbtrouble')
            ->select('time')
            ->orderBy('time', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        $startDate = $firstTrouble
            ? date('M d, Y', strtotime($firstTrouble->time))
            : date('M d');

        $endDate = date('M d, Y');

        $totalUsers = $db->table('tb_it')->countAllResults();

        $totalAdmin = $db->table('tb_it')
            ->where('role', '3')
            ->countAllResults();

        // ====== PASS TO VIEW ======
        return view('admin/dashboard', [
            'totalTech'   => $totalTech,
            'offDuty'     => $offDuty,
            'avgPing'     => $avgPingValue,
            'avgTemp'     => $avgTempValue,

            'totalTroubleshoots' => $totalTroubleshoots,
            'totalInspected'     => $totalInspected,
            'totalUsers'         => $totalUsers,
            'totalAdmin'         => $totalAdmin,

            'troubleLabels' => $labels,
            'troubleData'   => $troubleData,

            'barLabels' => $barLabels,
            'barData'   => $barData,

            'techActivitiesUser'  => $techActivitiesUser,
            'techActivitiesAdmin' => $techActivitiesAdmin,
            'techTroubleMap'  => $techTroubleMap,

            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);
    }
}