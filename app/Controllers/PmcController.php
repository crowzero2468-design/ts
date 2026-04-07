<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PmsModel;

class PmcController extends BaseController
{
    public function index()
    {
        $model = new PmsModel();

        $data['records'] = $model->orderBy('datetime', 'DESC')->findAll();

        return view('admin/pms');
    }

public function getData()
{
    $ward = $this->request->getGet('area');       
    $monthYear = $this->request->getGet('month'); // format: YYYY-MM

    $data = [];

    if (!empty($ward) && !empty($monthYear)) {
        $model = new PmsModel();

        // Filter by area (ward name)
        $model->where('area', $ward);

        // Filter by month
        $startDate = $monthYear . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $model->where('datetime >=', $startDate);
        $model->where('datetime <=', $endDate);

        $data = $model->orderBy('datetime', 'DESC')->findAll();

        // Log query for debugging
        $builder = $model->builder();
        $builder->where('area', $ward);
        $builder->where('datetime >=', $startDate);
        $builder->where('datetime <=', $endDate);
        $builder->orderBy('datetime', 'DESC');
        log_message('debug', 'Query: ' . $builder->getCompiledSelect());
    }

    return $this->response->setJSON(['data' => $data]);
}

public function getWards()
{
    $search = $this->request->getGet('search');

    $db = \Config\Database::connect();
    $builder = $db->table('tb_ward');

    if (!empty($search)) {
        $builder->like('ward', $search);
    }

    $wards = $builder->get()->getResultArray();

    return $this->response->setJSON($wards);
}


public function addPmsForm()
{
    $db = \Config\Database::connect();
    $wards = $db->table('tb_ward')->get()->getResultArray(); // fetch wards for select
    return view('admin/add_pms', ['wards' => $wards]);
}


public function savePms()
{
    $model = new \App\Models\PmsModel();

    $data = [
        'area' => $this->request->getPost('location'),
        'datetime' => $this->request->getPost('datetime'),
        'computerlabel' => $this->request->getPost('computerlabel'),
        'keyboard' => $this->request->getPost('keyboard') ?? 0,
        'mouse' => $this->request->getPost('mouse') ?? 0,
        'display' => $this->request->getPost('display') ?? 0,
        'vga' => $this->request->getPost('vga') ?? 0,
        'hdd' => $this->request->getPost('hdd') ?? 0,
        'ups' => $this->request->getPost('ups') ?? 0,
        'connect' => $this->request->getPost('connect') ?? 0,
        'powercables' => $this->request->getPost('powercables') ?? 0,
        'remarks' => $this->request->getPost('remarks'),
        'performedby' => $this->request->getPost('performedby'),
        'notedby' => $this->request->getPost('notedby'),
    ];

    $model->insert($data);

    return redirect()->to('/pmc')->with('success', 'PMS record added successfully');
}

public function form()
{
    $ward = $this->request->getGet('area');
    $monthYear = $this->request->getGet('month');

    $model = new \App\Models\PmsModel();

    $builder = $model;

    if ($ward) $builder = $builder->where('area', $ward);
    if ($monthYear) {
        $startDate = $monthYear . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        $builder = $builder->where('datetime >=', $startDate)
                           ->where('datetime <=', $endDate);
    }

    $records = $builder->orderBy('datetime', 'ASC')->findAll();

    // ====================== PDF PART ======================
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    // Header & Styles
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

    <table class="header-table" width="100%">
        <tr>
            <td colspan="3" style="text-align:right;"><b>IM-006-0</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" height="80"></td>
            <td style="text-align:center;">
                <div class="top-title">Republic of the Philippines</div>
                <div class="top-title"><strong>DEPARTMENT OF HEALTH</strong></div>
                <div class="top-title"><strong>CAGAYAN VALLEY MEDICAL CENTER</strong></div>
                <div class="top-title">Regional Tertiary, Teaching, Training, and Research Medical Center</div>
                <div class="top-title"><strong>IT EQUIPMENT AND DEVICE PREVENTIVE MAINTENANCE CHECKLIST</strong></div>
            </td>
            <td style="text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_logo.png" height="80"></td>
        </tr>

        <tr>
            <td colspan="3" style="text-align:left;">Area/Location: <strong>' . esc($ward) . '</strong></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:left;">Month & Year: <strong>' . date('F Y', strtotime($monthYear . '-01')) . '</strong></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Time</th>
                <th rowspan="2">Computer Label</th>
                <th colspan="8">Check Points</th>
                <th rowspan="2">Remarks</th>
                <th rowspan="2">Performed By:</th>
                <th rowspan="2">Noted By:</th>
            </tr>
            <tr>
                <th>Keyboard</th>
                <th>Mouse</th>
                <th>Display</th>
                <th>VGA Cable</th>
                <th>HDD Space</th>
                <th>UPS/AVR</th>
                <th>Connect</th>
                <th>Power Cables</th>
            </tr>
        </thead>
        <tbody>';

    if (!empty($records)) {
        foreach ($records as $row) {
            $html .= '<tr>
                <td>' . date('m/d/Y', strtotime($row['datetime'])) . '</td>
                <td>' . date('h:i A', strtotime($row['datetime'])) . '</td>
                <td>' . esc($row['computerlabel']) . '</td>
                <td>' . ($row['keyboard'] ? '✔' : '') . '</td>
                <td>' . ($row['mouse'] ? '✔' : '') . '</td>
                <td>' . ($row['display'] ? '✔' : '') . '</td>
                <td>' . ($row['vga'] ? '✔' : '') . '</td>
                <td>' . ($row['hdd'] ? '✔' : '') . '</td>
                <td>' . ($row['ups'] ? '✔' : '') . '</td>
                <td>' . ($row['connect'] ? '✔' : '') . '</td>
                <td>' . ($row['powercables'] ? '✔' : '') . '</td>
                <td>' . esc($row['remarks']) . '</td>
                <td>' . esc($row['performedby']) . '</td>
                <td>' . esc($row['notedby']) . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="13">No data found</td></tr>';
    }

    $html .= '</tbody></table>';

    $mpdf->WriteHTML($html);

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="IT_PMC_Checklist.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}

}
