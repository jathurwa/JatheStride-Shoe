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
            return [
                'success' => false,
                'url'     => null,
                'error'   => $file->getErrorString(),
            ];
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'];
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes, true)) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Only JPG, PNG, WEBP, AVIF, and GIF images are allowed.',
            ];
        }

        if (!empty($this->cloudName)) {
            return $this->uploadToCloudinary($file, $folder);
        }

        if (getenv('VERCEL') || getenv('VERCEL_ENV')) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Cloudinary is not configured. Set CLOUDINARY_CLOUD_NAME and either CLOUDINARY_API_KEY plus CLOUDINARY_API_SECRET, or CLOUDINARY_UPLOAD_PRESET, in Vercel.',
            ];
        }

        $localResult = $this->uploadToLocalDisk($file, $folder);
        if ($localResult['success']) {
            return $localResult;
        }

        return [
            'success' => false,
            'url'     => null,
            'error'   => $localResult['error'] . ' Configure Cloudinary or provide a direct image URL.',
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
        if (!function_exists('curl_init') || !class_exists(\CURLFile::class)) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'The server is missing the cURL extension required for Cloudinary uploads.',
            ];
        }

        $apiUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $tempPath = $file->getTempName();
        $timestamp = time();

        if (!empty($this->apiSecret) && !empty($this->apiKey)) {
            $postFields = [
                'timestamp' => $timestamp,
                'folder'    => $folder,
            ];
            $postFields['api_key']   = $this->apiKey;
            $postFields['signature'] = sha1("folder={$folder}&timestamp={$timestamp}" . $this->apiSecret);
        } elseif (!empty($this->uploadPreset)) {
            $postFields = ['upload_preset' => $this->uploadPreset];
        } else {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Cloudinary credentials are incomplete. Configure both CLOUDINARY_API_KEY and CLOUDINARY_API_SECRET, or set CLOUDINARY_UPLOAD_PRESET.',
            ];
        }

        $postFields['file'] = new \CURLFile($tempPath, $file->getMimeType(), $file->getClientName());

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($json['secure_url'])) {
            return [
                'success'   => true,
                'url'       => $json['secure_url'],
                'public_id' => $json['public_id'] ?? null,
                'error'     => null,
            ];
        }

        return [
            'success' => false,
            'url'     => null,
            'error'   => $json['error']['message'] ?? ($curlError !== '' ? 'Cloudinary request failed: ' . $curlError : 'Cloudinary upload failed.'),
        ];
    }
}
