<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard2Controller extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // ====== GET AVAILABLE YEARS FROM DB ======
        $yearsQuery = $db->table('tbtrouble')
            ->select("YEAR(time) as year")
            ->groupBy("YEAR(time)")
            ->orderBy("year", "DESC")
            ->get()
            ->getResult();

        $years = array_column($yearsQuery, 'year');

        // ====== GET SELECTED YEAR ======
        $selectedYear = $this->request->getGet('year') ?? ($years[0] ?? date('Y'));

        $start = $selectedYear . '-01-01 00:00:00';
        $end   = $selectedYear . '-12-31 23:59:59';

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
            ->where('time >=', $start)
            ->where('time <=', $end)
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
            ->where('time >=', $start)
            ->where('time <=', $end)
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

        // ====== MOST ACTIVE TECHNICIANS (USER) ======
        $techActivitiesUser = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                SUM(x.cnt) AS total,
                MAX(x.latest_time) AS latest_time
            FROM (
                SELECT t.person AS tech_id, t.time AS latest_time, 1 AS cnt
                FROM tbtrouble t
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'

                UNION ALL

                SELECT i2.id AS tech_id, t.time AS latest_time, 1 AS cnt
                FROM tbtrouble t
                LEFT JOIN tb_it i2 ON i2.name = t.personnel
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'
            ) x
            JOIN tb_it i ON i.id = x.tech_id
            WHERE i.role IN ('user', '3')
            AND i.name != 'admin'
            GROUP BY i.id, i.name
            ORDER BY total DESC
        ")->getResult();

        // ====== MOST ACTIVE TECHNICIANS (ADMIN) ======
        $techActivitiesAdmin = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                SUM(x.cnt) AS total,
                MAX(x.latest_time) AS latest_time
            FROM (
                SELECT t.person AS tech_id, t.time AS latest_time, 1 AS cnt
                FROM tbtrouble t
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'

                UNION ALL

                SELECT i2.id AS tech_id, t.time AS latest_time, 1 AS cnt
                FROM tbtrouble t
                LEFT JOIN tb_it i2 ON i2.name = t.personnel
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'
            ) x
            JOIN tb_it i ON i.id = x.tech_id
            WHERE i.role = 'admin'
            GROUP BY i.id, i.name
            ORDER BY total DESC
        ")->getResult();

        // ====== TECH TROUBLE BREAKDOWN ======
        $techTroublesRaw = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                x.description,
                COUNT(*) AS total
            FROM (
                SELECT t.person AS tech_id, t.description
                FROM tbtrouble t
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'

                UNION ALL

                SELECT i2.id AS tech_id, t.description
                FROM tbtrouble t
                JOIN tb_it i2 ON i2.name = t.personnel
                WHERE t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'
            ) x
            JOIN tb_it i ON i.id = x.tech_id
            GROUP BY i.id, i.name, x.description
            HAVING COUNT(*) > 10
            ORDER BY total DESC
        ")->getResult();

        $techTroubleMap = [];

        foreach ($techTroublesRaw as $row) {
            $techTroubleMap[$row->tech_id][] =
                $row->description . ' (' . $row->total . ')';
        }

        // ====== FILTERED TOTALS ======
        $totalTroubleshoots = $db->table('tbtrouble')
            ->where('status', 'Done')
            ->where('time >=', $start)
            ->where('time <=', $end)
            ->countAllResults();

        $totalInspected = $db->table('tb_tools')->countAllResults();

        // ====== DATE DISPLAY ======
        $startDate = date('M d, Y', strtotime($start));
        $endDate   = date('M d, Y', strtotime($end));

        $totalUsers = $db->table('tb_it')->countAllResults();

        $totalAdmin = $db->table('tb_it')
            ->where('role', '3')
            ->countAllResults();

        // ====== PASS TO VIEW ======
        return view('admin/dashboard', [
            'years' => $years,
            'selectedYear' => $selectedYear,

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
            'techTroubleMap'      => $techTroubleMap,

            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);
    }
}