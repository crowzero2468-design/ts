<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard2Controller extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // ====== YEARS ======
        $yearsQuery = $db->table('tbtrouble')
            ->select("YEAR(time) as year")
            ->groupBy("YEAR(time)")
            ->orderBy("year", "DESC")
            ->get()
            ->getResult();

        $years = array_column($yearsQuery, 'year');

        $selectedYear = $this->request->getGet('year') ?? ($years[0] ?? date('Y'));

        $start = $selectedYear . '-01-01 00:00:00';
        $end   = $selectedYear . '-12-31 23:59:59';

        // ====== TECHS ======
        $totalTech = $db->table('tb_it')
            ->where('status', 'active')
            ->whereIn('role', ['user', 'admin', '3'])
            ->countAllResults();

        $offDuty = $db->table('tb_it')
            ->where('status', 'inactive')
            ->whereIn('role', ['user', 'admin', '3'])
            ->countAllResults();

        // ====== AVERAGES ======
        $avgPingRow = $db->table('tb_speedtest')->selectAvg('ping')->get()->getRow();
        $avgPingValue = $avgPingRow ? round($avgPingRow->ping, 2) : 0;

        $avgTempRow = $db->table('tb_temp')->selectAvg('temp')->get()->getRow();
        $avgTempValue = $avgTempRow ? round($avgTempRow->temp, 2) : 0;

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

        // ====== MOST ACTIVE TECHS (USER) ======
        $techActivitiesUser = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                COUNT(DISTINCT t.id) AS total,
                MAX(t.time) AS latest_time,
                COALESCE(AVG(r.rate), 0) AS avg_rating
            FROM tb_it i
            LEFT JOIN tbtrouble t 
                ON t.person = i.id 
                AND t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'
            LEFT JOIN tb_rate r 
                ON r.trouble_id = t.id
                AND r.user_id = i.id
                AND r.rateddate BETWEEN '$start' AND '$end'
            WHERE i.role IN ('user', '3')
            GROUP BY i.id, i.name
            ORDER BY total DESC
        ")->getResult();

        // ====== MOST ACTIVE TECHS (ADMIN) ======
        $techActivitiesAdmin = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                COUNT(DISTINCT t.id) AS total,
                MAX(t.time) AS latest_time,
                COALESCE(AVG(r.rate), 0) AS avg_rating
            FROM tb_it i
            LEFT JOIN tbtrouble t 
                ON t.person = i.id 
                AND t.status = 'Done'
                AND t.time BETWEEN '$start' AND '$end'
            LEFT JOIN tb_rate r 
                ON r.trouble_id = t.id
                AND r.user_id = i.id
                AND r.rateddate BETWEEN '$start' AND '$end'
            WHERE i.role = 'admin'
            GROUP BY i.id, i.name
            ORDER BY total DESC
        ")->getResult();

        // ====== BREAKDOWN ======
        $techTroublesRaw = $db->query("
            SELECT 
                i.id AS tech_id,
                i.name,
                t.description,
                COUNT(*) AS total
            FROM tbtrouble t
            JOIN tb_it i ON i.id = t.person
            WHERE t.status = 'Done'
            AND t.time BETWEEN '$start' AND '$end'
            GROUP BY i.id, i.name, t.description
            HAVING COUNT(*) > 10
            ORDER BY total DESC
        ")->getResult();

        $techTroubleMap = [];
        foreach ($techTroublesRaw as $row) {
            $techTroubleMap[$row->tech_id][] =
                $row->description . ' (' . $row->total . ')';
        }

        // ====== TOTALS ======
        $totalTroubleshoots = $db->table('tbtrouble')
            ->where('status', 'Done')
            ->where('time >=', $start)
            ->where('time <=', $end)
            ->countAllResults();

        $totalInspected = $db->table('tb_tools')->countAllResults();

        $totalUsers = $db->table('tb_it')->countAllResults();

        $totalAdmin = $db->table('tb_it')
            ->where('role', '3')
            ->countAllResults();

        // ====== VIEW ======
        return view('admin/dashboard', [
            'years' => $years,
            'selectedYear' => $selectedYear,

            'totalTech' => $totalTech,
            'offDuty' => $offDuty,
            'avgPing' => $avgPingValue,
            'avgTemp' => $avgTempValue,

            'totalTroubleshoots' => $totalTroubleshoots,
            'totalInspected' => $totalInspected,
            'totalUsers' => $totalUsers,
            'totalAdmin' => $totalAdmin,

            'troubleLabels' => $labels,
            'troubleData' => $troubleData,

            'barLabels' => $barLabels,
            'barData' => $barData,

            'techActivitiesUser' => $techActivitiesUser,
            'techActivitiesAdmin' => $techActivitiesAdmin,
            'techTroubleMap' => $techTroubleMap,

            'startDate' => date('M d, Y', strtotime($start)),
            'endDate' => date('M d, Y', strtotime($end)),
        ]);
    }
}