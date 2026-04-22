<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\Tbtrouble;

class Profile extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $troubleModel = new Tbtrouble();

        $userId = session()->get('user_id');

        // Get current user
        $data['user'] = $userModel->find($userId);

        // Get activity log from tbtrouble
        $data['activities'] = $troubleModel
            ->where('person', $userId)
            ->orWhere('personnel', session()->get('name'))
            ->orderBy('time', 'DESC')
            ->findAll();

        return view('profile/profile', $data);
    }

//     public function updateName()
//     {
//         $userModel = new UserModel();
//         $userId = session()->get('user_id');

//         $currentUser = $userModel->find($userId);
//         $newName = trim($this->request->getPost('name'));

//         // 🔎 Check if same name
//         if ($newName === $currentUser['name']) {
//             return redirect()->back()->with('info', 'No changes detected');
//         }

//         $userModel->update($userId, [
//             'name' => $newName
//         ]);

//         session()->set('name', $newName);

//         return redirect()->back()->with('success', 'Name updated successfully');
//     }


//     public function updatePassword()
//     {
//         $userModel = new UserModel();
//         $userId = session()->get('user_id');

//         if (!$userId) {
//             return redirect()->back()->with('info', 'Session expired');
//         }

//         $currentUser = $userModel->where('id', $userId)->first();

//         if (!$currentUser) {
//             return redirect()->back()->with('info', 'User not found');
//         }

//         $newPassword = $this->request->getPost('new_password');

//         if (empty($newPassword)) {
//             return redirect()->back()->with('info', 'No changes detected');
//         }

//         // Make sure column name matches your DB
//         if (isset($currentUser['pass']) && password_verify($newPassword, $currentUser['pass'])) {
//             return redirect()->back()->with('info', 'No changes detected');
//         }

//         $userModel->update($userId, [
//         'pass' => password_hash($newPassword, PASSWORD_DEFAULT)
//         ]);

//         return redirect()->back()->with('password_changed', 'Password updated successfully');
//     }

//     public function updateUsername()
// {
//     $userModel = new UserModel();

//     $userId = session()->get('user_id');

//     $userModel->update($userId, [
//         'uname' => $this->request->getPost('username')
//     ]);

//     return redirect()->back()->with('success', 'Username updated successfully.');
// }

public function update()
{
    $userModel = new UserModel();
    $userId = session()->get('user_id');

    if (!$userId) {
        return redirect()->back()->with('info', 'Session expired');
    }

    $currentUser = $userModel->where('id', $userId)->first();

    if (!$currentUser) {
        return redirect()->back()->with('info', 'User not found');
    }

    $username    = trim($this->request->getPost('username'));
    $name        = trim($this->request->getPost('name'));
    $newPassword = $this->request->getPost('new_password');

    $updateData = [];
    $changedFields = [];

    // ✅ USERNAME
    if (!empty($username) && $username !== $currentUser['uname']) {
        $updateData['uname'] = $username;
        $changedFields[] = 'Username';
    }

    // ✅ NAME
    if (!empty($name) && $name !== $currentUser['name']) {
        $updateData['name'] = $name;
        $changedFields[] = 'Name';
        session()->set('name', $name);
    }

    // ✅ PASSWORD
    if (!empty($newPassword)) {

        if (isset($currentUser['pass']) &&
            password_verify($newPassword, $currentUser['pass'])) {

            return redirect()->back()->with('info', 'No changes detected');
        }

        $updateData['pass'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $changedFields[] = 'Password';
    }

    // ✅ IF SOMETHING CHANGED
    if (!empty($updateData)) {

        $userModel->update($userId, $updateData);

        // 🔐 If password changed → force logout
        if (in_array('Password', $changedFields)) {

            // If ALL fields changed
            if (count($changedFields) === 3) {
                return redirect()->to('/profile')
                    ->with('logout_reason', 'Successfully updated. Please login again.');
            }

            // Password only (or password + 1 field)
            return redirect()->to('/profile')
                ->with('logout_reason', 'Password updated. Please login again.');
        }

        // ✅ Only Username / Name changed
        return redirect()->to('/profile')
            ->with('success', implode(', ', $changedFields) . ' updated successfully!');
    }

    return redirect()->back()->with('info', 'No changes detected');
}

public function printPdf()
{
    $db = \Config\Database::connect();

    // Get filters from request
    $start = $this->request->getGet('start_date');
    $end   = $this->request->getGet('end_date');
    $name  = $this->request->getGet('name');
    $type  = $this->request->getGet('ts_type');
    $userId = session()->get('user_id');


    // ================== LIMIT TO 6 MONTHS ==================
    if (!empty($start) && !empty($end)) {
        $startDate = new \DateTime($start);
        $endDate   = new \DateTime($end);

        $interval = $startDate->diff($endDate);

        if ($interval->m + ($interval->y * 12) > 6) {
            $endDate = clone $startDate;
            $endDate->modify('+6 months');
            $end = $endDate->format('Y-m-d H:i:s');
        }
    }

    $loggedUser = session()->get('name') ?? 'Unknown User';
    $role = session()->get('role') ?? 'user';
    // ================== QUERY ==================
        $builder = $db->table('tbtrouble t');
        $builder->select('t.*, a.id_num, it.name as personnel_name');

        $builder->join('tb_AcknowledgedBy a', 'a.id = t.acknoby', 'left');
        $builder->join('tb_it it', 'it.id = t.person', 'left'); // safer
        

        // ✅ ROLE-BASED FILTER
        if ($role == 'admin' || $role == 3) {
           $builder->groupStart()
                ->where('t.personnel', $loggedUser)
                ->orWhere('t.person', $userId)
                ->groupEnd();
            

        } else {
            // Regular users only see their own records
            $builder->where('person', $userId); 
        }

        // FIX THIS LINE (only if correct relation exists)
        // $builder->where('it.user_id', session()->get('user_id'));

       if (!empty($start)) {
            $builder->where('t.time >=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $builder->where('t.time <=', $end . ' 23:59:59');
        }

        if (!empty($name))  $builder->like('t.name', $name);
        if (!empty($type))  $builder->where('t.ts_type', $type);

    $records = $builder->orderBy('t.time', 'ASC')->get()->getResultArray();

    // ====================== PDF ======================
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 55,
        'margin_bottom' => 20,
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
                                <td width="15%" style="border:none;"><img src="' . FCPATH . 'assets/img/DOH_Logo.png" height="80"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:center;"><h3>Technical Assistance Support Log</h3></td>
                            </tr>
                        </table>
                        <hr>
                        ';

                        $mpdf->SetHTMLHeader($header);

                        // Footer
                        //$footer = '
                        // <table width="100%" style="font-size:10pt;">
                        //     <tr>
                        //         <td width="50%">Generated by: ' . esc($loggedUser) . '</td>
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
                                                $completionTime .= '<br>' . $durationText;
                                            }
                                        }

                                $tableRows .= '
                                <tr>
                                    <td>' . $time . '</td>
                                    <td>' . esc($row['name'] ?? '') . '</td>
                                    <td>' . esc($row['id_num'] ?? '') . '</td>
                                    <td>' . esc($row['location'] ?? '') . '</td>
                                    <td>' . esc($row['description'] ?? '') . '</td>
                                    <td>' . esc($row['remarks'] ?? '') . '</td>
                                    <td>&nbsp;</td>
                                    <td>'. $timeStarted .'</td>
                                    <td>' . $completionTime . '</td>
                                    <td>' . esc($row['personnel_name'] ?? '') . '</td>
                                </tr>';
                            }
                        } else {
                            $tableRows = '<tr><td colspan="10">No data found</td></tr>';
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
