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
        $acknoModel  = new AcknoModel();

        $data['persons'] = $personModel->findAll();
        $data['acknos']  = $acknoModel->findAll();

        // Counts
        $onDutyCount = $db->table('tb_it')
            ->where('status', 'active')
            ->whereIn('role', ['user','admin'])
            ->countAllResults();

        $ongoingCount = $db->table('tbtrouble')
            ->where('status', 'Ongoing')
            ->countAllResults();

        $TotalTSCount = $db->table('tbtrouble')
            ->countAllResults();

        // ✅ WITH REMARKS JOIN
        $todayTroubles = $db->table('tbtrouble t')
            ->select('
                t.*, 
                p.name as tech_name,
                ack.id_num as ack_id_num,
                ack.full_name as ack_full_name,
                r.remarks as ack_remarks
            ')
            ->join('tb_it p', 'p.id = t.person', 'left')
            ->join('tb_AcknowledgedBy ack', 'ack.id = t.Acknoby', 'left')
            ->join('tb_AcknowledgedByRemarks r', 'r.id_ack = ack.id AND r.trouble_id = t.id', 'left')
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
            'todayTroubles' => $todayTroubles,
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
            ->where('id >', $lastId)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'new' => $newRecords
        ]);
    }

    public function refreshTodayTable()
    {
        $db = Database::connect();
        $acknoModel = new AcknoModel();

        $data['acknos'] = $acknoModel->findAll();

        // ✅ WITH REMARKS JOIN
        $todayTroubles = $db->table('tbtrouble t')
            ->select('
                t.*, 
                tech.name as tech_name, 
                ack.id_num as ack_id_num, 
                ack.full_name as ack_full_name,
                r.remarks as ack_remarks
            ')
            ->join('tb_it tech', 'tech.id = t.person', 'left')
            ->join('tb_AcknowledgedBy ack', 'ack.id = t.Acknoby', 'left')
            ->join('tb_AcknowledgedByRemarks r', 'r.id_ack = ack.id AND r.trouble_id = t.id', 'left')
            ->where('t.time >=', date('Y-m-d 00:00:00'))
            ->where('t.time <=', date('Y-m-d 23:59:59'))
            ->whereIn('t.status', ['Ongoing', 'Done'])
            ->orderBy("FIELD(t.status, 'Ongoing', 'Done')", '', false)
            ->orderBy('t.time', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/partials/today_table_rows', [
            'todayTroubles' => $todayTroubles,
            'acknos'        => $data['acknos'],
        ]);
    }

    public function refreshCounts()
    {
        $db = Database::connect();

        $onDutyCount = $db->table('tb_it')
            ->where('status', 'active')
            ->whereIn('role', ['user','admin'])
            ->countAllResults();

        $TotalTSCount = $db->table('tbtrouble')
            ->countAllResults();

        $ongoingCount = $db->table('tbtrouble')
            ->where('status', 'Ongoing')
            ->countAllResults();

        return $this->response->setJSON([
            'onDuty'  => $onDutyCount,
            'totalTS' => $TotalTSCount,
            'ongoing' => $ongoingCount,
        ]);
    }
}