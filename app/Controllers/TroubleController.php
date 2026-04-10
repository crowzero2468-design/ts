<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;
use App\Models\Tbtrouble;
use App\Models\PersonModel;

class TroubleController extends BaseController
{



   public function saveResponse()
{
    $db = \Config\Database::connect();

    $personnels = $this->request->getPost('person_id');

    $loggedUser = session()->get('name'); // 🔥 get logged-in user name

    $baseData = [
        'name'        => strtoupper($this->request->getPost('name')),
        'location'    => $this->request->getPost('location'),
        'ts_type'     => $this->request->getPost('ts_type'),
        'description' => strtoupper($this->request->getPost('description')),
        'status'      => 'Ongoing',
        'time'        => date('Y-m-d H:i:s'),
        'personnel'  => $loggedUser, // ✅ save who created it
    ];

    if (
        empty($baseData['name']) ||
        empty($baseData['location']) ||
        empty($baseData['description']) ||
        empty($personnels)
    ) {
        return redirect()->back()->with('error', 'Please complete all required fields');
    }

    foreach ($personnels as $techId) {

        $data = $baseData;
        $data['person'] = $techId;

        $db->table('tbtrouble')->insert($data);
    }

    return redirect()->back()->with('success', 'Troubleshoot saved successfully');
}


public function markDone()
{
    $id = $this->request->getPost('id');

    $tbTrouble = new Tbtrouble();
    $row = $tbTrouble->find($id);

    if (!$row) {
        return redirect()->back()->with('error', 'Record not found');
    }

    // ❌ BLOCK if not started
    if (empty($row['time_started'])) {
        return redirect()->back()->with('error', 'Cannot mark as done. Start the task first.');
    }

    // ✅ Proceed
    $tbTrouble->update($id, [
        'status' => 'Done',
        'remarks' => $this->request->getPost('remarks'),
        'completion_time' => date('Y-m-d H:i:s')
    ]);

    return redirect()->back()->with('success', 'Marked as done');
}

public function endorse()
{
    $db = \Config\Database::connect();

    $id     = $this->request->getPost('id');
    $person = $this->request->getPost('person_id'); // single ID

    $db->table('tbtrouble')
       ->where('id', $id)
       ->update([
           'status' => 'Ongoing',
           'person' => $person
       ]);

    return redirect()->back()->with('success', 'Troubleshoot endorsed successfully.');
}

public function startNow()
{
    $tbTrouble = new Tbtrouble();

    $id = $this->request->getPost('id');

    if (!$id) {
        return redirect()->back()->with('error', 'Invalid ID');
    }

    $tbTrouble->update($id, [
        'time_started' => date('Y-m-d H:i:s'),
    ]);

    return redirect()->back()->with('success', 'Work started');
}

public function saveAck()
{
    $db = \Config\Database::connect();

    $troubleId = $this->request->getPost('id');          // trouble record ID
    $idNum = trim($this->request->getPost('id_num'));    // person ID number
    $fullName = trim($this->request->getPost('full_name')); // person full name

    if (!$troubleId || !$idNum || !$fullName) {
        return redirect()->back()->with('error', 'Invalid data');
    }

    $ackTable = $db->table('tb_AcknowledgedBy');

    // Check if person already exists by id_num
    $existing = $ackTable->where('id_num', $idNum)->get()->getRowArray();

    if ($existing) {
        $ackId = $existing['id']; // use existing primary id
    } else {
        // Insert new person
        $ackTable->insert([
            'id_num' => $idNum,
            'full_name' => $fullName
        ]);
        $ackId = $db->insertID();
    }

    // Update tbtrouble with the Acknoby ID
    $tbTrouble = new Tbtrouble();
    $tbTrouble->update($troubleId, [
        'Acknoby' => $ackId
    ]);

    return redirect()->back()->with('success', 'Acknowledged saved successfully');
}
}
