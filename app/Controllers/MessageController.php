<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MessageModel;
use App\Models\UserModel;

class MessageController extends BaseController
{
    protected $messageModel;
    protected $userModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->userModel    = new UserModel();
    }

    public function index($receiver = null)
    {
        $currentUser = session()->get('name');

        if (!$currentUser) {
            return redirect()->to('/login');
        }

        /* ===============================
           GET USERS
        =============================== */
        $users = $this->userModel->findAll();

        /* ===============================
           MOVE USERS WITH UNREAD ON TOP
        =============================== */
        $unreadSenders = $this->messageModel
            ->select('sender')
            ->where('receiver', $currentUser)
            ->where('is_read', 0)
            ->groupBy('sender')
            ->findAll();

        $unreadNames = array_column($unreadSenders, 'sender');

        usort($users, function ($a, $b) use ($unreadNames) {

            $aUnread = in_array($a['name'], $unreadNames);
            $bUnread = in_array($b['name'], $unreadNames);

            if ($aUnread && !$bUnread) return -1;
            if (!$aUnread && $bUnread) return 1;

            return 0;
        });

        $messages = [];

        /* ===============================
           LOAD CHAT IF RECEIVER EXISTS
        =============================== */
        if ($receiver) {

            // Mark as read
            $this->messageModel
                ->where('sender', $receiver)
                ->where('receiver', $currentUser)
                ->set(['is_read' => 1])
                ->update();

            $messages = $this->messageModel
                ->groupStart()
                    ->where('sender', $currentUser)
                    ->where('receiver', $receiver)
                ->groupEnd()
                ->orGroupStart()
                    ->where('sender', $receiver)
                    ->where('receiver', $currentUser)
                ->groupEnd()
                ->orderBy('id', 'ASC')
                ->findAll();
        }

        return view('message/index', [
            'users'       => $users,
            'messages'    => $messages,
            'receiver'    => $receiver,
            'currentUser' => $currentUser
        ]);
    }

    /* ===============================
       SEND MESSAGE
    =============================== */
    public function send()
    {
        $sender   = session()->get('name');
        $receiver = $this->request->getPost('receiver');

        $this->messageModel->save([
            'message'  => $this->request->getPost('message'),
            'sender'   => $sender,
            'receiver' => $receiver,
            'is_read'  => 0
        ]);

        // Stop typing
        $this->userModel
            ->where('name', $sender)
            ->set(['is_typing' => null])
            ->update();

        return $this->response->setJSON(['status' => 'sent']);
    }

    /* ===============================
       FETCH MESSAGES
    =============================== */
    public function fetch($receiver)
    {
        $currentUser = session()->get('name');

        // Mark as read
        $this->messageModel
            ->where('sender', $receiver)
            ->where('receiver', $currentUser)
            ->set(['is_read' => 1])
            ->update();

        $messages = $this->messageModel
            ->groupStart()
                ->where('sender', $currentUser)
                ->where('receiver', $receiver)
            ->groupEnd()
            ->orGroupStart()
                ->where('sender', $receiver)
                ->where('receiver', $currentUser)
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->response->setJSON($messages);
    }

    /* ===============================
       UNREAD PER USER
    =============================== */
    public function unreadCount()
    {
        $currentUser = session()->get('name');

        $counts = $this->messageModel
            ->select('sender, COUNT(*) as total')
            ->where('receiver', $currentUser)
            ->where('is_read', 0)
            ->groupBy('sender')
            ->findAll();

        return $this->response->setJSON($counts ?? []);
    }

    /* ===============================
       TOTAL UNREAD
    =============================== */
    public function unreadTotal()
    {
        $currentUser = session()->get('name');

        if (!$currentUser) {
            return $this->response->setJSON(['total' => 0]);
        }

        $total = $this->messageModel
            ->where('receiver', $currentUser)
            ->where('is_read', 0)
            ->countAllResults();

        return $this->response->setJSON(['total' => $total]);
    }

    /* ===============================
       TYPING
    =============================== */
    public function typing()
    {
        $receiver = $this->request->getPost('receiver');
        $sender   = session()->get('username');

        if (!$receiver || !$sender) {
            return $this->response->setJSON(['status' => false]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('typing_status');

        $builder->replace([
            'sender'     => $sender,
            'receiver'   => $receiver,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => true]);
    }



    public function checkTyping($receiver)
    {
        $currentUser = session()->get('username');

        $db = \Config\Database::connect();
        $builder = $db->table('typing_status');

        $builder->where('sender', $receiver);
        $builder->where('receiver', $currentUser);
        $builder->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-3 seconds')));

        $exists = $builder->countAllResults();

        return $this->response->setJSON([
            'typing' => $exists > 0
        ]);
    }


}
