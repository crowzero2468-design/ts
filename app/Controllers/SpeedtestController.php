<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SpeedtestModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Mpdf\Mpdf;

class SpeedtestController extends BaseController
{
    public function index()
    {
        return view('admin/speedtest');
    }

    public function fetchData()
    {
        $request = \Config\Services::request();
        $model = new SpeedtestModel();
        
        $filterDate = $request->getGet('date');
        $filterNode = $request->getGet('node');
        $filterBy = $request->getGet('checked_by');
        $filterLocation = $request->getGet('location'); 

        $builder = $model->builder();

        if($filterDate) $builder->where('DATE(datetime)', $filterDate);
        if($filterNode) $builder->like('node', $filterNode);
        if($filterBy) $builder->like('checked_by', $filterBy);
        if($filterLocation) $builder->like('location', $filterLocation); 

        $builder->where('status', 'A'); // only active records

        $total = $builder->countAllResults(false);

        $data = $builder->get()->getResultArray();
        $result = [];
        foreach($data as $row){
            $result[] = [
                'id' => $row['id'], 
                'date' => date('F j, Y', strtotime($row['datetime'])),
                'time' => date('h:i A', strtotime($row['datetime'])),
                'datetime_raw' => date('Y-m-d\TH:i', strtotime($row['datetime'])),
                'node' => $row['node'],
                'location' => $row['location'],
                'ping' => $row['ping'],
                'download' => $row['download'],
                'upload' => $row['upload'],
                'checked_by' => $row['checked_by'],
                'remarks' => $row['remarks']
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($request->getGet('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result
        ]);
    }

    public function add()
    {
        $model = new SpeedtestModel();
        
        $insertData = [
            'datetime'   => $this->request->getPost('datetime'),
            'node'       => $this->request->getPost('node'),
            'location'   => $this->request->getPost('location'),
            'ping'       => $this->request->getPost('ping'),
            'download'   => $this->request->getPost('download'),
            'upload'     => $this->request->getPost('upload'),
            'checked_by' => $this->request->getPost('checked_by'),
            'status'     => 'A',
            'remarks'    => $this->request->getPost('remarks')
        ];

        $model->insert($insertData);

        return $this->response->setJSON(['status' => 'success']);
    }

public function get($id)
{
    $model = new SpeedtestModel();
    $row = $model->find($id);

    if (!$row) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Record not found']);
    }

    // ✅ Make sure datetime exists and is valid
    if (isset($row['datetime']) && !empty($row['datetime'])) {

    try {
        // ✅ handle array OR object
        $datetime = is_array($row) ? ($row['datetime'] ?? null) : ($row->datetime ?? null);

        if ($datetime) {
            $dt = new \DateTime($datetime);
            $row['datetime_raw'] = $dt->format('Y-m-d\TH:i');
        } else {
            $row['datetime_raw'] = '';
        }

    } catch (\Exception $e) {
        $row['datetime_raw'] = '';
    }

    } else {
        $row['datetime_raw'] = '';
    }

    return $this->response->setJSON($row);
}

    public function update()
    {
        $model = new SpeedtestModel();
        $id = $this->request->getPost('id');

        $updateData = [
            'datetime'   => $this->request->getPost('datetime'),
            'node'       => $this->request->getPost('node'),
            'location'   => $this->request->getPost('location'),
            'ping'       => $this->request->getPost('ping'),
            'download'   => $this->request->getPost('download'),
            'upload'     => $this->request->getPost('upload'),
            'checked_by' => $this->request->getPost('checked_by')
        ];

        $model->update($id, $updateData);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function delete()
    {
        $model = new SpeedtestModel();
        $id = $this->request->getPost('id');

        // Soft delete: mark as Inactive
        $model->update($id, ['status' => 'I']);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function viewForm()
{
    $model = new \App\Models\SpeedtestModel();

    // Get optional filters from GET parameters
    $date = $this->request->getGet('date');
    $node = $this->request->getGet('node');
    $checked_by = $this->request->getGet('checked_by');

    $builder = $model;

    if ($date) $builder = $builder->where('DATE(datetime)', $date);
    if ($node) $builder = $builder->like('node', $node);
    if ($checked_by) $builder = $builder->like('checked_by', $checked_by);

    $builder = $builder->where('status', 'A');
    $records = $builder->orderBy('datetime', 'DESC')->findAll();

    // ====================== PDF PART ======================
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 55,
        'margin_bottom' => 10,
    ]);

    // Header
    $header = '
    <table width="100%" style="border:none;">
        <tr>
            <td colspan="3" style="text-align:right; border:none;">
                                    <b>IM-019-0</b><br>
                                    <small>28March2024</small>
                                </td>
        </tr>
        <tr>
            <td style="border:none;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" height="80"></td>
            <td style="border:none; text-align:center;">
                Republic of the Philippines<br>
                <strong>DEPARTMENT OF HEALTH</strong><br>
                <strong>CAGAYAN VALLEY MEDICAL CENTER</strong><br>
                Regional Tertiary, Teaching, Training, and Research Medical Center<br>
                <strong>DAILY BANDWIDTH SPEED CHECK</strong><br>
            </td>
            <td style="border:none; text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_Logo.png" height="80"></td>
        </tr>
        <tr>
            <td colspan="3" style="border:none; text-align:left;">
                <b>As of:</b> ' . date('F, Y') . '
            </td>
        </tr>
    </table>
    ';
    $mpdf->SetHTMLHeader($header);

    // Table rows
    $tableRows = '';
    if (!empty($records)) {
        foreach ($records as $row) {
            $tableRows .= '
            <tr>
                <td>' . date('F j, Y - h:i A', strtotime($row['datetime'])) . '</td>
                <td>' . esc($row['node']) . '</td>
                <td>' . esc($row['ping']) . '</td>
                <td>' . esc($row['download']) . '</td>
                <td>' . esc($row['upload']) . '</td>
                <td>' . esc($row['checked_by']) . '</td>
            </tr>
            ';
        }
    } else {
        $tableRows = '<tr><td colspan="6">No data found</td></tr>';
    }

    // HTML content
    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: center; vertical-align: middle; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>

    <table>
        <thead>
            <tr>
                <th>Time and Date</th>
                <th>Node</th>
                <th>Ping (ms)</th>
                <th>Download (Mbps)</th>
                <th>Upload (Mbps)</th>
                <th>Checked By</th>
            </tr>
        </thead>
        <tbody>
            ' . $tableRows . '
        </tbody>
    </table>
    ';

    $mpdf->WriteHTML($html);

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="Speedtest_Report.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}


     public function importExcel()
    {
        $file = $this->request->getFile('excel_file');

        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->getTempName();

            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = [];

            // Get logged-in user
            $loggedUser = session()->get('name') ?? '';

            foreach ($sheet->getRowIterator(2) as $row) { // skip header
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Convert Excel datetime to MySQL DATETIME
                $excelDate = $rowData[0];
                if (is_numeric($excelDate)) {
                    $dateTime = Date::excelToDateTimeObject($excelDate)->format('Y-m-d H:i:s');
                } else {
                    $dateTime = date('Y-m-d H:i:s', strtotime($excelDate));
                }

                $data[] = [
                    'datetime'    => $dateTime,
                    'node'        => $rowData[1], // B
                    'ping'        => $rowData[2], // C
                    'download'    => $rowData[4], // E
                    'upload'      => $rowData[5], // F
                    'location'    => $rowData[8], // I
                    'status'      => 'A',         // fixed
                    //'checked_by'  =>  $loggedUser,
                    'checked_by'  => $rowData[9],
                    'remarks'  =>  $rowData[10],
                ];
            }

            $model = new SpeedtestModel();
            $model->insertBatch($data);

            return redirect()->back()->with('success', 'Excel imported successfully.');
        }

        return redirect()->back()->with('error', 'Invalid file.');
    }

}