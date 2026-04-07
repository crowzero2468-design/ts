<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use App\Models\AcknoModel;

class OngoingController extends BaseController
{

    public function index()
    {
        $db = Database::connect();
        $acknoModel = new AcknoModel();
        $data['acknos'] = $acknoModel->findAll();

        // ✅ ON DUTY IT (role is VARCHAR = 'admin')
        $onDutyCount = $db->table('tb_it')
            ->where('role', 'user')
            ->where('stat', 'available')
            ->countAllResults();

        // ✅ ONGOING TROUBLE
        $ongoingCount = $db->table('tbtrouble')
            ->where('status', 'Ongoing')
            ->countAllResults();

        $TotalTSCount = $db->table('tbtrouble')
            ->countAllResults();

        $todayTroubles = $db->table('tbtrouble t')
            ->select('t.*, p.name as tech_name')
            ->join('tb_it p', 'p.id = t.person', 'left')
            ->where('t.status', 'Ongoing')
            ->orderBy('t.time', 'DESC')
            ->get()
            ->getResultArray();


        $types = $db->table('tb_tstype')->get()->getResultArray();

        return view('admin/ongoing', [
            'onDutyCount'   => $onDutyCount,
            'ongoingCount'  => $ongoingCount,
            'todayTroubles'=> $todayTroubles,
            'TotalTSCount'  => $TotalTSCount,
            'types'         => $types,
            'acknos'        => $data['acknos']
        ]);
    }
    public function delete()
{
    $id = $this->request->getPost('id');

    $db = \Config\Database::connect();

    $db->table('tbtrouble')->delete(['id' => $id]);

    return redirect()->back()->with('success', 'Record deleted successfully.');
}

}
