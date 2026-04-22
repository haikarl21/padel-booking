<?php
/**
 * ============================================
 * PADEL BOOKING - PROJECT SETUP SCRIPT
 * ============================================
 * 
 * Script ini untuk automasi setup project PWA + Midtrans di localhost
 * Jalankan: php setup-project.php
 * 
 * Checklist yang dilakukan:
 * ✓ Cek konfigurasi .env
 * ✓ Cek file PWA (service worker, manifest)
 * ✓ Cek database connectivity
 * ✓ Cek konfigurasi Midtrans
 * ✓ Cek folder permissions
 * ✓ Cek cache configuration
 * ✓ Generate app key jika belum
 */

echo "\n====================================\n";
echo "  PADEL BOOKING - PROJECT SETUP\n";
echo "====================================\n\n";

class ProjectSetup {
    protected $errors = [];
    protected $warnings = [];
    protected $success = [];
    
    public function run() {
        echo "[1/8] Checking .env file...\n";
        $this->checkEnvFile();
        
        echo "[2/8] Checking PWA files...\n";
        $this->checkPwaFiles();
        
        echo "[3/8] Checking database configuration...\n";
        $this->checkDatabase();
        
        echo "[4/8] Checking Midtrans configuration...\n";
        $this->checkMidtrans();
        
        echo "[5/8] Checking folder permissions...\n";
        $this->checkFolderPermissions();
        
        echo "[6/8] Checking cache and storage...\n";
        $this->checkCacheStorage();
        
        echo "[7/8] Checking Laravel app key...\n";
        $this->checkAppKey();
        
        echo "[8/8] Final checks...\n";
        $this->finalChecks();
        
        $this->displayResults();
    }
    
    protected function checkEnvFile() {
        if (!file_exists('.env')) {
            $this->errors[] = ".env file not found! Copy from .env.example first";
            return;
        }
        
        $env = file_get_contents('.env');
        
        // Check required variables
        $required = [
            'APP_NAME' => 'Application name',
            'APP_ENV' => 'Application environment (should be "local" for development)',
            'APP_DEBUG' => 'Debug mode (should be "true" for development)',
            'DB_HOST' => 'Database host',
            'DB_DATABASE' => 'Database name',
            'MIDTRANS_SERVER_KEY' => 'Midtrans server key',
            'MIDTRANS_CLIENT_KEY' => 'Midtrans client key',
        ];
        
        foreach ($required as $key => $desc) {
            if (preg_match("/^{$key}=/m", $env)) {
                $this->success[] = ".env: {$desc} is configured";
            } else {
                $this->warnings[] = ".env: {$desc} might not be configured";
            }
        }
        
        // Check APP_DEBUG
        if (preg_match('/APP_DEBUG=true/i', $env)) {
            $this->success[] = ".env: Debug mode is ON (good for development)";
        } else {
            $this->warnings[] = ".env: Debug mode is OFF (you should enable it for development)";
        }
        
        // Check APP_ENV
        if (preg_match('/APP_ENV=local/i', $env)) {
            $this->success[] = ".env: Environment is set to 'local'";
        } else {
            $this->warnings[] = ".env: Environment should be 'local' for development";
        }
    }
    
    protected function checkPwaFiles() {
        $pwaFiles = [
            'public/service-worker.js' => 'Service Worker',
            'public/manifest.json' => 'PWA Manifest',
            'public/offline.html' => 'Offline Fallback Page',
        ];
        
        foreach ($pwaFiles as $file => $name) {
            if (file_exists($file)) {
                $this->success[] = "PWA: {$name} exists";
                
                // Check file size
                $size = filesize($file);
                if ($size > 0) {
                    $this->success[] = "PWA: {$name} has content ({$size} bytes)";
                } else {
                    $this->errors[] = "PWA: {$name} is EMPTY!";
                }
            } else {
                $this->errors[] = "PWA: {$name} NOT FOUND at {$file}";
            }
        }
    }
    
    protected function checkDatabase() {
        // Load .env to get DB config
        $dotenv = parse_ini_file('.env', true);
        
        if (!isset($dotenv['DB_HOST']) || !isset($dotenv['DB_DATABASE'])) {
            $this->warnings[] = "Database: Could not parse .env database configuration";
            return;
        }
        
        $host = $dotenv['DB_HOST'] ?? '127.0.0.1';
        $db = $dotenv['DB_DATABASE'] ?? 'padel';
        $user = $dotenv['DB_USERNAME'] ?? 'root';
        $pass = $dotenv['DB_PASSWORD'] ?? '';
        
        try {
            $conn = @mysqli_connect($host, $user, $pass, $db);
            if ($conn) {
                $this->success[] = "Database: Connected successfully to '{$db}'";
                mysqli_close($conn);
            } else {
                $this->warnings[] = "Database: Could not connect (This might be OK if DB server is not running yet)";
            }
        } catch (Exception $e) {
            $this->warnings[] = "Database: Connection check skipped - " . $e->getMessage();
        }
    }
    
    protected function checkMidtrans() {
        $env = parse_ini_file('.env');
        
        if (isset($env['MIDTRANS_IS_PRODUCTION'])) {
            $isProduction = strtolower($env['MIDTRANS_IS_PRODUCTION']) === 'true';
            $env_name = $isProduction ? 'PRODUCTION' : 'SANDBOX';
            $this->success[] = "Midtrans: Mode set to {$env_name}";
            
            if (!$isProduction) {
                $this->success[] = "Midtrans: Using Sandbox (good for testing)";
            }
        }
        
        $keys = ['MIDTRANS_SERVER_KEY', 'MIDTRANS_CLIENT_KEY', 'MIDTRANS_MERCHANT_ID'];
        foreach ($keys as $key) {
            if (isset($env[$key]) && !empty($env[$key])) {
                // Show only first and last 5 chars for security
                $value = $env[$key];
                if (strlen($value) > 10) {
                    $masked = substr($value, 0, 5) . '***' . substr($value, -5);
                } else {
                    $masked = '***';
                }
                $this->success[] = "Midtrans: {$key} is configured ({$masked})";
            } else {
                $this->warnings[] = "Midtrans: {$key} is NOT configured!";
            }
        }
    }
    
    protected function checkFolderPermissions() {
        $folders = [
            'storage' => 'Storage folder',
            'storage/logs' => 'Logs folder',
            'storage/app' => 'App storage folder',
            'bootstrap/cache' => 'Cache folder',
            'public' => 'Public folder',
        ];
        
        foreach ($folders as $folder => $name) {
            if (is_dir($folder)) {
                if (is_writable($folder)) {
                    $this->success[] = "Permissions: {$name} is writable";
                } else {
                    $this->warnings[] = "Permissions: {$name} might not be writable";
                }
            } else {
                $this->errors[] = "Permissions: {$name} NOT FOUND";
            }
        }
    }
    
    protected function checkCacheStorage() {
        $env = parse_ini_file('.env');
        
        if (isset($env['CACHE_STORE'])) {
            $cache = $env['CACHE_STORE'];
            $this->success[] = "Cache: Store driver is '{$cache}'";
            
            if ($cache === 'database') {
                $this->success[] = "Cache: Using database driver (requires migrations)";
            }
        }
        
        if (isset($env['SESSION_DRIVER'])) {
            $session = $env['SESSION_DRIVER'];
            $this->success[] = "Session: Driver is '{$session}'";
        }
    }
    
    protected function checkAppKey() {
        $env = parse_ini_file('.env');
        
        if (isset($env['APP_KEY']) && !empty($env['APP_KEY'])) {
            $this->success[] = "App Key: Already generated";
        } else {
            $this->warnings[] = "App Key: NOT SET! Run: php artisan key:generate";
        }
    }
    
    protected function finalChecks() {
        // Check app.blade.php has PWA registration
        if (file_exists('resources/views/layouts/app.blade.php')) {
            $content = file_get_contents('resources/views/layouts/app.blade.php');
            
            if (strpos($content, 'service-worker') !== false) {
                $this->success[] = "Views: app.blade.php has Service Worker registration";
            } else {
                $this->warnings[] = "Views: app.blade.php might not have Service Worker registration";
            }
            
            if (strpos($content, 'manifest.json') !== false) {
                $this->success[] = "Views: app.blade.php has manifest link";
            } else {
                $this->warnings[] = "Views: app.blade.php might not have manifest link";
            }
        }
        
        // Check routes
        if (file_exists('routes/web.php')) {
            $content = file_get_contents('routes/web.php');
            
            if (strpos($content, 'midtrans') !== false || strpos($content, 'payment') !== false) {
                $this->success[] = "Routes: Payment routes are configured";
            } else {
                $this->warnings[] = "Routes: Payment routes might not be configured";
            }
        }
    }
    
    protected function displayResults() {
        echo "\n====================================\n";
        echo "  SETUP RESULTS\n";
        echo "====================================\n\n";
        
        if (!empty($this->success)) {
            echo "✓ SUCCESS (" . count($this->success) . "):\n";
            foreach ($this->success as $msg) {
                echo "  ✓ {$msg}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠ WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $msg) {
                echo "  ⚠ {$msg}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "✗ ERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $msg) {
                echo "  ✗ {$msg}\n";
            }
            echo "\n";
        }
        
        // Summary
        $totalIssues = count($this->errors) + count($this->warnings);
        
        if (count($this->errors) === 0) {
            echo "════════════════════════════════\n";
            echo "✓ PROJECT SETUP LOOKS GOOD!\n";
            echo "════════════════════════════════\n\n";
            
            echo "Next steps:\n";
            echo "1. php artisan migrate (run this if you haven't already)\n";
            echo "2. php artisan serve\n";
            echo "3. Open http://localhost:8000 in your browser\n";
            echo "4. Check browser console (F12) for any errors\n\n";
        } else {
            echo "════════════════════════════════\n";
            echo "✗ SETUP HAS {$totalIssues} ISSUE(S)\n";
            echo "════════════════════════════════\n\n";
            echo "Please fix the errors above before proceeding.\n\n";
        }
    }
}

// Run setup
$setup = new ProjectSetup();
$setup->run();
