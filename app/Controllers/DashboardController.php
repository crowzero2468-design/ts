<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;
use App\Models\Tbtrouble;
use App\Models\PersonModel;
use App\Models\AcknoModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        date_default_timezone_set('Asia/Manila');

        $personModel = new PersonModel();
        $acknoModel = new AcknoModel();

          $data['persons'] = $personModel->findAll();
          $data['acknos'] = $acknoModel->findAll();

        // ✅ ON DUTY IT (role is VARCHAR = 'admin')
        $onDutyCount = $db->table('tb_it')
            ->where('role', 'user')
            ->where('status', 'active')
            ->countAllResults();

        // ✅ ONGOING TROUBLE
        $ongoingCount = $db->table('tbtrouble')
            ->where('status', 'Ongoing')
            ->countAllResults();

        $TotalTSCount = $db->table('tbtrouble')
            ->countAllResults();

        // ✅ TODAY TROUBLES (SAFE DATE FILTER)
        $todayTroubles = $db->table('tbtrouble t')
            ->select('t.*, p.name as tech_name')
            ->join('tb_it p', 'p.id = t.person', 'left')
            ->where('t.time >=', date('Y-m-d 00:00:00'))
            ->where('t.time <=', date('Y-m-d 23:59:59'))
            ->whereIn('t.status', ['Ongoing', 'Done'])
            ->orderBy('t.time', 'DESC')
            ->get()
            ->getResultArray();

        $types = $db->table('tb_tstype')->get()->getResultArray();

        return view('admin/index', [
            'onDutyCount'   => $onDutyCount,
            'ongoingCount'  => $ongoingCount,
            'todayTroubles'=> $todayTroubles,
            'TotalTSCount'  => $TotalTSCount,
            'types'         => $types,
            'persons'       => $data['persons'],
            'acknos'        => $data['acknos'],
        ]);
    }
public function checkNewTrouble()
{
    $model = new Tbtrouble();

    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd   = date('Y-m-d 23:59:59');

    $lastId = $this->request->getGet('lastId') ?? 0;

    $newRecords = $model
        ->where('time >=', $todayStart)
        ->where('time <=', $todayEnd)
        ->where('id >', $lastId) // 🔥 THIS IS THE FIX
        ->orderBy('id', 'ASC')
        ->findAll();

    return $this->response->setJSON([
        'new' => $newRecords
    ]);
}



public function refreshTodayTable()
{
    $db = \Config\Database::connect();
    $acknoModel = new AcknoModel();
    $data['acknos'] = $acknoModel->findAll();

    $todayTroubles = $db->table('tbtrouble t')
        ->select('t.*, tech.name as tech_name, ack.id_num as ack_id_num, ack.full_name as ack_full_name')
        ->join('tb_it tech', 'tech.id = t.person', 'left')                // assigned IT
        ->join('tb_AcknowledgedBy ack', 'ack.id = t.Acknoby', 'left')    // acknowledged by
        ->where('t.time >=', date('Y-m-d 00:00:00'))
        ->where('t.time <=', date('Y-m-d 23:59:59'))
        ->whereIn('t.status', ['Ongoing', 'Done'])
        ->orderBy("FIELD(t.status, 'Ongoing', 'Done')", '', false)         // Ongoing first
        ->orderBy('t.time', 'DESC')                                        // newest inside group
        ->get()
        ->getResultArray();

    return view('admin/partials/today_table_rows', [
        'todayTroubles' => $todayTroubles,
        'acknos'        => $data['acknos'],
    ]);
}

public function refreshCounts()
{
    $db = \Config\Database::connect();

    $onDutyCount = $db->table('tb_it')
        ->where('role', 'user')
        ->where('status', 'active')
        ->countAllResults();


    $TotalTSCount = $db->table('tbtrouble')
        ->countAllResults();

    $ongoingCount = $db->table('tbtrouble')
        ->where('status', 'Ongoing')
        ->countAllResults();

    return $this->response->setJSON([
        'onDuty'   => $onDutyCount,
        'totalTS'  => $TotalTSCount,
        'ongoing'  => $ongoingCount,
    ]);
}


}
