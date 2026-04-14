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
            ->where('role', 'user')
            ->countAllResults();

        $offDuty = $db->table('tb_it')
            ->where('status', 'inactive')
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
        $techActivities = $db->table('tbtrouble t')
            ->select('i.id as tech_id, i.name, COUNT(t.id) as total, MAX(t.time) as latest_time')
            ->join('tb_it i', 'i.id = t.person', 'left')
            ->where('t.status', 'Done')
            ->where('i.name IS NOT NULL')
            ->where('i.name !=', '')
            ->groupBy('t.person')
            ->having('COUNT(t.id) >', 10)   // ✅ ONLY MORE THAN 10
            ->orderBy('total', 'DESC')
            ->get()
            ->getResult();

        // ====== TECH TROUBLE BREAKDOWN (FIXED) ======
        $techTroublesRaw = $db->table('tbtrouble t')
            ->select('t.person, i.name, t.description, COUNT(*) as total')
            ->join('tb_it i', 'i.id = t.person', 'left')
            ->where('t.status', 'Done')
            ->where('i.name IS NOT NULL')
            ->where('i.name !=', '')
            ->groupBy(['t.person', 't.description'])
            ->having('COUNT(*) >', 10)   // ✅ SAME FILTER
            ->orderBy('total', 'DESC')
            ->get()
            ->getResult();

        $techTroubleMap = [];

        foreach ($techTroublesRaw as $row) {

            $key = $row->person; // ✅ FIX: use ID instead of name

            if (!isset($techTroubleMap[$key])) {
                $techTroubleMap[$key] = [];
            }

            $techTroubleMap[$key][] =
                $row->description . ' (' . $row->total . ')';
        }

        // ====== OTHER STATS ======
        $totalTroubleshoots = $db->table('tbtrouble')->countAllResults();
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

            'techActivities'  => $techActivities,
            'techTroubleMap'  => $techTroubleMap,

            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);
    }
}