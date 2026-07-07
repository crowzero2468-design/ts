<?php

namespace App\Controllers;
use Config\Database;
use App\Controllers\BaseController;
use App\Models\AcknoModel;
use App\Models\Tbtrouble;


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

        $builder->select('tbtrouble.*, tb_it.name as personnel_name, a.id_num as ack_id_num, a.full_name as ack_full_name, r.remarks as ack_remarks');
        $builder->join('tb_it', 'tb_it.id = tbtrouble.person', 'left');
        $builder->join('tb_AcknowledgedBy a', 'a.id = tbtrouble.Acknoby', 'left');
        $builder->join('tb_AcknowledgedByRemarks r', 'r.id_ack = a.id AND r.trouble_id = tbtrouble.id', 'left');

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
            $rateColumns = array_column($db->query("SHOW COLUMNS FROM tb_rate")->getResultArray(), 'Field');
            $rateTroubleColumn = in_array('trouble_id', $rateColumns) ? 'trouble_id' : 'arta_id';
            $rateValueColumn = in_array('rating', $rateColumns) ? 'rating' : 'rate';

            $builder = $db->table('tbtrouble t');
            $builder->select('t.*, a.id_num, a.full_name as ack_full_name, it.name as personnel_name, r.remarks as ack_remarks, rate.' . $rateValueColumn . ' as rating');
            $builder->join('tb_AcknowledgedBy a', 'a.id = t.acknoby', 'left');
            $builder->join('tb_AcknowledgedByRemarks r', 'r.id_ack = a.id AND r.trouble_id = t.id', 'left');
            $builder->join('tb_it it', 'it.id = t.person', 'left');
            $builder->join('tb_rate rate', "rate.{$rateTroubleColumn} = t.id", 'left');

            if (!empty($start)) $builder->where('t.time >=', $start);
            if (!empty($end))   $builder->where('t.time <=', $end);
            if (!empty($name))  $builder->like('t.name', $name);
            if (!empty($type))  $builder->where('t.ts_type', $type);


            $records = $builder->orderBy('t.time', 'ASC')->get()->getResultArray();

                    // ====================== EXCEL/HTML EXPORT PART ======================
                        $tableRows = '';

                        if (!empty($records)) {
                            foreach ($records as $row) {
                                $time = !empty($row['time']) ? date('F j, Y h:i a', strtotime($row['time'])) : '';
                                $timeStarted = !empty($row['time_started']) ? date('F j, Y h:i a', strtotime($row['time_started'])) : '';
                                $completionTime = '';
                                if (!empty($row['completion_time'])) {
                                    $compDT = new \DateTime($row['completion_time']);
                                    $startTimeField = !empty($row['time_started']) ? $row['time_started'] : $row['time'];
                                    $startDT = !empty($startTimeField) ? new \DateTime($startTimeField) : null;

                                    $durationText = '';
                                    if ($startDT) {
                                        $diff = $startDT->diff($compDT);
                                        $parts = [];
                                        if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                                        if ($diff->h > 0) $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                                        if ($diff->i > 0) $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
                                        $durationText = !empty($parts) ? 'Returned ' . implode(' ', $parts) : 'Returned less than a minute';
                                    }
                                    $completionTime = $compDT->format('F j, Y h:i a');
                                    if ($durationText) {
                                        $completionTime .= ' (' . $durationText . ')';
                                    }
                                }

                                $name = esc($row['name'] ?? '');
                                $idNum = esc($row['id_num'] ?? '');
                                $location = esc($row['location'] ?? '');
                                $description = esc($row['description'] ?? '');
                                $remarks = esc($row['remarks'] ?? '');
                                $personnel = esc($row['personnel_name'] ?? $row['personnel'] ?? '');
                                $status = esc($row['status'] ?? '');
                                $ackFullName = esc($row['ack_full_name'] ?? '');
                                $ackRemarks = esc($row['ack_remarks'] ?? '');
                                $ratingValue = $row['rating'] ?? '';

                                $acknowledgedBy = '-';
                                if (!empty($row['Acknoby'])) {
                                    $acknowledgedBy = 'ID Number: ' . ($idNum ?: '-') . '<br>'
                                        . 'Full Name: ' . ($ackFullName ?: '-') . '<br>'
                                        . 'Remarks: ' . ($ackRemarks ?: 'No remarks');
                                }

                                $ratingDisplay = '-';
                                if ($ratingValue !== null && $ratingValue !== '') {
                                    $displayRating = is_numeric($ratingValue) ? (int) $ratingValue : null;
                                    if ($displayRating !== null) {
                                        $ratingDisplay = ($displayRating * 20) . '%';
                                    } else {
                                        $ratingDisplay = esc($ratingValue);
                                    }
                                }

                                $tableRows .= '<tr>'
                                    . '<td>' . esc($time) . '</td>'
                                    . '<td>' . $name . '</td>'
                                    . '<td>' . $idNum . '</td>'
                                    . '<td>' . $location . '</td>'
                                    . '<td>' . $description . '</td>'
                                    . '<td>' . $remarks . '</td>'
                                    . '<td>' . $status . '</td>'
                                    . '<td>' . esc($timeStarted) . '</td>'
                                    . '<td>' . $completionTime . '</td>'
                                    . '<td>' . $personnel . '</td>'
                                    . '<td>' . $acknowledgedBy . '</td>'
                                    . '<td>' . $ratingDisplay . '</td>'
                                . '</tr>';
                            }
                        } else {
                            $tableRows = '<tr><td colspan="12">No data found</td></tr>';
                        }

                        $html = '<html><head>'
                            . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
                            . '<style>'
                            . 'body { font-family: Arial, sans-serif; font-size: 12pt; }'
                            . 'table { width: 100%; border-collapse: collapse; }'
                            . 'th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }'
                            . 'th { background-color: #f5f5f5; font-weight: bold; }'
                            . '.title-row { font-size: 18pt; font-weight: bold; text-align: center; }'
                            . '.subtitle-row { text-align: center; font-size: 10pt; }'
                            . '</style>'
                            . '</head><body>'
                            . '<div class="title-row">Technical Assistance Support Log</div>'
                            . '<br />'
                            . '<table>'
                            . '<thead><tr>'
                            . '<th>Date and Time</th>'
                            . '<th>Requested by</th>'
                            . '<th>ID No. of requester</th>'
                            . '<th>Section/Unit</th>'
                            . '<th>Description of work/Problem</th>'
                            . '<th>Action Taken</th>'
                            . '<th>Status/Recommendation</th>'
                            . '<th>Time Started</th>'
                            . '<th>Completion Time</th>'
                            . '<th>Processed by</th>'
                            . '<th>Acknowledged By</th>'
                            . '<th>Rating</th>'
                            . '</tr></thead>'
                            . '<tbody>' . $tableRows . '</tbody>'
                            . '</table>'
                            . '</body></html>';

                        $filename = 'Technical_Assistance_Log_' . date('Ymd_His') . '.xls';

                        return $this->response
                            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
                            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                            ->setBody($html);
            }
}
