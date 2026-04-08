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

        // ====== Average Server Temperature ======
        $avgTemp = $db->table('tb_temp')
            ->selectAvg('temp')
            ->get()
            ->getRow();
        $avgTempValue = $avgTemp ? round($avgTemp->temp, 2) : 0;

        // ====== Trouble Data for Line Chart ======
        $troubles = $db->table('tbtrouble')
            ->select('ts_type, COUNT(*) as total')
            ->groupBy('ts_type')
            ->get()
            ->getResult();

        $labels = [];
        $troubleData = [];
        foreach ($troubles as $row) {
            $labels[] = $row->ts_type;
            $troubleData[] = (int)$row->total;
        }

        // In Dashboard2Controller
        $totalTroubleshoots = $db->table('tbtrouble')->countAllResults();
        // Total IT Equipment Inspected
        $totalInspected = $db->table('tb_tools')->countAllResults();
        // ====== First Trouble Date ======
        $firstTrouble = $db->table('tbtrouble')
            ->select('time')
            ->orderBy('time', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        $startDate = $firstTrouble ? date('M d, Y', strtotime($firstTrouble->time)) : date('M d');
        $endDate   = date('M d, Y'); // today

        // Total ALL users (Admin + Technician)
        $totalUsers = $db->table('tb_it')
            ->countAllResults();

        // Total Admin
        $totalAdmin = $db->table('tb_it')
            ->where('role', '3') 
            ->countAllResults();


        // ====== Pass all data to the view ======
        $data = [
            'totalTech'   => $totalTech,
            'offDuty'     => $offDuty,
            'avgPing'     => $avgPingValue,
            'avgTemp'     => $avgTempValue,
            'totalTroubleshoots' => $totalTroubleshoots,
            'totalInspected' => $totalInspected,
            'totalUsers' => $totalUsers,
            'totalAdmin' => $totalAdmin,
            'troubleLabels' => $labels,
            'troubleData'   => $troubleData,
            'startDate'     => $startDate,
            'endDate'       => $endDate
        ];

        return view('admin/dashboard', $data);
    }
}