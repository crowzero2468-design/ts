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
        'personnel'  => $loggedUser, 
    ];

    if (
        empty($baseData['name']) ||
        empty($baseData['location']) ||
        empty($baseData['description']) 
        // empty($personnels)
    ) {
        return redirect()->back()->with('error', 'Please complete all required fields');
    }

    // ✅ CASE 1: If personnel selected → loop insert
    if (!empty($personnels)) {
        foreach ($personnels as $techId) {
            $data = $baseData;
            $data['person'] = $techId;

            $db->table('tbtrouble')->insert($data);
        }
    } 
    // ✅ CASE 2: No personnel selected → insert once with NULL
    else {
        $data = $baseData;
        $data['person'] = null; // or 0 if your DB requires

        $db->table('tbtrouble')->insert($data);
    }

    return redirect()->back()->with('success', 'Troubleshoot saved successfully');
}


public function markDone()
{
    $troubleModel = new Tbtrouble();

    $id = $this->request->getPost('id');

    $file = $this->request->getFile('proof_image');

    $data = [
        'remarks' => $this->request->getPost('remarks'),
        'status'  => 'Done',
	    'completion_time' => date('Y-m-d H:i:s')
    ];

    // only update image if file is uploaded
    if ($file && $file->isValid() && !$file->hasMoved() && $file->getName() !== '') {

        $imageName = $file->getRandomName();
        $file->move(FCPATH . 'assets/img/uploads', $imageName);

        $data['image'] = $imageName;
    }

    $troubleModel->update($id, $data);

    return redirect()->back()->with('success', 'Marked as done successfully.');
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

    $troubleId = $this->request->getPost('id');
    $idNum     = trim($this->request->getPost('id_num'));
    $fullName  = trim($this->request->getPost('full_name'));
    $remarks   = trim($this->request->getPost('remarks'));

    // Basic validation
    if (!$troubleId || !$idNum || !$fullName) {
        return redirect()->back()->with('error', 'Invalid data');
    }

    $ackTable     = $db->table('tb_AcknowledgedBy');
    $remarksTable = $db->table('tb_AcknowledgedByRemarks');

    // Check if person already exists
    $existing = $ackTable->where('id_num', $idNum)->get()->getRowArray();

    if ($existing) {
        $ackId = $existing['id'];

        // Optional: update full name if changed
        if ($existing['full_name'] !== $fullName) {
            $ackTable->where('id', $ackId)->update([
                'full_name' => $fullName
            ]);
        }

    } else {
        // Insert new person
        $ackTable->insert([
            'id_num'    => $idNum,
            'full_name' => $fullName,
        ]);

        $ackId = $db->insertID();
    }

    // ✅ Prevent duplicate remarks for same trouble + person
    $existingRemark = $remarksTable
        ->where('id_ack', $ackId)
        ->where('trouble_id', $troubleId)
        ->get()
        ->getRowArray();

    if ($existingRemark) {
        // Update existing remark instead of inserting duplicate
        $remarksTable
            ->where('id', $existingRemark['id'])
            ->update([
                'remarks' => $remarks
            ]);
    } else {
        // Insert new remark
        $remarksTable->insert([
            'id_ack'     => $ackId,
            'trouble_id' => $troubleId,
            'remarks'    => $remarks
        ]);
    }

    // Update tbtrouble with the Acknoby ID
    $tbTrouble = new Tbtrouble();
    $tbTrouble->update($troubleId, [
        'Acknoby' => $ackId
    ]);

    return redirect()->back()->with('success', 'Acknowledged saved successfully');
}
}
