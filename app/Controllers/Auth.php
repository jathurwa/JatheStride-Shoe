<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Show admin login form
     */
    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('admin/login', [
            'title' => 'Admin Portal Login | ApexStride',
        ]);
    }

    /**
     * Authenticate admin
     */
    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Check against UserModel
        $user = $this->userModel->verifyCredentials($email, $password);

        // Optional environment-provided admin bootstrap if the database hasn't been seeded yet
        $adminEmailEnv = getenv('ADMIN_EMAIL');
        $adminPassEnv  = getenv('ADMIN_PASSWORD');

        if (
            !$user
            && $adminEmailEnv !== false
            && $adminEmailEnv !== ''
            && $adminPassEnv !== false
            && $adminPassEnv !== ''
            && $email === $adminEmailEnv
            && is_string($password)
            && hash_equals($adminPassEnv, $password)
        ) {
            $user = [
                'id'    => 1,
                'name'  => 'Store Owner',
                'email' => $adminEmailEnv,
                'role'  => 'admin',
            ];
        }

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Invalid admin credentials entered.');
        }

        // Setup session
        session()->set([
            'is_logged_in' => true,
            'user_id'      => $user['id'],
            'user_name'    => $user['name'],
            'user_email'   => $user['email'],
            'user_role'    => $user['role'] ?? 'admin',
        ]);

        return redirect()->to('/admin')->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been securely logged out.');
    }
}
