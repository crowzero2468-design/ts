<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;
use App\Models\UserModel;

class TechController extends BaseController
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
        // Run scheduler only if override OFF
        if (session()->get('manual_override') != 1) {
            $this->updateDutyStatus();
        }

        $data['records'] = $this->db
            ->table($this->table)
            ->whereIn('status', ['active', 'inactive'])
            ->orderBy('status', 'ASC')
            ->get()
            ->getResultArray();

        $data['schedules'] = $this->db->table('tb_schedule s')
            ->select('s.schedule_date, s.start_time, s.end_time, t.name, t.location')
            ->join('tb_it t', 't.id = s.tech_id')
            ->get()
            ->getResultArray();

        // send override state to view
        $data['override'] = session()->get('manual_override') ?? 0;

        return view('admin/tech', $data);
    }

    /* =========================
       TOGGLE STATUS (MANUAL)
    ========================== */
    public function toggleStatus($id)
    {
        $builder = $this->db->table($this->table);

        $user = $builder->where('id', $id)->get()->getRow();

        if (!$user) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $newStatus = ($user->status === 'active') ? 'inactive' : 'active';

        $builder->where('id', $id)->update([
            'status' => $newStatus,
            'manual_override' => 1
        ]);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /* =========================
       UPDATE RECORD
    ========================== */
    public function update($id)
    {
        $builder = $this->db->table($this->table);

        $record = $builder->where('id', $id)->get()->getRow();

        if (!$record) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'location' => $this->request->getPost('location'),
            'role'     => $this->request->getPost('role'),
            'status'   => $this->request->getPost('status'),
        ];

        $password = $this->request->getPost('password');

        if (!empty($password)) {
            $data['pass'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $builder->where('id', $id)->update($data);

        return redirect()->back()->with('success', 'Record updated successfully.');
    }

    /* =========================
       DELETE RECORD
    ========================== */
    public function delete($id)
    {
        $builder = $this->db->table($this->table);

        $record = $builder->where('id', $id)->get()->getRow();

        if (!$record) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $builder->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Record deleted successfully.');
    }

    /* =========================
       AUTO DUTY SCHEDULER
    ========================== */
    private function updateDutyStatus()
    {
        // stop scheduler if manual override ON
        if (session()->get('manual_override') == 1) {
            return;
        }

        $db = \Config\Database::connect();

        date_default_timezone_set('Asia/Manila');

        $nowDate = date('Y-m-d');
        $nowTime = date('H:i:s');

        // Set everyone inactive first (only automatic techs)
        $db->table('tb_it')
            ->where('manual_override', 0)
            ->update(['status' => 'inactive']);

        // Get technicians scheduled today
        $schedules = $db->table('tb_schedule')
            ->where('schedule_date', $nowDate)
            ->get()
            ->getResult();

        foreach ($schedules as $schedule) {

            // activate 30 minutes before shift
            $start = date('H:i:s', strtotime('-30 minutes', strtotime($schedule->start_time)));
            $end   = $schedule->end_time;

            $isOnDuty = false;

            // Normal shift
            if ($start < $end) {
                if ($nowTime >= $start && $nowTime <= $end) {
                    $isOnDuty = true;
                }
            }
            // Night shift
            else {
                if ($nowTime >= $start || $nowTime <= $end) {
                    $isOnDuty = true;
                }
            }

            if ($isOnDuty) {
                $db->table('tb_it')
                    ->where('id', $schedule->tech_id)
                    ->where('manual_override', 0)
                    ->update(['status' => 'active']);
            }
        }
    }

    /* =========================
       ADD TECHNICIAN
    ========================== */
    public function store()
    {
        $userModel = new UserModel();

        $data = [
            'name'     => $this->request->getPost('fullname'),
            'uname'    => $this->request->getPost('username'),
            'pass'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'location' => $this->request->getPost('location'),
            'status'   => 'active',
            'role'     => 'admin',
            'stat'     => '',
            'img'      => ''
        ];

        $userModel->insert($data);

        return redirect()->back()->with('success', 'Technician added successfully');
    }

    /* =========================
       GLOBAL SCHEDULER SWITCH
    ========================== */
    public function setOverride($status)
{
    session()->set('manual_override', $status);

    // If switching back to automatic scheduler
    if ($status == 0) {

        // Reset all manual overrides
        $this->db->table('tb_it')->update([
            'manual_override' => 0
        ]);

    }

    return redirect()->back()->with('success', 'Scheduler mode updated');
}


}