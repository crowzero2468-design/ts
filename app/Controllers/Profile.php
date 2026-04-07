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



}
