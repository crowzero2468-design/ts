<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class LogoutController extends BaseController
{
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
