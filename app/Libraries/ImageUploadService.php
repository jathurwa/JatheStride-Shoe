<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class ImageUploadService
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $uploadPreset;

    public function __construct()
    {
        $this->cloudName    = (string)(getenv('CLOUDINARY_CLOUD_NAME') ?: '');
        $this->apiKey       = (string)(getenv('CLOUDINARY_API_KEY') ?: '');
        $this->apiSecret    = (string)(getenv('CLOUDINARY_API_SECRET') ?: '');
        $this->uploadPreset = (string)(getenv('CLOUDINARY_UPLOAD_PRESET') ?: '');
    }

    public function upload(UploadedFile $file, string $folder = 'shoes'): array
    {
        if (!$file->isValid()) {
            return ['success' => false, 'url' => null, 'error' => $file->getErrorString()];
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'];
        if (!in_array($file->getClientMimeType(), $allowedMimes, true)) {
            return ['success' => false, 'url' => null, 'error' => 'Invalid file format. Only JPG, PNG, WEBP, and AVIF allowed.'];
        }

        // 1. Cloudinary upload (works on Vercel Serverless)
        if (!empty($this->cloudName)) {
            return $this->uploadToCloudinary($file, $folder);
        }

        // 2. Local uploads if running locally on PC with writable disk (XAMPP / Spark)
        $localResult = $this->uploadToLocalDisk($file, $folder);
        if ($localResult['success']) {
            return $localResult;
        }

        // 3. Clear message on Vercel when Cloudinary keys haven't been added yet
        return [
            'success' => false,
            'url'     => null,
            'error'   => 'Vercel filesystem is read-only. To upload files directly from your PC on Vercel, add your free CLOUDINARY_CLOUD_NAME in Vercel Settings > Environment Variables, OR paste an image link in the "Direct Image URL" field.',
        ];
    }

    protected function uploadToLocalDisk(UploadedFile $file, string $folder): array
    {
        $baseUploadDir = defined('FCPATH') ? FCPATH . 'uploads' : ROOTPATH . 'public/uploads';
        $targetDir = $baseUploadDir . DIRECTORY_SEPARATOR . $folder;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        if (is_dir($targetDir) && is_writable($targetDir)) {
            $newName = $file->getRandomName();
            if ($file->move($targetDir, $newName)) {
                return [
                    'success'   => true,
                    'url'       => base_url('uploads/' . $folder . '/' . $newName),
                    'public_id' => null,
                    'error'     => null,
                ];
            }
        }

        return ['success' => false, 'url' => null, 'error' => 'Local directory is not writable.'];
    }

    protected function uploadToCloudinary(UploadedFile $file, string $folder): array
    {
        $apiUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $tempPath = $file->getTempName();
        $timestamp = time();

        $postFields = [
            'timestamp' => $timestamp,
            'folder'    => $folder,
        ];

        if (!empty($this->apiSecret) && !empty($this->apiKey)) {
            $postFields['api_key']   = $this->apiKey;
            $postFields['signature'] = sha1("folder={$folder}&timestamp={$timestamp}" . $this->apiSecret);
        } elseif (!empty($this->uploadPreset)) {
            $postFields['upload_preset'] = $this->uploadPreset;
        }

        $postFields['file'] = new \CURLFile($tempPath, $file->getClientMimeType(), $file->getClientName());

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($json['secure_url'])) {
            return [
                'success'   => true,
                'url'       => $json['secure_url'],
                'public_id' => $json['public_id'] ?? null
            ];
        }

        return [
            'success' => false,
            'url'     => null,
            'error'   => $json['error']['message'] ?? 'Cloudinary upload failed.'
        ];
    }
}
