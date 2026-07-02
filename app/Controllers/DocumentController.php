<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use Config\Database;

class DocumentController extends BaseController
{
    public function index()
    {
        return view('admin/document');
    }

    public function getData()
    {
        $model = new DocumentModel();

        $builder = $model;

        if ($this->request->getGet('type')) {
            $builder->like('type', $this->request->getGet('type'));
        }

        if ($this->request->getGet('title')) {
            $builder->like('title', $this->request->getGet('title'));
        }

        if ($this->request->getGet('receivedby')) {
            $builder->like('receivedby', $this->request->getGet('receivedby'));
        }

        $data = $builder->findAll();

        return $this->response->setJSON([
            'data' => $data
        ]);
    }

    public function add()
    {
        $model = new DocumentModel();
        $db = Database::connect();

        $type = trim($this->request->getPost('type'));

        // Save new document type automatically
        $exists = $db->table('tb_doctype')
            ->where('doc_type', $type)
            ->countAllResults();

        if ($exists == 0 && !empty($type)) {
            $db->table('tb_doctype')->insert([
                'doc_type' => $type
            ]);
        }

        $model->insert([
            'type'       => $type,
            'title'      => $this->request->getPost('title'),
            'receivedby' => $this->request->getPost('receivedby'),
            'sendby'     => $this->request->getPost('sendby'),
            'shelf'      => $this->request->getPost('shelf'),
            'status'     => $this->request->getPost('status'),
            'remarks'    => $this->request->getPost('remarks')
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Document added successfully.'
        ]);
    }

    public function edit($id)
    {
        $model = new DocumentModel();

        return $this->response->setJSON(
            $model->find($id)
        );
    }

    public function update()
    {
        $model = new DocumentModel();
        $db = Database::connect();

        $id = $this->request->getPost('id');
        $type = trim($this->request->getPost('type'));

        // Save new document type automatically
        $exists = $db->table('tb_doctype')
            ->where('doc_type', $type)
            ->countAllResults();

        if ($exists == 0 && !empty($type)) {
            $db->table('tb_doctype')->insert([
                'doc_type' => $type
            ]);
        }

        $model->update($id, [
            'type'       => $type,
            'title'      => $this->request->getPost('title'),
            'receivedby' => $this->request->getPost('receivedby'),
            'sendby'     => $this->request->getPost('sendby'),
            'shelf'      => $this->request->getPost('shelf'),
            'status'     => $this->request->getPost('status'),
            'remarks'    => $this->request->getPost('remarks')
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Document updated successfully.'
        ]);
    }

    public function delete()
    {
        $model = new DocumentModel();

        $model->delete(
            $this->request->getPost('id')
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Document deleted successfully.'
        ]);
    }
}