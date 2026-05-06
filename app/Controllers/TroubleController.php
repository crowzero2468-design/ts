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
    $rating    = (int) $this->request->getPost('rating'); 

    // Basic validation
    if (!$troubleId || !$idNum || !$fullName) {
        return redirect()->back()->with('error', 'Invalid data');
    }

    $ackTable     = $db->table('tb_AcknowledgedBy');
    $remarksTable = $db->table('tb_AcknowledgedByRemarks');
    $rateTable    = $db->table('tb_rate'); // ⭐ NEW

    $rateColumns = array_column($db->query("SHOW COLUMNS FROM tb_rate")->getResultArray(), 'Field');
    $rateTroubleColumn = in_array('trouble_id', $rateColumns) ? 'trouble_id' : 'arta_id';
    $rateValueColumn = in_array('rating', $rateColumns) ? 'rating' : 'rate';
    $rateUserColumn = in_array('user_id', $rateColumns) ? 'user_id' : (in_array('IT_name', $rateColumns) ? 'IT_name' : null);
    $rateDateColumn = in_array('rateddate', $rateColumns) ? 'rateddate' : (in_array('rated_date', $rateColumns) ? 'rated_date' : null);
    $rateInsertDefaults = [];

    $trouble = $db->table('tbtrouble')->select('person')->where('id', $troubleId)->get()->getRowArray();
    $ratePersonId = $trouble['person'] ?? session()->get('user_id') ?? session()->get('id') ?? 0;
    $rateDateValue = date('Y-m-d');

    if (in_array('comment', $rateColumns)) {
        $rateInsertDefaults['comment'] = $remarks ?: 'Rating';
    }

    if ($rateUserColumn) {
        $rateInsertDefaults[$rateUserColumn] = $ratePersonId;
    }

    if ($rateDateColumn) {
        $rateInsertDefaults[$rateDateColumn] = $rateDateValue;
    }

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
        $remarksTable
            ->where('id', $existingRemark['id'])
            ->update([
                'remarks' => $remarks
            ]);
    } else {
        $remarksTable->insert([
            'id_ack'     => $ackId,
            'trouble_id' => $troubleId,
            'remarks'    => $remarks
        ]);
    }


    if ($rating > 0) {

        $existingRate = $rateTable
            ->where($rateTroubleColumn, $troubleId)
            ->get()
            ->getRowArray();

        $rateUpdateData = [$rateValueColumn => $rating];
        if ($rateUserColumn) {
            $rateUpdateData[$rateUserColumn] = $ratePersonId;
        }
        if ($rateDateColumn) {
            $rateUpdateData[$rateDateColumn] = $rateDateValue;
        }

        if ($existingRate) {
            // Update existing rating
            $rateTable
                ->where('id', $existingRate['id'])
                ->update($rateUpdateData);
        } else {
            // Insert new rating
            $rateData = array_merge([
                $rateTroubleColumn => $troubleId,
                $rateValueColumn => $rating
            ], $rateInsertDefaults);

            $rateTable->insert($rateData);
        }
    }


    $tbTrouble = new Tbtrouble();
    $tbTrouble->update($troubleId, [
        'Acknoby' => $ackId
    ]);

    return redirect()->back()->with('success', 'Acknowledged + Rating saved successfully');
}
}
