<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ServerchecklistModel;
use Mpdf\Mpdf;

class ServerchecklistController extends BaseController
{
    protected $serverchecklistModel;

    public function __construct()
    {
        $this->serverchecklistModel = new ServerchecklistModel();
    }

    public function index()
    {
        return view('admin/server_checklist');
    }

    public function fetchData()
{
    $start_date  = $this->request->getGet('start_date');
    $end_date    = $this->request->getGet('end_date');
    $servername  = $this->request->getGet('servername');
    $checked_by  = $this->request->getGet('checked_by');

    $query = $this->serverchecklistModel;

    // ✅ Proper date range filtering (NO DATE() function)
    if (!empty($start_date) && !empty($end_date)) {
        $query = $query->where('datetime >=', $start_date . ' 00:00:00')
                       ->where('datetime <=', $end_date . ' 23:59:59');
    } elseif (!empty($start_date)) {
        $query = $query->where('datetime >=', $start_date . ' 00:00:00');
    } elseif (!empty($end_date)) {
        $query = $query->where('datetime <=', $end_date . ' 23:59:59');
    }

    if (!empty($servername)) {
        $query = $query->like('servername', $servername);
    }

    if (!empty($checked_by)) {
        $query = $query->like('checked_by', $checked_by);
    }

    $data = $query->orderBy('id', 'DESC')->findAll();

    $formattedData = [];

    foreach ($data as $row) {

        $datetime = !empty($row['datetime']) ? strtotime($row['datetime']) : null;

        $formattedData[] = [
            'id'         => $row['id'],
            'date'       => $datetime ? date('Y-m-d', $datetime) : '',
            'time'       => $datetime ? date('h:i A', $datetime) : '',
            'servername' => $row['servername'],
            'checkpoint' => $row['checkpoint'],
            'problem'    => $row['problem'],
            'corrective' => $row['corrective'],
            'checked_by' => $row['checked_by'],

            'datetime_local' => $datetime ? date('Y-m-d\TH:i', $datetime) : '',
        ];
    }

    return $this->response->setJSON([
        'data' => $formattedData
    ]);
}

    public function add()
    {
        $datetime    = $this->request->getPost('datetime');
        $servername  = $this->request->getPost('servername');
        $checkpoint  = $this->request->getPost('checkpoint');
        $problem     = $this->request->getPost('problem');
        $corrective  = $this->request->getPost('corrective');
        $checked_by  = $this->request->getPost('checked_by');

        $data = [
            'datetime'   => date('Y-m-d H:i:s', strtotime($datetime)),
            'servername' => $servername,
            'checkpoint' => $checkpoint,
            'problem'    => $problem,
            'corrective' => $corrective,
            'checked_by' => $checked_by
        ];

        $insert = $this->serverchecklistModel->insert($data);

        return $this->response->setJSON([
            'success' => (bool)$insert,
            'message' => $insert ? 'Record saved successfully' : 'Failed to save record'
        ]);
    }

    public function getEdit()
{
    $id = $this->request->getGet('id');

    if (empty($id)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'ID is required'
        ])->setStatusCode(400);
    }

    $data = $this->serverchecklistModel->find($id);

    if (!$data) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Record not found'
        ])->setStatusCode(404);
    }

    // Safe datetime conversion
    $datetimeLocal = '';
    if (!empty($data['datetime'])) {
        $timestamp = strtotime((string) $data['datetime']);
        $datetimeLocal = $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
    }

    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'id' => $data['id'] ?? '',
            'datetime_local' => $datetimeLocal,
            'servername' => $data['servername'] ?? '',
            'checkpoint' => $data['checkpoint'] ?? '',
            'problem' => $data['problem'] ?? '',
            'corrective' => $data['corrective'] ?? '',
            'checked_by' => $data['checked_by'] ?? ''
        ]
    ]);
}


    public function update()
    {
        $id          = $this->request->getPost('id');
        $datetime    = $this->request->getPost('datetime');
        $servername  = $this->request->getPost('servername');
        $checkpoint  = $this->request->getPost('checkpoint');
        $problem     = $this->request->getPost('problem');
        $corrective  = $this->request->getPost('corrective');
        $checked_by  = $this->request->getPost('checked_by');

        $data = [
            'datetime'   => date('Y-m-d H:i:s', strtotime($datetime)),
            'servername' => $servername,
            'checkpoint' => $checkpoint,
            'problem'    => $problem,
            'corrective' => $corrective,
            'checked_by' => $checked_by
        ];

        $update = $this->serverchecklistModel->update($id, $data);

        return $this->response->setJSON([
            'success' => (bool)$update,
            'message' => $update ? 'Record updated successfully' : 'Failed to update record'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        $delete = $this->serverchecklistModel->delete($id);

        return $this->response->setJSON([
            'success' => (bool)$delete,
            'message' => $delete ? 'Record deleted successfully' : 'Failed to delete record'
        ]);
    }

    public function viewForm()
    {
        // Get optional filters from GET parameters
        $date = $this->request->getGet('date');
        $servername = $this->request->getGet('servername');
        $checked_by = $this->request->getGet('checked_by');

        $builder = $this->serverchecklistModel;

        if ($date) $builder = $builder->where('DATE(datetime)', $date, false);
        if ($servername) $builder = $builder->like('servername', $servername);
        if ($checked_by) $builder = $builder->like('checked_by', $checked_by);

        $records = $builder->orderBy('datetime', 'DESC')->findAll();

        // ====================== PDF PART ======================
        $mpdf = new Mpdf([
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 60,
            'margin_bottom' => 10,
        ]);

        // Header
        $header = '
        <table width="100%" style="border:none;">
            <tr>
                <td colspan="3" style="text-align:right; border:none;">
                                        <b>IM-018-0</b><br>
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
                    Dalan na Pagayaya, Carig Sur, Tuguegarao City, Cagayan<br>
                    <strong>SERVER MANAGEMENT CHECKLIST</strong><br>
                </td>
                <td style="border:none; text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_Logo.png" height="80"></td>
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
                    <td>' . esc($row['servername']) . '</td>
                    <td>' . esc($row['checkpoint']) . '</td>
                    <td>' . esc($row['problem']) . '</td>
                    <td>' . esc($row['corrective']) . '</td>
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
                    <th>Date & Time</th>
                    <th>Server Name</th>
                    <th>Checkpoint</th>
                    <th>Problem</th>
                    <th>Corrective</th>
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
            ->setHeader('Content-Disposition', 'inline; filename="ServerChecklist_Report.pdf"')
            ->setBody($mpdf->Output('', 'S'));
    }
}