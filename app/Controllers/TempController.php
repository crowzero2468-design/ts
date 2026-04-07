<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TempModel;

class TempController extends BaseController
{
    public function index()
    {
        return view('admin/server_temp');
    }

     public function getData()
    {
        $model = new TempModel();

        $request = service('request');
        $searchDate = $request->getGet('date');
        $searchMonitor = $request->getGet('monitor_by');
        $searchTemp = $request->getGet('temp');

        $builder = $model->builder();

        if ($searchDate) {
            $builder->like('DATE(datetime)', $searchDate);
        }
        if ($searchMonitor) {
            $builder->like('monitor_by', $searchMonitor);
        }
        if ($searchTemp) {
            $builder->like('temp', $searchTemp);
        }

        $data = $builder->get()->getResultArray();

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'date' => date('F j, Y', strtotime($row['datetime'])),
                'time' => date('g:i A', strtotime($row['datetime'])),
                'temp' => $row['temp'],
                'monitor_by' => $row['monitor_by'],
            ];
        }

        return $this->response->setJSON(['data' => $result]);
    }

     public function add()
    {
        $request = service('request');

        // Only accept POST
        if (!$request->isAJAX() || !$request->getMethod() === 'post') {
            return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                        ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $model = new TempModel();

        // Get input
        $datetime = $request->getPost('datetime');  // expects "YYYY-MM-DD HH:MM"
        $temp = $request->getPost('temp');
        $monitor_by = $request->getPost('monitor_by');

        // Simple validation
        if (!$datetime || !$temp || !$monitor_by) {
            return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                        ->setJSON(['status' => 'error', 'message' => 'All fields are required']);
        }

        // Save record
        $model->insert([
            'datetime' => $datetime,
            'temp' => $temp,
            'monitor_by' => $monitor_by
        ]);

        return $this->response
            ->setJSON(['status' => 'success', 'message' => 'Temperature record added']);
    }

    public function TempReport()
{
    $model = new TempModel();

    // Optional filters
    $date = $this->request->getGet('date');
    $monitor_by = $this->request->getGet('monitor_by');
    $status = $this->request->getGet('status');

    $builder = $model;

    if ($date) $builder = $builder->where('DATE(datetime)', $date);
    if ($monitor_by) $builder = $builder->like('monitor_by', $monitor_by);
    if ($status) $builder = $builder->where('status', $status);

    $list = $builder->findAll();

    // ================= PDF =================
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 55,
        'margin_bottom' => 10,
    ]);

    // Header
    $header = '
    <table width="100%" style="border:none;">
        <tr>
            <td style="border:none;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" height="80"></td>
            <td style="border:none; text-align:center;">
                Republic of the Philippines<br>
                <strong>DEPARTMENT OF HEALTH</strong><br>
                <strong>CAGAYAN VALLEY MEDICAL CENTER</strong><br>
                Regional Tertiary, Teaching, Training, and Research Medical Center<br>
                <strong>SERVER ROOM TEMPERATURE MONITORING</strong><br>
            </td>
            <td style="border:none; text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_logo.png" height="80"></td>
        </tr>
    </table>';
    $mpdf->SetHTMLHeader($header);

    // Table rows
    $tableRows = '';
    if (!empty($list)) {
        $no = 1;
        foreach ($list as $row) {
            $dt = (empty($row['datetime']) || $row['datetime'] == '0000-00-00 00:00:00') 
                ? '-' 
                : (new \DateTime($row['datetime']))->format('F j, Y - h:i A');

            $tableRows .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . $dt . '</td>
                <td>' . esc($row['temp']) . '</td>
                <td>' . esc($row['monitor_by']) . '</td>
            </tr>';
        }
    } else {
        $tableRows = '<tr><td colspan="5">No data found</td></tr>';
    }

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
                <th>No.</th>
                <th>Date & Time</th>
                <th>Temperature</th>
                <th>Monitored By</th>
            </tr>
        </thead>
        <tbody>
            ' . $tableRows . '
        </tbody>
    </table>';

    $mpdf->WriteHTML($html);

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="Temperature_Monitoring.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}
}
