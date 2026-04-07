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
                'time' => date('H:i', strtotime($row['datetime'])),
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
}
