<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ServerinventoryModel;
use Mpdf\Mpdf;

class ServerinventoryController extends BaseController
{
    protected $serverinventoryModel;

    public function __construct()
    {
        $this->serverinventoryModel = new ServerinventoryModel();
    }

    public function index()
    {
        return view('admin/server_inventory');
    }

    public function fetchData()
    {
        $acquisition = $this->request->getGet('acquisition');
        $type        = $this->request->getGet('type');
        $model       = $this->request->getGet('model');
        $server_name = $this->request->getGet('server_name');

        $query = $this->serverinventoryModel;

        if (!empty($acquisition)) {
            $query = $query->like('acquisition', $acquisition);
        }

        if (!empty($type)) {
            $query = $query->like('type', $type);
        }

        if (!empty($model)) {
            $query = $query->like('model', $model);
        }

        if (!empty($server_name)) {
            $query = $query->like('server_name', $server_name);
        }

        $data = $query->orderBy('id', 'DESC')->findAll();

        $formattedData = [];

        foreach ($data as $row) {
            $formattedData[] = [
                'id'          => $row['id'],
                'acquisition' => $row['acquisition'],
                'type'        => $row['type'],
                'model'       => $row['model'],
                'processor'   => $row['processor'],
                'memory'      => $row['memory'],
                'OS'          => $row['OS'],
                'server_name'=> $row['server_name'],
            ];
        }

        return $this->response->setJSON([
            'data' => $formattedData
        ]);
    }

    public function add()
    {
        $data = [
            'acquisition' => $this->request->getPost('acquisition'),
            'type'        => $this->request->getPost('type'),
            'model'       => $this->request->getPost('model'),
            'processor'   => $this->request->getPost('processor'),
            'memory'      => $this->request->getPost('memory'),
            'OS'          => $this->request->getPost('OS'),
            'server_name' => $this->request->getPost('server_name')
        ];

        $insert = $this->serverinventoryModel->insert($data);

        return $this->response->setJSON([
            'success' => $insert ? true : false,
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

        $data = $this->serverinventoryModel->find($id);

        if (!$data) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Record not found'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');

        $data = [
            'acquisition' => $this->request->getPost('acquisition'),
            'type'        => $this->request->getPost('type'),
            'model'       => $this->request->getPost('model'),
            'processor'   => $this->request->getPost('processor'),
            'memory'      => $this->request->getPost('memory'),
            'OS'          => $this->request->getPost('OS'),
            'server_name'=> $this->request->getPost('server_name')
        ];

        $update = $this->serverinventoryModel->update($id, $data);

        return $this->response->setJSON([
            'success' => (bool)$update,
            'message' => $update ? 'Record updated successfully' : 'Failed to update record'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        $delete = $this->serverinventoryModel->delete($id);

        return $this->response->setJSON([
            'success' => (bool)$delete,
            'message' => $delete ? 'Record deleted successfully' : 'Failed to delete record'
        ]);
    }


public function viewForm()
{
    $startDate   = $this->request->getGet('start_date');
    $endDate     = $this->request->getGet('end_date');

    $type        = $this->request->getGet('type');
    $model       = $this->request->getGet('model');
    $server_name = $this->request->getGet('server_name');

    $builder = $this->serverinventoryModel;

    // ✅ DATE RANGE FILTER
    if ($startDate && $endDate) {
        $builder = $builder->where('acquisition >=', $startDate)
                           ->where('acquisition <=', $endDate);
    }

    if ($type)        $builder = $builder->like('type', $type);
    if ($model)       $builder = $builder->like('model', $model);
    if ($server_name) $builder = $builder->like('server_name', $server_name);

    $records = $builder->orderBy('id', 'DESC')->findAll();

    // ====================== PDF ======================
    $mpdf = new Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 55,
        'margin_bottom' => 10,
    ]);

    // HEADER
    $header = '
    <table width="100%" style="border:none;">
        <tr>
            <td colspan="3" style="text-align:right; border:none;">
                <b>IM-001-0</b><br>
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
                    <strong>INVENTORY OF SERVER COMPUTERS</strong><br>
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

    // TABLE ROWS
    $tableRows = '';

    if (!empty($records)) {
        foreach ($records as $row) {

            $datetime = !empty($row['datetime']) ? strtotime($row['datetime']) : null;

            $tableRows .= '
            <tr>
                <td>' . esc($row['acquisition']) . '</td>
                <td>' . esc($row['type']) . '</td>
                <td>' . esc($row['model']) . '</td>
                <td>' . esc($row['processor']) . '</td>
                <td>' . esc($row['memory']) . '</td>
                <td>' . esc($row['OS']) . '</td>
                <td>' . esc($row['server_name']) . '</td>
            </tr>
            ';
        }
    } else {
        $tableRows = '<tr><td colspan="8">No data found</td></tr>';
    }

    // HTML CONTENT
    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f5f5f5; font-weight: bold; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>

    <table>
        <thead>
            <tr>
                <th>Acquisition</th>
                <th>Type</th>
                <th>Model</th>
                <th>Processor</th>
                <th>Memory</th>
                <th>OS</th>
                <th>Server Name</th>
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
        ->setHeader('Content-Disposition', 'inline; filename="ServerInventory_Report.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}
}