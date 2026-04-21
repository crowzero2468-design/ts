<?php

namespace App\Controllers;
use Config\Database;
use App\Controllers\BaseController;
use App\Models\AcknoModel;
use App\Models\Tbtrouble;
use Mpdf\Mpdf;


class TSHistoryController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $types = $db->table('tb_tstype')->get()->getResultArray();
        return view('admin/activity-logs', ['types' => $types]);
    }

    public function getData()
    {
        $db = \Config\Database::connect();

        $start   = $this->request->getGet('start_date');
        $end     = $this->request->getGet('end_date');
        $name    = $this->request->getGet('name');
        $ts_type = $this->request->getGet('ts_type'); // NEW

        $builder = $db->table('tbtrouble');

        $builder->select('tbtrouble.*, tb_it.name as personnel_name');
        $builder->join('tb_it', 'tb_it.id = tbtrouble.person', 'left');

        if (!empty($start)) {
            $builder->where('tbtrouble.time >=', $start);
        }

        if (!empty($end)) {
            $builder->where('tbtrouble.time <=', $end);
        }

        if (!empty($name)) {
            $builder->like('tb_it.name', $name, 'both');
        }

        // FILTER BY TS TYPE
        if (!empty($ts_type)) {
            $builder->where('tbtrouble.ts_type', $ts_type);
        }

        $records = $builder
            ->orderBy('tbtrouble.time', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'data' => $records
        ]);
    }

        public function printForm()
        {
            $db = \Config\Database::connect();

            // Get filters from request
            $start = $this->request->getGet('start_date');
            $end   = $this->request->getGet('end_date');
            $name  = $this->request->getGet('name');
            $type  = $this->request->getGet('ts_type');

            // ================== LIMIT TO 6 MONTHS ==================
            if (!empty($start) && !empty($end)) {
                $startDate = new \DateTime($start);
                $endDate   = new \DateTime($end);

                // Calculate difference
                $interval = $startDate->diff($endDate);

                // If more than 6 months, adjust end date
                if ($interval->m + ($interval->y * 12) > 6) {
                    $endDate = clone $startDate;
                    $endDate->modify('+6 months');
                    $end = $endDate->format('Y-m-d H:i:s');
                }
            }
            // ======================================================
            $loggedUser = session()->get('name') ?? session()->get('name') ?? 'Unknown User';
            // Build query
            $builder = $db->table('tbtrouble t');
            $builder->select('t.*, a.id_num, it.name as personnel_name');
            $builder->join('tb_AcknowledgedBy a', 'a.id = t.acknoby', 'left');
            $builder->join('tb_it it', 'it.id = t.person', 'left');

            if (!empty($start)) $builder->where('t.time >=', $start);
            if (!empty($end))   $builder->where('t.time <=', $end);
            if (!empty($name))  $builder->like('t.name', $name);
            if (!empty($type))  $builder->where('t.ts_type', $type);


            $records = $builder->orderBy('t.time', 'ASC')->get()->getResultArray();

                    // ====================== PDF PART ======================
                        $mpdf = new \Mpdf\Mpdf([
                            'format' => 'A4-L',
                            'margin_left' => 10,
                            'margin_right' => 10,
                            'margin_top' => 55,    // space for header
                            'margin_bottom' => 20, // space for footer
                        ]);

                        // Header
                        $header = '
                        <table width="100%">
                            <tr>
                                <td colspan="3" style="text-align:right; border:none;">
                                    <b>IM-019-0</b><br>
                                    <small>28March2024</small>
                                </td>
                            </tr>
                            <tr>
                                <td width="15%" style="border:none;"><img src="' . FCPATH . 'assets/img/cvmc_logo.png" height="80"></td>
                                <td width="70%" style="text-align:center; border:none;">
                                    Republic of the Philippines<br>
                                    <strong>DEPARTMENT OF HEALTH</strong><br>
                                    <strong>CAGAYAN VALLEY MEDICAL CENTER</strong><br>
                                    Regional Tertiary, Teaching, Training, and Research Medical Center<br>
                                    Dalan na Padday, Carig Sur, Tuguegarao City, Cagayan
                                </td>
                                <td width="15%" style="border:none;"><img src="' . FCPATH . 'assets/img/DOH_logo.png" height="80"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:center;"><h3>Technical Assistance Support Log</h3></td>
                            </tr>
                        </table>
                        <hr>
                        ';

                        $mpdf->SetHTMLHeader($header);
                            
                        // Footer
                        // $footer = '
                        // <table width="100%" style="font-size:10pt;">
                        //     <tr>
                        //         <td width="50%">Generated by: ' .strtoupper( $loggedUser ). '</td>
                        //         <td width="50%" style="text-align:right;">Page {PAGENO} of {nb}</td>
                        //     </tr>
                        // </table>
                        // ';
                        // $mpdf->SetHTMLFooter($footer);

                        // Table rows
                        $tableRows = '';
                        if (!empty($records)) {
                            foreach ($records as $row) {
                                $time = !empty($row['time']) ? date('F j, Y', strtotime($row['time'])) . '<br>' . date('h:i a', strtotime($row['time'])) : '';
                                $timeStarted = !empty($row['time_started']) ? date('F j, Y', strtotime($row['time_started'])) . '<br>' . date('h:i a', strtotime($row['time_started'])) : '';
                                
                                    $completionTime = '';
                                        if (!empty($row['completion_time'])) {
                                            $compDT = new \DateTime($row['completion_time']);

                                            // Prioritize time_started if available, else fallback to time
                                            $startTimeField = !empty($row['time_started']) ? $row['time_started'] : $row['time'];
                                            $startDT = !empty($startTimeField) ? new \DateTime($startTimeField) : null;

                                            $durationText = '';
                                            if ($startDT) {
                                                $diff = $startDT->diff($compDT);

                                                // Build human-readable duration
                                                $parts = [];
                                                if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                                                if ($diff->h > 0) $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                                                if ($diff->i > 0) $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');

                                                $durationText = !empty($parts) ? 'Returned ' . implode(' ', $parts) : 'Returned less than a minute';
                                            }

                                            // Format completion time
                                            $completionTime = $compDT->format('F j, Y') . '<br>' . $compDT->format('h:i a');
                                            if ($durationText) {
                                                $completionTime .= '<br><br>' . $durationText;
                                            }
                                        }
                                        $name = $row['name'] ?? '';
                                        $idNum = $row['id_num'] ?? '';
                                        $location = $row['location'] ?? '';
                                        $description = $row['description'] ?? '';
                                        $remarks = $row['remarks'] ?? '';
                                        $personnel = $row['personnel_name'] ?? $row['personnel'] ?? '';
                                        $status = $row['status'] ?? '';
                                $tableRows .= "<tr>
                                <td>" . strtoupper($time) . "</td>
                                <td>" . strtoupper($name) . "</td>
                                <td>" . strtoupper($idNum) . "</td>
                                <td>" . strtoupper($location) . "</td>
                                <td>" . strtoupper($description) . "</td>
                                <td>" . strtoupper($remarks) . "</td>
                                <td>" . strtoupper($status) . "</td>
                                <td>" . strtoupper($timeStarted) . "</td>
                                <td>" . strtoupper($completionTime) . "</td>
                                <td>" . strtoupper($personnel) . "</td>
                            </tr>";
                            }
                        } else {
                            $tableRows = '<tr><td colspan="8">No data found</td></tr>';
                        }

                        // HTML content for table
                        $html = '
                        <style>
                            body { font-family: Arial, sans-serif; font-size: 12pt; }
                            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                            th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
                            th { background-color: #f5f5f5; font-weight: bold; width: 10%; }
                            tr { page-break-inside: avoid; }
                            thead { display: table-header-group; }
                            tfoot { display: table-footer-group; }
                        </style>

                        <table>
                            <thead>
                                <tr>
                                    <th>Date and Time</th>
                                    <th>Requested by</th>
                                    <th>ID No. of requester</th>
                                    <th>Section/Unit</th>
                                    <th>Description of work/Problem</th>
                                    <th>Action Taken</th>
                                    <th>Status/Recommendation</th>
                                    <th>Time Started</th>
                                    <th>Completion Time</th>
                                    <th>Processed by</th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $tableRows . '
                            </tbody>
                        </table>
                        ';

                $mpdf->WriteHTML($html);

                // ====================== INLINE PREVIEW ======================
                // Return PDF inline for browser preview
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="Technical_Assistance_Log.pdf"')
                    ->setBody($mpdf->Output('', 'S'));
            }
}
