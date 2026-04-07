<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
{
    if (session()->get('isLoggedIn')) {
        return redirect()->to('/dashboard');
    }

    return view('auth/login');
}

// public function login()
// {
//     echo '<pre>';
//     print_r(session()->get());
//     die();
// }



   public function attemptLogin()
{
    $userModel = new UserModel();

    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    // Allow login using uname OR name
    $user = $userModel
        ->groupStart()
            ->where('uname', $username)
            ->orWhere('name', $username)
        ->groupEnd()
        ->first();

    if (!$user) {
        return redirect()->back()->with('error', 'User not found');
    }

    if (!password_verify($password, $user['pass'])) {
        return redirect()->back()->with('error', 'Invalid password');
    }

    if ($user['status'] === 'inactive' && $user['role'] == "user") {
        return redirect()->back()->with('error', 'Account is inactive');
    }

    // ✅ Use uname if not empty, else use name
    if (!empty($user['uname'])) {
        $displayUsername = $user['uname'];
    } else {
        $displayUsername = $user['name'];
    }

    session()->set([
        'user_id'    => $user['id'],
        'username'   => $displayUsername,
        'name'       => $user['name'],
        'role'       => $user['role'],
        'isLoggedIn' => true
    ]);

    return redirect()->to('dashboard');
}


    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

}
