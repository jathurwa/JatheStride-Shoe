<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'email', 'password_hash', 'role', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
            unset($data['data']['password']);
        }
        return $data;
    }

    /**
     * Authenticate user against password_hash
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)->first();
        if (!$user) {
            return null;
        }

        if (password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }
}
