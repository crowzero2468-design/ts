<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class SearchController extends BaseController
{
    /**
     * 🔍 Live search for WARD / OFFICE
     * Returns matching ward names from tb_ward
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

    public function ward2()
{
    $q = $this->request->getGet('q');

    if (!$q) {
        return $this->response->setJSON([]);
    }

    $db = Database::connect();

    $result = $db->table('tb_pms')
        ->select('area as ward')
        ->like('area', $q)
        ->groupBy('area')
        ->orderBy('area', 'ASC')
        ->limit(10)
        ->get()
        ->getResultArray();

    return $this->response->setJSON($result);
}


public function doctype()
{
    $q = $this->request->getGet('q');

    $db = Database::connect();

    $builder = $db->table('tb_doctype');

    if ($q) {
        $builder->like('doc_type', $q);
    }

    $data = $builder->select('doc_type')->limit(20)->get()->getResultArray();

    return $this->response->setJSON($data);
}

}
