<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class Corrective extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        $data['dev'] = $db->table('tb_pms')
            ->where('repair', 1)
            ->get()
            ->getResult();


        $data['areas'] = $db->table('tb_DCA')
            ->select('area')
            ->distinct()
            ->where('area IS NOT NULL', null, false)
            ->where('area !=', '')
            ->orderBy('area', 'ASC')
            ->get()
            ->getResult();

        return view('admin/corrective', $data);
    }

    // =========================
    // DATATABLE SOURCE
    // =========================
    public function getData()
    {
            $db = Database::connect();

    $area  = $this->request->getGet('area');
    $start = $this->request->getGet('start');
    $end   = $this->request->getGet('end');

    $builder = $db->table('tb_DCA');

    // AREA FILTER
    if ($area) {
        $builder->where('area', $area);
    }

    // DATE FILTER
    if ($start && $end) {
        $startDate = $start . '-01 00:00:00';
        $endDate   = date('Y-m-t 23:59:59', strtotime($end . '-01'));

        $builder->where('datetime >=', $startDate)
                ->where('datetime <=', $endDate);
    }

    $data = $builder->orderBy('datetime', 'DESC')->get()->getResult();

        // format for DataTable
        $result = [];

        foreach ($data as $row) {

            $dt = strtotime($row->datetime);

            $result[] = [
                'id'             => $row->id,
                'code'           => $row->code,
                'date'           => date('Y-m-d', $dt),
                'time'           => date('H:i', $dt),
                'device'         => $row->device,
                'problem'        => $row->problem,
                'recommendation' => $row->recommendation,
                'performedby'    => $row->performedby,
                'notedby'        => $row->notedby,
            ];
        }

        return $this->response->setJSON(['data' => $result]);
    }

    // =========================
    // SAVE (AJAX VERSION)
    // =========================
   public function save()
    {
        $db = Database::connect();

        $device = $this->request->getPost('device_name');

        // GET AREA FROM tb_pms
        $areaData = $db->table('tb_pms')
            ->select('area')
            ->where('computerlabel', $device)
            ->get()
            ->getRow();

        $area = $areaData ? $areaData->area : null;

        // GET LAST CODE
        $last = $db->table('tb_DCA')
            ->select('code')
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRow();

        $num = $last ? ((int) substr($last->code, 2) + 1) : 1;
        $repair_code = 'IT' . str_pad($num, 3, '0', STR_PAD_LEFT);

        $datetime = $this->request->getPost('date') . ' ' . $this->request->getPost('time');

        $data = [
            'code'           => $repair_code,
            'datetime'       => $datetime,
            'device'         => $device,
            'area'           => $area,
            'problem'        => $this->request->getPost('problem'),
            'recommendation' => $this->request->getPost('comments'),
            'performedby'    => session()->get('name'),
            'notedby'        => $this->request->getPost('noted_by'),
        ];

        $insert = $db->table('tb_DCA')->insert($data);

        // =========================
        // UPDATE tb_pms AFTER SAVE
        // =========================
        if ($insert) {

            $db->table('tb_pms')
                ->where('computerlabel', $device)
                ->update(['repair' => 0]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Saved successfully'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to save'
        ]);
    }

    public function form()
{
    $db = Database::connect();

    $area  = $this->request->getGet('area');
    $start = $this->request->getGet('start');
    $end   = $this->request->getGet('end');

    $builder = $db->table('tb_DCA');

    // AREA FILTER
    if ($area) {
        $builder->where('area', $area);
    }

    // DATE RANGE FILTER
    if ($start && $end) {
        $startDate = $start . '-01 00:00:00';
        $endDate   = date('Y-m-t 23:59:59', strtotime($end . '-01'));

        $builder->where('datetime >=', $startDate)
                ->where('datetime <=', $endDate);
    }

    $records = $builder->orderBy('datetime', 'ASC')->get()->getResultArray();

    $rangeText = '';
    if ($start && $end) {
        $rangeText = date('F Y', strtotime($start . '-01')) .
                     ' - ' .
                     date('F Y', strtotime($end . '-01'));
    }

    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    $html = '
  <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: center; vertical-align: middle; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .top-title { text-align:center; }
        .header-table td { border:none; }
        .header-table img { height:80px; }
    </style>

   <table class="header-table" width="100%" style="border:none;">
        <tr>
            <td colspan="3" style="text-align:right;"><b>IM-007-0</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" width="90px"></td>
            <td style="text-align:center;">
                <div class="top-title">Republic of the Philippines</div>
                <div class="top-title"><strong>DEPARTMENT OF HEALTH</strong></div>
                <div class="top-title"><strong>CAGAYAN VALLEY MEDICAL CENTER</strong></div>
                <div class="top-title">Regional Tertiary, Teaching, Training, and Research Medical Center</div>
                <div class="top-title"><strong>IT EQUIPMENT AND DEVICE PREVENTIVE - CORRECTIVE ACTION FORM</strong></div>
            </td>
            <td style="text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_Logo.png" width="90px"></td>
        </tr>
        
      <tr>
            <td colspan="3" style="text-align:left;">
                Area/Location: <strong>' . ($area ? esc($area) : 'All Areas') . '</strong>
            </td>
        </tr>

        <tr>
            <td colspan="3" style="text-align:left;">
                Period: <strong>' . 
                    ($start && $end 
                        ? date('F Y', strtotime($start . '-01')) . ' - ' . date('F Y', strtotime($end . '-01'))
                        : date('F Y')
                    ) . 
                '</strong>
            </td>
        </tr>
       
    </table>

    <table>
        <thead>
            <tr>
                <th>Repair Code</th>
                <th>Date</th>
                <th>Time</th>
                <th>Device Name</th>
                <th>Problem Encounted & Action Taken</th>
                <th>Comments/Recommendation</th>
                <th>Performed By:</th>
                <th>Noted By:</th>
            </tr>
        </thead>
        <tbody>
    ';

    if (!empty($records)) {
        foreach ($records as $row) {

            $dt = strtotime($row['datetime']);

            $html .= '
            <tr>
                <td>'.$row['code'].'</td>
                <td>'.date('m/d/Y', $dt).'</td>
                <td>'.date('h:i A', $dt).'</td>
                <td>'.$row['device'].'</td>
                <td>'.$row['problem'].'</td>
                <td>'.$row['recommendation'].'</td>
                <td>'.$row['performedby'].'</td>
                <td>'.$row['notedby'].'</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="9">No data found</td></tr>';
    }

    $html .= '</tbody></table>';

    $mpdf->WriteHTML($html);

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="Repair_Report.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}



// =========================
// GET SINGLE DATA (EDIT)
// =========================
public function edit()
{
    $db = Database::connect();
    $id = $this->request->getGet('id');

    if (!$id) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Missing record ID'
        ]);
    }

    $data = $db->table('tb_DCA')
        ->where('id', $id)
        ->get()
        ->getRow();

    if (!$data) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Record not found'
        ]);
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $data
    ]);
}


// =========================
// UPDATE DATA
// =========================
public function update()
{
    $db = Database::connect();
    $id = $this->request->getPost('id');

    if (!$id) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Missing record ID'
        ]);
    }

    $device = $this->request->getPost('device_name');

    $areaData = $db->table('tb_pms')
        ->select('area')
        ->where('computerlabel', $device)
        ->get()
        ->getRow();

    $area = $areaData ? $areaData->area : null;

    $datetime = $this->request->getPost('date') . ' ' . $this->request->getPost('time');

    $data = [
        'datetime'       => $datetime,
        'device'         => $device,
        'area'           => $area,
        'problem'        => $this->request->getPost('problem'),
        'recommendation' => $this->request->getPost('comments'),
        'notedby'        => $this->request->getPost('noted_by'),
    ];

    $update = $db->table('tb_DCA')
        ->where('id', $id)
        ->update($data);

    return $this->response->setJSON([
        'status' => $update ? 'success' : 'error',
        'message' => $update ? 'Updated successfully' : 'Update failed'
    ]);
}


// =========================
// DELETE DATA
// =========================
public function delete()
{
    $db = Database::connect();
    $id = $this->request->getPost('id');

    if (!$id) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Missing record ID'
        ]);
    }

    $delete = $db->table('tb_DCA')
        ->where('id', $id)
        ->delete();

    return $this->response->setJSON([
        'status'  => $delete ? 'success' : 'error',
        'message' => $delete ? 'Deleted successfully' : 'Delete failed'
    ]);
}
}