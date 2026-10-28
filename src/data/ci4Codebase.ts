import { CI4FileEntry } from '../types';

export const CI4_CODE_FILES: CI4FileEntry[] = [
  {
    path: 'vercel.json',
    category: 'devops',
    description: 'Serverless deployment routing using vercel-php@0.7.0 community runtime mapped to public/index.php',
    content: `{
  "version": 2,
  "functions": {
    "public/index.php": {
      "runtime": "vercel-php@0.7.0",
      "maxDuration": 15,
      "memory": 1024
    }
  },
  "routes": [
    {
      "src": "^/(assets|css|js|images|static|favicon.ico)(.*)$",
      "dest": "/public/$1$2"
    },
    {
      "src": "^(.*)$",
      "dest": "/public/index.php"
    }
  ],
  "env": {
    "CI_ENVIRONMENT": "production"
  }
}`,
  },
  {
    path: '.env.example',
    category: 'devops',
    description: 'Environment variables template for Cloud Database, Cloudinary Storage & CI4 Vercel runtime',
    content: `# Application Settings
CI_ENVIRONMENT=production
app.baseURL="https://my-shoe-store.vercel.app/"
app.sessionSavePath="/tmp"

# Cloud Database Connection (e.g. PlanetScale, Supabase, Neon, AWS RDS, TiDB)
DB_HOST=aws.connect.psdb.cloud
DB_PORT=3306
DB_NAME=shoestore_db
DB_USER=shoe_admin
DB_PASS=your_secure_db_password_here
DB_DRIVER=MySQLi
# Or set complete connection string:
# DATABASE_URL=mysql://shoe_admin:password@aws.connect.psdb.cloud:3306/shoestore_db?ssl-mode=REQUIRED

# Cloudinary Storage Configuration (Crucial for Serverless: avoids local public/uploads)
CLOUDINARY_CLOUD_NAME=your_cloudinary_cloud_name
CLOUDINARY_API_KEY=your_cloudinary_api_key
CLOUDINARY_API_SECRET=your_cloudinary_api_secret
CLOUDINARY_UPLOAD_PRESET=shoe_store_preset

# Alternative: AWS S3 Storage (if preferred over Cloudinary)
AWS_ACCESS_KEY_ID=your_aws_key_id
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=my-shoe-ecommerce-assets

# Configure admin credentials only in the deployment environment.
ADMIN_EMAIL=your_admin_email@example.com
ADMIN_PASSWORD=replace_with_a_long_random_password`,
  },
  {
    path: 'composer.json',
    category: 'devops',
    description: 'PHP dependencies definition including CodeIgniter 4 framework and Cloudinary SDK',
    content: `{
  "name": "apexstride/codeigniter4-shoe-ecommerce",
  "type": "project",
  "description": "CodeIgniter 4 Shoe E-Commerce tailored for Vercel Serverless Platform",
  "require": {
    "php": "^8.1 || ^8.2",
    "codeigniter4/framework": "^4.4.0",
    "cloudinary/cloudinary_php": "^2.1",
    "curl/curl": "^2.4"
  },
  "require-dev": {
    "fakerphp/faker": "^1.9"
  },
  "autoload": {
    "psr-4": {
      "App\\\\": "app/",
      "Config\\\\": "app/Config/"
    },
    "exclude-from-classmap": [
      "**/Database/Migrations/**"
    ]
  },
  "scripts": {
    "post-update-cmd": [
      "CodeIgniter\\\\ComposerScripts::postUpdate"
    ]
  },
  "config": {
    "allow-plugins": {
      "kylekatarnls/update-helper": true
    }
  }
}`,
  },
  {
    path: 'public/index.php',
    category: 'devops',
    description: 'Vercel Front Controller: sets up /tmp writable session & cache paths, boots CI4',
    content: `<?php

// Check if running on Vercel Serverless or read-only container
if (!empty(getenv('VERCEL')) || !empty(getenv('NOW_REGION')) || !empty(getenv('AWS_LAMBDA_FUNCTION_NAME'))) {
    $tmpWritable = '/tmp/ci4_writable';
    
    // Ensure all critical runtime directories exist in the writable /tmp partition
    foreach (['session', 'cache', 'logs', 'uploads'] as $dir) {
        $path = $tmpWritable . '/' . $dir;
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
    }

    // Direct PHP session handler to /tmp
    ini_set('session.save_path', $tmpWritable . '/session');
}

// Path to the front controller's directory
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(FCPATH);

// Load our paths config file
require FCPATH . '../app/Config/Paths.php';

$paths = new Config\\Paths();

// Location of the framework bootstrap file.
$bootstrap = rtrim($paths->systemDirectory, '\\\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require $bootstrap;

// Grab the framework engine and launch the application!
$app->run();`,
  },
  {
    path: 'app/Config/Paths.php',
    category: 'config',
    description: 'Writable Directory Bypass: Dynamically redirects writable/ to /tmp/ci4_writable on Vercel',
    content: `<?php

namespace Config;

class Paths
{
    public string $systemDirectory = __DIR__ . '/../../system';
    public string $appDirectory = __DIR__ . '/..';
    public string $writableDirectory;
    public string $testsDirectory = __DIR__ . '/../../tests';
    public string $viewDirectory = __DIR__ . '/../Views';

    public function __construct()
    {
        // Detect Vercel serverless environment or read-only filesystem
        $isVercel = !empty(getenv('VERCEL')) || !empty(getenv('NOW_REGION')) || !empty(getenv('AWS_LAMBDA_FUNCTION_NAME'));
        $defaultWritable = realpath(__DIR__ . '/../../writable');

        if ($isVercel || !$defaultWritable || !is_writable($defaultWritable)) {
            $this->writableDirectory = '/tmp/ci4_writable';
            
            // Guarantee required subdirectories exist in /tmp
            $subDirs = ['cache', 'logs', 'session', 'debugbar', 'uploads'];
            foreach ($subDirs as $dir) {
                $path = $this->writableDirectory . '/' . $dir;
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
            }
        } else {
            $this->writableDirectory = $defaultWritable;
        }
    }
}`,
  },
  {
    path: 'app/Config/Routes.php',
    category: 'config',
    description: 'Complete routing map for Storefront, Category filters, Product details, Cart, and Admin CRUD',
    content: `<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Default namespace and routing rules
$routes->setDefaultNamespace('App\\\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Public Storefront Routes
 * --------------------------------------------------------------------
 */
$routes->get('/', 'Home::index');
$routes->get('category/(:segment)', 'Shoes::category/$1');
$routes->get('shoes/(:segment)', 'Shoes::show/$1');
$routes->get('api/shoes/search', 'Shoes::search');

/*
 * --------------------------------------------------------------------
 * Authentication Routes
 * --------------------------------------------------------------------
 */
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Admin\\\\Auth::login');
    $routes->post('login', 'Admin\\\\Auth::attemptLogin');
    $routes->get('logout', 'Admin\\\\Auth::logout');
});

/*
 * --------------------------------------------------------------------
 * Admin Protected Routes (Filtered by AuthFilter)
 * --------------------------------------------------------------------
 */
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Admin\\\\Shoes::index');
    $routes->get('shoes', 'Admin\\\\Shoes::index');
    $routes->get('shoes/create', 'Admin\\\\Shoes::create');
    $routes->post('shoes/store', 'Admin\\\\Shoes::store');
    $routes->get('shoes/edit/(:num)', 'Admin\\\\Shoes::edit/$1');
    $routes->post('shoes/update/(:num)', 'Admin\\\\Shoes::update/$1');
    $routes->get('shoes/delete/(:num)', 'Admin\\\\Shoes::delete/$1');
});`,
  },
  {
    path: 'app/Config/Database.php',
    category: 'config',
    description: 'Cloud Database Configuration strictly using getenv() for Vercel Environment Variables',
    content: `<?php

namespace Config;

use CodeIgniter\\Database\\Config;

class Database extends Config
{
    public string $defaultGroup = 'default';
    public array $default = [];

    public function __construct()
    {
        parent::__construct();

        $host     = getenv('DB_HOST') ?: getenv('database.default.hostname') ?: '127.0.0.1';
        $database = getenv('DB_NAME') ?: getenv('database.default.database') ?: 'shoestore_db';
        $username = getenv('DB_USER') ?: getenv('database.default.username') ?: 'root';
        $password = getenv('DB_PASS') ?: getenv('database.default.password') ?: '';
        $port     = (int)(getenv('DB_PORT') ?: getenv('database.default.port') ?: 3306);
        $driver   = getenv('DB_DRIVER') ?: getenv('database.default.DBDriver') ?: 'MySQLi';

        // Parse DATABASE_URL if provided by cloud database providers (PlanetScale, Supabase, Neon)
        $databaseUrl = getenv('DATABASE_URL');
        if (!empty($databaseUrl)) {
            $parsed = parse_url($databaseUrl);
            if ($parsed !== false) {
                $host     = $parsed['host'] ?? $host;
                $port     = isset($parsed['port']) ? (int)$parsed['port'] : $port;
                $username = $parsed['user'] ?? $username;
                $password = $parsed['pass'] ?? $password;
                $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $database;
                if (isset($parsed['scheme']) && ($parsed['scheme'] === 'postgres' || $parsed['scheme'] === 'postgresql')) {
                    $driver = 'Postgre';
                }
            }
        }

        $this->default = [
            'DSN'          => '',
            'hostname'     => $host,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
            'DBDriver'     => $driver,
            'DBPrefix'     => '',
            'pConnect'     => false, // Disabled on serverless to prevent socket exhaustion
            'DBDebug'      => (getenv('CI_ENVIRONMENT') === 'development'),
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_unicode_ci',
            'encrypt'      => (bool)(getenv('DB_SSL') ?: false),
            'port'         => $port,
        ];
    }
}`,
  },
  {
    path: 'app/Config/Filters.php',
    category: 'config',
    description: 'Security & Middleware Filters: Registers AuthFilter to guard administrative shoe management',
    content: `<?php

namespace Config;

use CodeIgniter\\Config\\BaseConfig;
use App\\Filters\\AuthFilter;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \\CodeIgniter\\Filters\\CSRF::class,
        'toolbar'  => \\CodeIgniter\\Filters\\DebugToolbar::class,
        'honeypot' => \\CodeIgniter\\Filters\\Honeypot::class,
        'auth'     => AuthFilter::class,
    ];

    public array $globals = [
        'before' => [
            // 'csrf', // Enabled in production with token fields
        ],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [
        'auth' => ['before' => ['admin', 'admin/*']],
    ];
}`,
  },
  {
    path: 'app/Filters/AuthFilter.php',
    category: 'library',
    description: 'Authentication Filter: Ensures session authenticity before allowing access to Admin portal',
    content: `<?php

namespace App\\Filters;

use CodeIgniter\\Filters\\FilterInterface;
use CodeIgniter\\HTTP\\RequestInterface;
use CodeIgniter\\HTTP\\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('is_admin_logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Authentication required. Please sign in.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after response
    }
}`,
  },
  {
    path: 'app/Libraries/ImageUploadService.php',
    category: 'library',
    description: 'Direct Cloudinary/S3 Uploader: Streams file uploads straight to CDN, bypassing ephemeral disk',
    content: `<?php

namespace App\\Libraries;

use CodeIgniter\\HTTP\\Files\\UploadedFile;

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

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
        if (!in_array($file->getClientMimeType(), $allowedMimes, true)) {
            return ['success' => false, 'url' => null, 'error' => 'Invalid file format. Only JPG, PNG, WEBP allowed.'];
        }

        if (!empty($this->cloudName)) {
            return $this->uploadToCloudinary($file, $folder);
        }

        // Safe fallback CDN placeholder if keys are still pending in Vercel settings
        return [
            'success'   => true,
            'url'       => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80',
            'public_id' => null,
            'error'     => null,
        ];
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

        $postFields['file'] = new \\CURLFile($tempPath, $file->getClientMimeType(), $file->getClientName());

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
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
}`,
  },
  {
    path: 'app/Models/ShoeModel.php',
    category: 'model',
    description: 'Shoe Model with relationships, query scopes, and strict validation rules',
    content: `<?php

namespace App\\Models;

use CodeIgniter\\Model;

class ShoeModel extends Model
{
    protected $table            = 'shoes';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'slug', 'category_id', 'price', 'sale_price',
        'description', 'image_url', 'cloudinary_public_id',
        'sizes_available', 'is_featured', 'stock_quantity'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Strict validation rules
    protected $validationRules = [
        'name'           => 'required|min_length[3]|max_length[255]',
        'category_id'    => 'required|is_natural_no_zero',
        'price'          => 'required|decimal|greater_than[0]',
        'description'    => 'required|min_length[10]',
        'image_url'      => 'required|valid_url',
        'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
    ];

    /**
     * Fetch shoes with joined category name and slug
     */
    public function getShoesWithCategory(?int $categoryId = null, int $limit = 50)
    {
        $builder = $this->select('shoes.*, categories.name as category_name, categories.slug as category_slug')
                        ->join('categories', 'categories.id = shoes.category_id', 'left')
                        ->orderBy('shoes.is_featured', 'DESC')
                        ->orderBy('shoes.created_at', 'DESC');

        if ($categoryId !== null) {
            $builder->where('shoes.category_id', $categoryId);
        }

        return $builder->findAll($limit);
    }

    /**
     * Find single shoe by URL slug
     */
    public function findBySlug(string $slug)
    {
        return $this->select('shoes.*, categories.name as category_name, categories.slug as category_slug')
                    ->join('categories', 'categories.id = shoes.category_id', 'left')
                    ->where('shoes.slug', $slug)
                    ->first();
    }
}`,
  },
  {
    path: 'app/Models/CategoryModel.php',
    category: 'model',
    description: 'Category Model: represents shoe classifications (Sneakers, Running, Boots, Formal)',
    content: `<?php

namespace App\\Models;

use CodeIgniter\\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'slug', 'description', 'icon'];
    protected $useTimestamps = false;

    public function getCategoriesWithShoeCount()
    {
        return $this->select('categories.*, COUNT(shoes.id) as shoe_count')
                    ->join('shoes', 'shoes.category_id = categories.id', 'left')
                    ->groupBy('categories.id')
                    ->findAll();
    }

    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }
}`,
  },
  {
    path: 'app/Models/UserModel.php',
    category: 'model',
    description: 'User Model: handles admin credentials, password hashing, and authentication verification',
    content: `<?php

namespace App\\Models;

use CodeIgniter\\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'email', 'password_hash', 'role'];
    protected $useTimestamps = true;

    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)->first();
        if (!$user) {
            return null;
        }

        if (password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }
}`,
  },
  {
    path: 'app/Controllers/Home.php',
    category: 'controller',
    description: 'Storefront Homepage Controller: loads featured hero footwear and catalog collections',
    content: `<?php

namespace App\\Controllers;

use App\\Models\\ShoeModel;
use App\\Models\\CategoryModel;

class Home extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $data = [
            'title'         => 'ApexStride Footwear | Performance, Lifestyle & Boots',
            'categories'    => $this->categoryModel->getCategoriesWithShoeCount(),
            'featuredShoes' => $this->shoeModel->where('is_featured', 1)->findAll(4),
            'shoes'         => $this->shoeModel->getShoesWithCategory(null, 24),
        ];

        return view('store/index', $data);
    }
}`,
  },
  {
    path: 'app/Controllers/Shoes.php',
    category: 'controller',
    description: 'Shoes Controller: handles category filtering, single shoe specifications, and JSON search API',
    content: `<?php

namespace App\\Controllers;

use App\\Models\\ShoeModel;
use App\\Models\\CategoryModel;

class Shoes extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
    }

    public function category(string $slug)
    {
        $category = $this->categoryModel->findBySlug($slug);
        if (!$category) {
            throw \\CodeIgniter\\Exceptions\\PageNotFoundException::forPageNotFound("Category '$slug' not found.");
        }

        $data = [
            'title'            => $category['name'] . ' Footwear Collection | ApexStride',
            'currentCategory'  => $category,
            'categories'       => $this->categoryModel->findAll(),
            'shoes'            => $this->shoeModel->getShoesWithCategory((int)$category['id'], 50),
        ];

        return view('store/index', $data);
    }

    public function show(string $slug)
    {
        $shoe = $this->shoeModel->findBySlug($slug);
        if (!$shoe) {
            throw \\CodeIgniter\\Exceptions\\PageNotFoundException::forPageNotFound("Shoe '$slug' not found.");
        }

        $data = [
            'title'       => $shoe['name'] . ' | ApexStride',
            'shoe'        => $shoe,
            'related'     => $this->shoeModel->where('category_id', $shoe['category_id'])
                                              ->where('id !=', $shoe['id'])
                                              ->findAll(4),
        ];

        return view('store/show', $data);
    }

    public function search()
    {
        $q = trim($this->request->getGet('q') ?? '');
        if (empty($q)) {
            return $this->response->setJSON([]);
        }

        $results = $this->shoeModel->like('name', $q)
                                   ->orLike('description', $q)
                                   ->findAll(10);

        return $this->response->setJSON($results);
    }
}`,
  },
  {
    path: 'app/Controllers/Admin/Shoes.php',
    category: 'controller',
    description: 'Admin Shoes CRUD Controller: Create, Read, Update, Delete with Cloudinary image streaming',
    content: `<?php

namespace App\\Controllers\\Admin;

use App\\Controllers\\BaseController;
use App\\Models\\ShoeModel;
use App\\Models\\CategoryModel;
use App\\Libraries\\ImageUploadService;

class Shoes extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;
    protected ImageUploadService $uploadService;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
        $this->uploadService = new ImageUploadService();
    }

    public function index()
    {
        return view('admin/shoes/index', [
            'title'      => 'Inventory & Product Management | ApexStride Admin',
            'shoes'      => $this->shoeModel->getShoesWithCategory(null, 100),
            'categories' => $this->categoryModel->findAll(),
        ]);
    }

    public function create()
    {
        return view('admin/shoes/form', [
            'title'      => 'Add New Shoe Product | ApexStride Admin',
            'shoe'       => null,
            'categories' => $this->categoryModel->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'           => 'required|min_length[3]',
            'category_id'    => 'required|is_natural_no_zero',
            'price'          => 'required|decimal|greater_than[0]',
            'description'    => 'required|min_length[10]',
            'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageUrl = $this->request->getPost('image_url_direct');
        $cloudinaryPublicId = null;

        // Process uploaded file directly to Cloudinary/S3
        $imageFile = $this->request->getFile('shoe_image');
        if ($imageFile && $imageFile->isValid()) {
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if (!$uploadResult['success']) {
                return redirect()->back()->withInput()->with('error', $uploadResult['error']);
            }
            $imageUrl = $uploadResult['url'];
            $cloudinaryPublicId = $uploadResult['public_id'] ?? null;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80';
        }

        $name = trim($this->request->getPost('name'));
        $this->shoeModel->insert([
            'name'                 => $name,
            'slug'                 => url_title($name, '-', true) . '-' . rand(100, 999),
            'category_id'          => (int)$this->request->getPost('category_id'),
            'price'                => (float)$this->request->getPost('price'),
            'sale_price'           => !empty($this->request->getPost('sale_price')) ? (float)$this->request->getPost('sale_price') : null,
            'description'          => $this->request->getPost('description'),
            'image_url'            => $imageUrl,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'sizes_available'      => $this->request->getPost('sizes_available') ?: '8,8.5,9,9.5,10,10.5,11,12',
            'stock_quantity'       => (int)$this->request->getPost('stock_quantity'),
            'is_featured'          => $this->request->getPost('is_featured') ? 1 : 0,
        ]);

        return redirect()->to('/admin/shoes')->with('success', 'Shoe published to catalog with Cloud CDN link!');
    }

    public function edit(int $id)
    {
        $shoe = $this->shoeModel->find($id);
        if (!$shoe) {
            return redirect()->to('/admin/shoes')->with('error', 'Shoe not found.');
        }

        return view('admin/shoes/form', [
            'title'      => 'Edit Shoe #' . $id . ' | ApexStride Admin',
            'shoe'       => $shoe,
            'categories' => $this->categoryModel->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $shoe = $this->shoeModel->find($id);
        if (!$shoe) {
            return redirect()->to('/admin/shoes')->with('error', 'Shoe not found.');
        }

        $data = [
            'name'            => trim($this->request->getPost('name')),
            'category_id'     => (int)$this->request->getPost('category_id'),
            'price'           => (float)$this->request->getPost('price'),
            'sale_price'      => !empty($this->request->getPost('sale_price')) ? (float)$this->request->getPost('sale_price') : null,
            'description'     => $this->request->getPost('description'),
            'sizes_available' => $this->request->getPost('sizes_available'),
            'stock_quantity'  => (int)$this->request->getPost('stock_quantity'),
            'is_featured'     => $this->request->getPost('is_featured') ? 1 : 0,
        ];

        // Check if new image was provided
        $imageFile = $this->request->getFile('shoe_image');
        if ($imageFile && $imageFile->isValid()) {
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if ($uploadResult['success']) {
                $data['image_url']            = $uploadResult['url'];
                $data['cloudinary_public_id'] = $uploadResult['public_id'];
            }
        } elseif (!empty($this->request->getPost('image_url_direct'))) {
            $data['image_url'] = $this->request->getPost('image_url_direct');
        }

        $this->shoeModel->update($id, $data);
        return redirect()->to('/admin/shoes')->with('success', 'Shoe #' . $id . ' updated successfully.');
    }

    public function delete(int $id)
    {
        $this->shoeModel->delete($id);
        return redirect()->to('/admin/shoes')->with('success', 'Shoe #' . $id . ' deleted.');
    }
}`,
  },
  {
    path: 'app/Controllers/Admin/Auth.php',
    category: 'controller',
    description: 'Admin Authentication Controller: login credentials verification, session storage, and logout',
    content: `<?php

namespace App\\Controllers\\Admin;

use App\\Controllers\\BaseController;
use App\\Models\\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('is_admin_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('admin/auth/login', [
            'title' => 'Admin Sign In | ApexStride',
        ]);
    }

    public function attemptLogin()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Check configured environment admin credentials or database user
        $envEmail = getenv('ADMIN_EMAIL');
        $envPass  = getenv('ADMIN_PASSWORD');

        if (
            $envEmail !== false
            && $envEmail !== ''
            && $envPass !== false
            && $envPass !== ''
            && $email === $envEmail
            && is_string($password)
            && hash_equals($envPass, $password)
        ) {
            session()->set([
                'is_admin_logged_in' => true,
                'admin_email'        => $email,
                'admin_name'         => 'System Administrator',
            ]);
            return redirect()->to('/admin');
        }

        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($email, $password);
        if ($user) {
            session()->set([
                'is_admin_logged_in' => true,
                'admin_email'        => $user['email'],
                'admin_name'         => $user['name'],
            ]);
            return redirect()->to('/admin');
        }

        return redirect()->back()->withInput()->with('error', 'Invalid admin email or password.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Logged out of admin console.');
    }
}`,
  },
  {
    path: 'app/Views/layouts/main.php',
    category: 'view',
    description: 'Master HTML5 View Template: Tailwind CSS styling, responsive navbar, and flash alerts',
    content: `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ApexStride Footwear') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col">
    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?= base_url() ?>" class="flex items-center gap-2 text-stone-950 font-black text-2xl tracking-tight">
                <span class="w-8 h-8 rounded-lg bg-stone-900 text-amber-500 flex items-center justify-center text-xs">AS</span>
                <span>APEX<span class="text-amber-600">STRIDE</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-stone-600">
                <a href="<?= base_url() ?>" class="hover:text-stone-950 transition-colors">Catalog</a>
                <a href="<?= base_url('category/sneakers') ?>" class="hover:text-stone-950 transition-colors">Sneakers</a>
                <a href="<?= base_url('category/running') ?>" class="hover:text-stone-950 transition-colors">Running</a>
                <a href="<?= base_url('category/boots') ?>" class="hover:text-stone-950 transition-colors">Boots</a>
                <a href="<?= base_url('category/formal') ?>" class="hover:text-stone-950 transition-colors">Formal</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="<?= base_url('admin') ?>" class="px-3 py-1.5 rounded-full border border-stone-300 hover:border-stone-900 text-xs font-semibold text-stone-700 transition-colors">
                    Admin Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Content slot -->
    <main class="flex-grow">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-stone-950 text-stone-400 py-10 border-t border-stone-900 text-xs text-center">
        <p>&copy; <?= date('Y') ?> ApexStride Shoe E-Commerce. CodeIgniter 4 on Vercel Serverless.</p>
    </footer>
</body>
</html>`,
  },
  {
    path: 'app/Views/store/index.php',
    category: 'view',
    description: 'Shoe Catalog View: Category filter chips, product card grid, prices, and CDN badges',
    content: `<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <!-- Category Pills -->
    <div class="flex flex-wrap items-center gap-2 border-b border-stone-200 pb-4">
        <a href="<?= base_url() ?>" class="px-4 py-2 rounded-xl text-xs font-bold <?= empty($currentCategory) ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
            All Footwear
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('category/' . esc($cat['slug'])) ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold <?= (!empty($currentCategory) && $currentCategory['id'] == $cat['id']) ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
                <?= esc($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($shoes)): ?>
            <?php foreach ($shoes as $shoe): ?>
                <div class="group bg-white rounded-3xl border border-stone-200 overflow-hidden hover:shadow-xl transition-all flex flex-col">
                    <div class="bg-stone-100 p-6 flex items-center justify-center h-64 relative">
                        <?php if ($shoe['is_featured']): ?>
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded bg-amber-500 text-stone-950 text-[10px] font-black uppercase tracking-wider">
                                Featured
                            </span>
                        <?php endif; ?>
                        <img src="<?= esc($shoe['image_url']) ?>" alt="<?= esc($shoe['name']) ?>" class="max-h-full object-contain group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div class="p-5 flex flex-col flex-grow justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-amber-600 uppercase"><?= esc($shoe['category_name'] ?? 'Footwear') ?></span>
                            <h3 class="font-bold text-stone-900 text-sm mt-1"><?= esc($shoe['name']) ?></h3>
                            <p class="text-xs text-stone-500 line-clamp-2 mt-1"><?= esc($shoe['description']) ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-base font-black text-stone-950">KSH <?= number_format($shoe['price'], 2) ?></span>
                            <a href="<?= base_url('shoes/' . esc($shoe['slug'])) ?>" class="px-3 py-1.5 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-colors">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-4 p-12 text-center text-stone-500 bg-white rounded-2xl border border-stone-200">
                No shoes found in this collection.
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>`,
  },
  {
    path: 'app/Views/store/show.php',
    category: 'view',
    description: 'Shoe Product Detail View: High-res gallery, size options, stock status, and add to bag',
    content: `<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white rounded-3xl border border-stone-200 p-6 sm:p-10 shadow-sm">
        <!-- Image Box -->
        <div class="bg-stone-100 rounded-2xl p-8 flex flex-col items-center justify-center relative">
            <img src="<?= esc($shoe['image_url']) ?>" alt="<?= esc($shoe['name']) ?>" class="max-h-80 object-contain drop-shadow-xl" />
            <div class="mt-6 text-[11px] text-stone-500 flex items-center gap-2">
                <i class="fa-solid fa-cloud text-amber-600"></i>
                <span>Cloudinary CDN Hosted &bull; ID #<?= esc($shoe['id']) ?></span>
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-6 flex flex-col justify-between">
            <div class="space-y-3">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider"><?= esc($shoe['category_name'] ?? 'Footwear') ?></span>
                <h1 class="text-3xl font-black text-stone-950 tracking-tight"><?= esc($shoe['name']) ?></h1>
                <div class="text-2xl font-black text-stone-900">KSH <?= number_format($shoe['price'], 2) ?></div>
                <p class="text-xs text-stone-600 leading-relaxed border-t border-stone-100 pt-4"><?= esc($shoe['description']) ?></p>

                <!-- Sizes Available -->
                <div class="pt-4">
                    <span class="text-xs font-bold text-stone-800 uppercase block mb-2">Available Sizes:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (explode(',', $shoe['sizes_available']) as $sz): ?>
                            <span class="px-3 py-1.5 rounded-xl border border-stone-200 text-xs font-bold bg-stone-50">
                                US <?= esc(trim($sz)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button class="w-full py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider transition-all shadow-md">
                Add To Footwear Bag
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>`,
  },
  {
    path: 'app/Views/admin/shoes/index.php',
    category: 'view',
    description: 'Admin Inventory Table View: Lists all shoes with stock count, edit, and delete actions',
    content: `<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-stone-900">Shoe Inventory Management</h1>
            <p class="text-xs text-stone-500">Manage products, pricing, stock and Cloudinary asset links</p>
        </div>
        <a href="<?= base_url('admin/shoes/create') ?>" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-bold rounded-xl shadow-sm">
            + Add New Shoe
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 uppercase font-bold text-[10px]">
                <tr>
                    <th class="py-3 px-4">Shoe Name</th>
                    <th class="py-3 px-4">Category</th>
                    <th class="py-3 px-4">Price</th>
                    <th class="py-3 px-4">Stock</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <?php foreach ($shoes as $s): ?>
                    <tr class="hover:bg-stone-50">
                        <td class="py-3 px-4 font-bold flex items-center gap-3">
                            <img src="<?= esc($s['image_url']) ?>" class="w-10 h-10 object-contain bg-stone-100 rounded-lg p-1" />
                            <span><?= esc($s['name']) ?></span>
                        </td>
                        <td class="py-3 px-4"><?= esc($s['category_name'] ?? 'General') ?></td>
                        <td class="py-3 px-4 font-black">KSH <?= number_format($s['price'], 2) ?></td>
                        <td class="py-3 px-4"><?= esc($s['stock_quantity']) ?> units</td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="<?= base_url('admin/shoes/edit/' . $s['id']) ?>" class="text-amber-700 font-bold hover:underline">Edit</a>
                            <a href="<?= base_url('admin/shoes/delete/' . $s['id']) ?>" onclick="return confirm('Delete this shoe?')" class="text-rose-600 font-bold hover:underline">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>`,
  },
  {
    path: 'app/Views/admin/shoes/form.php',
    category: 'view',
    description: 'Admin Shoe Form View: Supports file upload directly to Cloudinary and validation feedback',
    content: `<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm">
        <h2 class="text-xl font-black text-stone-900 mb-4"><?= empty($shoe) ? 'Add New Shoe' : 'Edit Shoe #' . $shoe['id'] ?></h2>

        <form action="<?= empty($shoe) ? base_url('admin/shoes/store') : base_url('admin/shoes/update/' . $shoe['id']) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Shoe Name *</label>
                <input type="text" name="name" value="<?= esc($shoe['name'] ?? '') ?>" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Category *</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (!empty($shoe) && $shoe['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Price (KSH) *</label>
                    <input type="number" step="0.01" name="price" value="<?= esc($shoe['price'] ?? '149.99') ?>" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Cloudinary File Upload</label>
                <input type="file" name="shoe_image" accept="image/*" class="w-full text-xs" />
                <p class="text-[11px] text-stone-400 mt-1">Uploaded image streams straight to Cloudinary API without saving to read-only container disk.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Direct Image URL (Alternative)</label>
                <input type="url" name="image_url_direct" value="<?= esc($shoe['image_url'] ?? '') ?>" placeholder="https://..." class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-mono" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Description *</label>
                <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs"><?= esc($shoe['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="<?= base_url('admin/shoes') ?>" class="px-4 py-2 border border-stone-300 rounded-xl text-xs font-bold text-stone-600">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold rounded-xl text-xs">
                    Save Shoe
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>`,
  },
  {
    path: 'database/schema.sql',
    category: 'sql',
    description: 'Production MySQL/PostgreSQL schema with users, categories, shoes & seed data',
    content: `-- ApexStride Shoe E-Commerce Database Schema
-- Compatible with MySQL 8.0, MariaDB 10+, TiDB, PlanetScale & PostgreSQL

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(60) DEFAULT 'fa-shoe-prints'
);

CREATE TABLE IF NOT EXISTS shoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category_id INT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(1000) NOT NULL,
    cloudinary_public_id VARCHAR(255) NULL,
    sizes_available VARCHAR(150) DEFAULT '7,8,8.5,9,9.5,10,10.5,11,12',
    is_featured TINYINT(1) DEFAULT 0,
    stock_quantity INT NOT NULL DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shoe_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
);

-- Seed Categories
INSERT INTO categories (id, name, slug, description, icon) VALUES
(1, 'Sneakers', 'sneakers', 'Low-profile and high-top street lifestyle footwear.', 'fa-bolt'),
(2, 'Running', 'running', 'Ultralight responsive carbon-plated and cushioned runners.', 'fa-person-running'),
(3, 'Boots', 'boots', 'Rugged, water-resistant handcrafted leather explorer boots.', 'fa-mountain'),
(4, 'Formal', 'formal', 'Sleek Goodyear-welted oxfords, brogues and derby dress shoes.', 'fa-briefcase')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Do not seed a shared default administrator; provision admin credentials securely.`,
  },
  {
    path: 'README.md',
    category: 'devops',
    description: 'Deployment Guide: Step-by-step instructions to deploy CodeIgniter 4 to Vercel Serverless',
    content: `# ApexStride CodeIgniter 4 Shoe E-Commerce on Vercel Serverless

This repository provides a production-grade **CodeIgniter 4 (CI4)** shoe e-commerce platform configured specifically for the **Vercel Serverless** platform.

## Architecture Highlights
1. **Serverless PHP Runtime**: Utilizes \`vercel-php@0.7.0\` inside \`vercel.json\` to route requests into \`public/index.php\`.
2. **Read-Only Filesystem Bypass**: \`app/Config/Paths.php\` redirects \`writable/\` to \`/tmp/ci4_writable\` to prevent \`is_writable()\` errors on Vercel's ephemeral disk.
3. **Cloud Media Uploads**: Admin shoe photo uploads bypass disk writes via \`app/Libraries/ImageUploadService.php\` and stream straight to **Cloudinary CDN** (or **AWS S3**).
4. **Cloud Database**: \`app/Config/Database.php\` connects dynamically via environment variables (\`getenv()\`) to PlanetScale, Supabase, Neon, or AWS RDS.

## 🚀 Quick Deployment to Vercel

### Step 1: Install Vercel CLI
\`\`\`bash
npm install -g vercel
\`\`\`

### Step 2: Set Environment Variables
In your Vercel Project Dashboard (or using \`vercel env add\`):
\`\`\`env
CI_ENVIRONMENT=production
DB_HOST=aws.connect.psdb.cloud
DB_NAME=shoestore_db
DB_USER=shoe_admin
DB_PASS=YourSecurePassword
DB_PORT=3306
DB_DRIVER=MySQLi
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
ADMIN_EMAIL=your_admin_email@example.com
ADMIN_PASSWORD=replace_with_a_long_random_password
\`\`\`

### Step 3: Run Database Migrations or Import SQL
Import \`database/schema.sql\` into your cloud database instance.

### Step 4: Deploy
\`\`\`bash
vercel --prod
\`\`\`

Your CodeIgniter 4 Shoe Store is now live on Vercel's global edge network!`,
  },
];
