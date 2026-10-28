<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * ImageUploadService
 * 
 * Uses Cloudinary for persistent remote storage and local public storage
 * only in development environments.
 */
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

    /**
     * Upload an intercepted uploaded shoe image directly to Cloud Storage
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return array ['success' => bool, 'url' => string, 'error' => ?string]
     */
    public function upload(UploadedFile $file, string $folder = 'shoes'): array
    {
        if (!$file->isValid()) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => $file->getErrorString() . ' (' . $file->getError() . ')',
            ];
        }

        // Validate MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
        $mime = $file->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Invalid image type. Allowed: JPG, PNG, WEBP, AVIF.',
            ];
        }

        // Validate Size (Max 5MB)
        if ($file->getSizeByUnit('mb') > 5) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Image exceeds maximum 5MB size limit.',
            ];
        }

        // 1. Prefer Cloudinary Direct API Upload
        if (!empty($this->cloudName)) {
            return $this->uploadToCloudinary($file, $folder);
        }

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return $this->saveLocally($file, $folder, $mime);
        }

        return [
            'success' => false,
            'url'     => null,
            'error'   => 'Cloudinary is not configured. Local image storage is available only in development.',
        ];
    }

    private function saveLocally(UploadedFile $file, string $folder, string $mime): array
    {
        if (!preg_match('#^[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*$#', $folder)) {
            return ['success' => false, 'url' => null, 'error' => 'Invalid image storage folder.'];
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
        ];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $directory = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $folder);

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            return ['success' => false, 'url' => null, 'error' => 'Unable to create the local image upload directory.'];
        }

        try {
            $file->move($directory, $fileName);
        } catch (\Throwable $exception) {
            return ['success' => false, 'url' => null, 'error' => 'Unable to save image locally. Check that public/uploads is writable.'];
        }

        return [
            'success' => true,
            'url'     => base_url('uploads/' . $folder . '/' . rawurlencode($fileName)),
            'error'   => null,
        ];
    }

    /**
     * Streams file via cURL to Cloudinary REST API
     */
    protected function uploadToCloudinary(UploadedFile $file, string $folder): array
    {
        $apiUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $tempPath = $file->getTempName();
        $timestamp = time();

        $postFields = [
            'timestamp' => $timestamp,
            'folder'    => $folder,
        ];

        // If signed upload with API Secret
        if (!empty($this->apiSecret) && !empty($this->apiKey)) {
            $paramsToSign = "folder={$folder}&timestamp={$timestamp}" . $this->apiSecret;
            $signature = sha1($paramsToSign);

            $postFields['api_key']   = $this->apiKey;
            $postFields['signature'] = $signature;
        } elseif (!empty($this->uploadPreset)) {
            // Unsigned preset upload
            $postFields['upload_preset'] = $this->uploadPreset;
        }

        // Attach the binary stream from PHP's /tmp storage
        $postFields['file'] = new \CURLFile($tempPath, $file->getMimeType(), $file->getClientName());

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'url'     => null,
                'error'   => 'Cloudinary cURL Error: ' . $curlError,
            ];
        }

        $json = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($json['secure_url'])) {
            return [
                'success'   => true,
                'url'       => $json['secure_url'],
                'public_id' => $json['public_id'] ?? null,
                'error'     => null,
            ];
        }

        $errorMsg = $json['error']['message'] ?? ('HTTP ' . $httpCode . ' upload failed');
        return [
            'success' => false,
            'url'     => null,
            'error'   => 'Cloudinary Error: ' . $errorMsg,
        ];
    }

}
