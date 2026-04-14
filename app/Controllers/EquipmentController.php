<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;

class EquipmentController extends BaseController
{
   public function index()
    {
        $model = new EquipmentModel();
        $data['equipment'] = $model->findAll();
        return view('admin/it_equipment', $data);
    }

    public function save()
    {
        $model = new EquipmentModel();

        $data = [
            'type' => $this->request->getPost('type'),
            'model' => $this->request->getPost('model'),
            'label' => $this->request->getPost('label'),
            'AccountableArea' => $this->request->getPost('AccountableArea'),
            'description' => $this->request->getPost('description'),
            'acquisitiondate' => $this->request->getPost('acquisitiondate'),
            'estimatedlife' => $this->request->getPost('estimatedlife'),
            'remarks' => $this->request->getPost('remarks'),
            'status' => $this->request->getPost('status') ?? 'NEW',
            'quantity' => $this->request->getPost('quantity') ?? 1,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Equipment saved successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save equipment. Please try again.'
            ]);
        }
    }

    public function getData()
{
    $db = \Config\Database::connect();
    $builder = $db->table('tb_tools');

    $draw   = intval($this->request->getGet('draw'));
    $start  = intval($this->request->getGet('start'));
    $length = intval($this->request->getGet('length'));

    $search = $this->request->getGet('search')['value'] ?? '';

    // Custom filters
    $type      = $this->request->getGet('type');
    $model     = $this->request->getGet('model');
    $area      = $this->request->getGet('area');
    $status    = $this->request->getGet('status');
    $acqStart  = $this->request->getGet('acquisition_start');
    $acqEnd    = $this->request->getGet('acquisition_end');
    $lifeSpan  = $this->request->getGet('estimatedlife');

    // Normalize dates (IMPORTANT)
    if ($acqStart) {
        $acqStart = date('Y-m-d', strtotime($acqStart));
    }
    if ($acqEnd) {
        $acqEnd = date('Y-m-d', strtotime($acqEnd));
    }

    // Total records (no filters)
    $recordsTotal = $builder->countAllResults(false);

    // ================= SEARCH (OR BLOCK ONLY) =================
    if ($search) {
        $builder->groupStart()
            ->like('type', $search)
            ->orLike('model', $search)
            ->orLike('label', $search)
            ->orLike('AccountableArea', $search)
            ->orLike('description', $search)
            ->orLike('remarks', $search)
            ->orLike('status', $search)
        ->groupEnd();
    }

    // ================= FILTERS (AND CONDITIONS) =================
    if ($type) {
        $builder->like('type', $type);
    }

    if ($model) {
        $builder->like('model', $model);
    }

    if ($area) {
        $builder->like('AccountableArea', $area);
    }

    if ($status) {
        $builder->where('status', $status);
    }

    if ($lifeSpan) {
        $builder->where('estimatedlife', $lifeSpan);
    }

    // ================= DATE FILTER (FIXED LOGIC) =================
    if ($acqStart && $acqEnd) {
        $builder->where('acquisitiondate >=', $acqStart);
        $builder->where('acquisitiondate <=', $acqEnd);
    } elseif ($acqStart) {
        $builder->where('acquisitiondate >=', $acqStart);
    } elseif ($acqEnd) {
        $builder->where('acquisitiondate <=', $acqEnd);
    }

    // ================= COUNT FILTERED =================
    $recordsFiltered = $builder->countAllResults(false);

    // ================= DATA =================
    $data = $builder->limit($length, $start)
                    ->get()
                    ->getResult();

    return $this->response->setJSON([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
}

   public function Form()
{
    $equipmentModel = new EquipmentModel();

    // Get optional filters from GET parameters
    $type = $this->request->getGet('type');
    $model = $this->request->getGet('model');
    $area = $this->request->getGet('area');
    $acqStart = $this->request->getGet('acquisition_start');
    $acqEnd   = $this->request->getGet('acquisition_end');
    $lifeSpan = $this->request->getGet('estimatedlife');
    $status   = $this->request->getGet('status');

    $builder = $equipmentModel;

    if ($type) $builder = $builder->where('type', $type);
    if ($model) $builder = $builder->like('model', $model);
    if ($area) $builder = $builder->like('AccountableArea', $area);
    if ($acqStart) $builder = $builder->where('acquisitiondate >=', $acqStart);
    if ($acqEnd)   $builder = $builder->where('acquisitiondate <=', $acqEnd);
    if ($lifeSpan) $builder = $builder->where('estimatedlife', $lifeSpan);
    if ($status)   $builder = $builder->where('status', $status);

    $equipmentList = $builder->findAll();

    // ====================== PDF PART ======================
    $mpdf = new Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 55,
        'margin_bottom' => 10,
    ]);

    // Format "As of" based on selected dates
    if ($acqStart && $acqEnd) {
        $startDate = (new \DateTime($acqStart))->format('F, Y');
        $endDate   = (new \DateTime($acqEnd))->format('F, Y');
        $asOf = $startDate . ' to ' . $endDate;
    } elseif ($acqStart) {
        $asOf = (new \DateTime($acqStart))->format('F, Y');
    } elseif ($acqEnd) {
        $asOf = (new \DateTime($acqEnd))->format('F, Y');
    } else {
        $asOf = date('F, Y');
    }

    // Header
    $header = '
    <table width="100%" style="border:none;">
        <tr>
            <td colspan="3" style="text-align:right; border:none;"><strong>IM-004-0</strong></td>
        </tr>
        <tr>
            <td style="border:none;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" height="80"></td>
            <td style="border:none; text-align:center;">
                Republic of the Philippines<br>
                <strong>DEPARTMENT OF HEALTH</strong><br>
                <strong>CAGAYAN VALLEY MEDICAL CENTER</strong><br>
                Regional Tertiary, Teaching, Training, and Research Medical Center<br>
                <strong>INVENTORY OF IT EQUIPMENT AND DEVICES</strong><br>
            </td>
            <td style="border:none; text-align:right;"><img src="' . FCPATH . 'assets/img/DOH_logo.png" height="80"></td>
        </tr>
        <tr>
            <td colspan="3" style="border:none; text-align:left;">
                <b>As of:</b> ' . $asOf . '
            </td>
        </tr>
    </table>';
    $mpdf->SetHTMLHeader($header);

    // Table rows
    $tableRows = '';
    if (!empty($equipmentList)) {
        $no = 1;
        foreach ($equipmentList as $eq) {
            $acq = (empty($eq['acquisitiondate']) || $eq['acquisitiondate'] == '0000-00-00') 
                ? '-' 
                : (new \DateTime($eq['acquisitiondate']))->format('F j, Y');

            $tableRows .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . esc($eq['type']) . '</td>
                <td>' . esc($eq['model']) . '</td>
                <td>' . esc($eq['label']) . '</td>
                <td>' . esc($eq['AccountableArea']) . '</td>
                <td>' . esc($eq['description']) . '</td>
                <td>' . $acq . '</td>
                <td>' .esc($eq['estimatedlife']) . '</td>
                <td>' . esc($eq['remarks']) . '</td>
            </tr>';
        }
    } else {
        $tableRows = '<tr><td colspan="9">No data found</td></tr>';
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
                <th>Equipment Type</th>
                <th>Model</th>
                <th>Label(If any)</th>
                <th>Accountable Area/Personnel</th>
                <th>Description/Specification</th>
                <th>Acquisition Date</th>
                <th>Estimated Life Span</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            ' . $tableRows . '
        </tbody>
    </table>';

    $mpdf->WriteHTML($html);

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="Inventory_IT_Equipment.pdf"')
        ->setBody($mpdf->Output('', 'S'));
}

    // New method: Import Excel
public function importExcel()
{
    $file = $this->request->getFile('excelFile');
    $status = $this->request->getPost('status');

    if ($file && $file->isValid()) {

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->getHighestRow();

        $model = new \App\Models\EquipmentModel();

        for ($i = 2; $i <= $rows; $i++) {

            // ================= DATE FIX =================
            $rawDate = $sheet->getCell('E' . $i)->getValue();
            $acquisitionDate = null;

            if ($rawDate) {

                // Excel numeric date (most reliable)
                if (is_numeric($rawDate)) {
                    $acquisitionDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)
                        ->format('Y-m-d');

                } else {

                    // Try strict formats (DD/MM/YYYY safe)
                    $formats = ['d/m/Y', 'd-M-y', 'Y-m-d'];

                    foreach ($formats as $format) {
                        $dt = \DateTime::createFromFormat($format, $rawDate);
                        if ($dt) {
                            $acquisitionDate = $dt->format('Y-m-d');
                            break;
                        }
                    }

                    // Fallback
                    if (!$acquisitionDate) {
                        $ts = strtotime($rawDate);
                        if ($ts !== false) {
                            $acquisitionDate = date('Y-m-d', $ts);
                        }
                    }
                }
            }

            // ================= QUANTITY FIX =================
            $quantityRaw = $sheet->getCell('G' . $i)->getValue();
            $quantity = is_numeric($quantityRaw)
                ? $quantityRaw
                : (int) filter_var($quantityRaw, FILTER_SANITIZE_NUMBER_INT);

            if (!$quantity) $quantity = 1;

            // ================= DATA =================
            $data = [
                'label' => trim($sheet->getCell('A' . $i)->getValue()),
                'model' => trim($sheet->getCell('B' . $i)->getValue()),
                'description' => trim($sheet->getCell('D' . $i)->getValue()),
                'acquisitiondate' => $acquisitionDate ?? date('Y-m-d'),
                'type' => trim($sheet->getCell('F' . $i)->getValue()),
                'quantity' => $quantity,
                'inspector' => trim($sheet->getCell('H' . $i)->getValue()) ?: 'N/A',
                'AccountableArea' => trim($sheet->getCell('I' . $i)->getValue()),
                'estimatedlife' => trim($sheet->getCell('J' . $i)->getValue()) ?: 'N/A',
                'remarks' => '', // REQUIRED FIELD
                'status' => $status ?? 'NEW',
            ];

            // ================= DEBUG (optional) =================
            // if (!$model->insert($data)) {
            //     dd($model->errors(), $data);
            // }

            $model->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Excel imported successfully!',
        ]);
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'Invalid Excel file!',
    ]);
}

    public function get($id)
{
    $model = new EquipmentModel();
    $data = $model->find($id);
    return $this->response->setJSON($data);
}

public function update()
{
    $model = new EquipmentModel();
    $id = $this->request->getPost('id');

    $data = [
        'type' => $this->request->getPost('type'),
        'model' => $this->request->getPost('model'),
        'label' => $this->request->getPost('label'),
        'AccountableArea' => $this->request->getPost('AccountableArea'),
        'description' => $this->request->getPost('description'),
        'acquisitiondate' => $this->request->getPost('acquisitiondate'),
        'estimatedlife' => $this->request->getPost('estimatedlife'),
        'remarks' => $this->request->getPost('remarks'),
    ];

    if($model->update($id, $data)){
        return $this->response->setJSON(['success'=>true,'message'=>'Equipment updated successfully']);
    }
    return $this->response->setJSON(['success'=>false,'message'=>'Failed to update equipment']);
}

public function delete()
{
    $model = new EquipmentModel();
    $id = $this->request->getPost('id');

    if($model->update($id, ['status'=>'I'])){
        return $this->response->setJSON(['success'=>true,'message'=>'Equipment marked as inactive']);
    }
    return $this->response->setJSON(['success'=>false,'message'=>'Failed to delete equipment']);
}
}