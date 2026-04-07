<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class SearchController extends BaseController
{
    /**
     * 🔍 Live search for WARD / OFFICE
     * Saves typed value if not found (frontend logic)
     */
    public function ward()
    {
        $q = $this->request->getGet('q');

        if (!$q) {
            return $this->response->setJSON([]);
        }

        $db = Database::connect();

        $result = $db->table('tb_ward')
            ->like('ward', $q)
            ->orderBy('ward', 'ASC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($result);
    }

    public function technician()
    {
        $q = $this->request->getGet('q');

        if (!$q) {
            return $this->response->setJSON([]);
        }

        $db = Database::connect();

        $result = $db->table('tb_it')
            ->like('name', $q)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($result);
    }
}
