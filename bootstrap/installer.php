<?php
// public/install.php

use SmartCms\Core\Actions\InitMenuSections;
use SmartCms\Core\Services\Frontend\LayoutService;
use SmartCms\Core\Services\Frontend\SectionService;

define('INSTALLER', true);
session_start();

// Redirect if already installed
if (file_exists(__DIR__ . '/../storage/installed')) {
    die('Application is already installed.');
}
$url = $_SERVER['REQUEST_URI'];
if ($url == '/phpinfo') {
    phpinfo();
    exit;
}
$step = $_GET['step'] ?? 1;
$step = (int) $step;
if ($step <= 1) {
    $step = 1;
}
$reqs = checkRequirements();
if (!empty($reqs['errors'])) {
    $step = 1;
}
switch ($step) {
    case 1:
        showRequirements();
        break;
    case 2:
        showEnvForm();
        break;
    case 3:
        showAdminForm();
        break;
    case 4:
        showFinish();
        break;
}
exit;
function renderLayout($content)
{
    $step1 = '';
    if ($GLOBALS['step'] == 1) {
        $step1 = '<h2 class="">System Requirements</h2>
            <p class="">Please ensure your server meets the following requirements:</p>';
    }
    $step2 = '';
    if ($GLOBALS['step'] == 2) {
        $step2 = '<h2 class="">Database Configuration</h2>
            <p class="">Please provide your database connection details:</p>';
    }
    $step3 = '';
    if ($GLOBALS['step'] == 3) {
        $step3 = '<h2 class="">Admin User Configuration</h2>
            <p class="">Please provide your admin user details:</p>';
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>SmartCMS Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #9ea9bf;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .install-wrap{
            width: 100%;
            max-width: 800px;
        }
        .wrap-heading{
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: #f0f4f8;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e4eb;
            margin-bottom: 20px;
        }
        .wrap-heading img{
            max-width: 180px;
            margin: 0 auto;
        }
        .wrap-content{
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e4eb;
            margin-bottom: 20px;
        }
        ul.param-list{
            list-style: none;
            margin: 0;
            padding: 0;
        }
        li{
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        li.error, div.error{
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 0 10px;
            border-radius: 5px;
        }
        .error span{
            color: red;
        }
        .success span{
            color: green;
        }
        .error span, .success span{
            font-size:36px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .button-group{
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .next-step{
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .retry{
            background: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .next-step:hover, .retry:hover{
            background: #0056b3;
            color: #fff;
        }
        .button-group .back-step{
            background: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
        }
        .button-group .back-step:hover{
            background: #5a6268;
        }
        .wrap-heading h1{
            font-size: 24px;
            margin: 10px 0;
        }
        .wrap-heading p{
            font-size: 16px;
            margin: 10px 0;
        }
        .wrap-content h2{
            font-size: 20px;
            margin: 10px 0;
        }
        .wrap-content p{
            font-size: 16px;
            margin: 10px 0;
        }
        form.step2{
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .step-field{
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .step-field label{
            width: 250px;
            margin-right: 10px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .step-field input, .step-field select{
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .valid {
            color: green;
        }
        .invalid {
            color: red;
        }
        .pass-req{
            width: 100%
        }
        .pass-req .requirements{
            display: flex;
            justify-content: space-around;
            margin-top: 5px;
        }
    </style>
    <script>
        function passwordValidation() {
            return {
                password: '',
                hasMinLength: false,
                hasUpperCase: false,
                hasNumber: false,
                // isValid: false,

                validatePassword() {
                    // Проверка минимальной длины
                    this.hasMinLength = this.password.length >= 8;

                    // Проверка наличия заглавной буквы
                    this.hasUpperCase = /[A-ZА-Я]/.test(this.password);

                    // Проверка наличия цифры
                    this.hasNumber = /[0-9]/.test(this.password);

                    // Общая валидность пароля
                    // this.isValid = this.hasMinLength && this.hasUpperCase && this.hasNumber;
                }
            }
        }
    </script>
</head>
<body>
    <div class="install-wrap">
        <div class="wrap-heading">
            <img src="https://divotek.com.ua/glide/storage/branding/01JMHQN7TJS154RYEC6VEDHPT3.webp" alt="Logo">
            <h1 class="">Welcome to the Smart CMS Installer</h1>
            <p class="">Please follow the steps to install the CMS.</p>
            <h2 class="text-xl font-semibold">Step {$GLOBALS['step']}</h2>
        </div>
        <div class="wrap-content">
            {$step1}
            {$step2}
            {$step3}
            {$content}
        </div>
    </div>
</body>
</html>
HTML;
}
function checkRequirements()
{
    $errors = [];
    $satisfied = [];
    if (version_compare(PHP_VERSION, '8.3.0', '<')) {
        $errors[] = "PHP 8.3 or higher is required.";
    } else {
        $satisfied[] = "PHP 8.3 or higher is installed.";
    }
    foreach (["gd", "intl", "mbstring", "fileinfo", "ctype", "curl", "dom", "filter", "pdo", "session", "tokenizer", "xml", "zip", "zlib",] as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "PHP extension {$ext} is required.";
        } else {
            $satisfied[] = "PHP extension {$ext} is installed.";
        }
    }
    if (!function_exists('imagewebp')) {
        $errors[] = "GD extension must support WebP.";
    } else {
        $satisfied[] = "GD extension supports WebP.";
    }
    if (!is_writable(__DIR__ . '/../storage') || !is_writable(__DIR__ . '/../bootstrap/cache')) {
        $errors[] = "Storage and bootstrap/cache directories must be writable.";
    } else {
        $satisfied[] = "Storage and bootstrap/cache directories are writable.";
    }
    return [
        'errors' => $errors,
        'satisfied' => $satisfied
    ];
}

function showRequirements()
{
    $requirements = checkRequirements();
    $errors = $requirements['errors'] ?? [];
    $satisfied = $requirements['satisfied'] ?? [];

    $errorList = "";
    foreach ($errors as $error) {
        $errorList .= "<li class='error'><span>&#9888;</span>{$error}</li>";
    }
    $satisfiedList = "";
    foreach ($satisfied as $satisfied) {
        $satisfiedList .= "<li class='success'><span>&#9745;</span>{$satisfied}</li>";
    }
    $content = "<ul class='param-list'>{$errorList}{$satisfiedList}</ul>";
    if (empty($errors)) {
        $content .= <<<HTML
        <div class="button-group">
            <a href='?step=2' class='next-step'>Continue</a>
        </div>
        HTML;
    } else {
        $content .= <<<HTML
        <div class="button-group">
            <a href='?step=1' class='retry'>Retry</a>
        </div>
        HTML;
    }

    renderLayout($content);
}

function showEnvForm()
{
    $env = $_SESSION['env'] ?? [];
    if (isset($env['appUrl'])) {
        $appUrl = $env['appUrl'];
    } else {
        $appUrl = ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://';
        $appUrl .= $_SERVER['HTTP_HOST'];
    }
    $dbType = $env['dbType'] ?? 'sqlite';
    $dbHost = $env['dbHost'] ?? '127.0.0.1';
    $dbName = $env['dbName'] ?? '';
    $dbUser = $env['dbUser'] ?? '';
    $dbPass = $env['dbPass'] ?? '';
    $siteName = $env['siteName'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $appUrl = $_POST['app_url'] ?? $appUrl;
        $dbType = $_POST['db_type'] ?? $dbType;
        $dbHost = $_POST['db_host'] ?? $dbHost;
        $dbName = $_POST['db_name'] ?? $dbName;
        $dbUser = $_POST['db_user'] ?? $dbUser;
        $dbPass = $_POST['db_pass'] ?? $dbPass;
        $siteName = $_POST['site_name'] ?? $siteName;
        $env = $_SESSION['env'] ?? [];
        $env = array_merge($env, compact('appUrl', 'dbType', 'dbHost', 'dbName', 'dbUser', 'dbPass', 'siteName'));
        $_SESSION['env'] = $env;
        if ($dbName == '') {
            $error = 'Database name is required.';
            header('Location: ?step=2&error=' . urlencode($error));
            exit;
        }
        try {
            if ($dbType == 'mysql') {
                new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
            }
        } catch (\Exception $e) {
            $error = 'Could not connect to database. Please check your credentials and try again.';
            header('Location: ?step=2&error=' . urlencode($error));
            exit;
        }
        header('Location: ?step=3');
        exit;
    }

    $form = <<<HTML
<form class="step2" method="POST" x-data="{
    dbType: '{$dbType}',
    dbHost: '{$dbHost}',
    dbName: '{$dbName}',
    dbUser: '{$dbUser}',
    dbPass: '{$dbPass}'
}">
    <div x-data="{error:'',isError:false}" x-init="function(){
        const paramsString = window.location.search;
        const searchParams = new URLSearchParams(paramsString);
        this.error = searchParams.get('error');
        this.isError = this.error ? true : false;
        }" x-show="isError" class="error"><span>⚠</span>
        <b x-text="error"></b>
    </div>
    <div class="step-field">
        <label>Site URL</label>
        <input name="app_url" value="{$appUrl}" required>
    </div>
    <div class="step-field">
        <label>Site Name</label>
        <input name="site_name" value="{$siteName}" required>
    </div>
    <div class="step-field">
        <label>Database Type</label>
        <select name="db_type" class="" x-model="dbType" required>
            <option value="mysql">MySQL</option>
            <option value="sqlite">SQLite</option>
        </select>
    </div>
    <div class="step-field" x-show="dbType === 'mysql'">
        <label class="">Database Host</label>
        <input name="db_host" value="{$dbHost}" class="">
    </div>
    <div class="step-field" x-show="dbType === 'mysql'">
        <label class="">Database Name</label>
        <input name="db_name" value="{$dbName}" class="">
    </div>
    <div class="step-field" x-show="dbType === 'mysql'">
        <label class="">Database User</label>
        <input name="db_user" value="{$dbUser}" class="">
    </div>
    <div class="step-field" x-show="dbType === 'mysql'">
        <label class="block mb-1">Database Password</label>
        <input name="db_pass" value="{$dbPass}" type="password" class="">
    </div>

    <div class="button-group">
        <a href="?step=1" class="back-step">Back</a>
        <button type="submit" class="next-step">Continue</button>
    </div>
</form>
HTML;

    renderLayout($form);
}

function updateEnvValue(string $key, string $value): void
{
    $path = __DIR__ . '/../.env';
    $env = file_get_contents($path);

    // Match the key with or without comment, replace the whole line
    $pattern = "/^#?{$key}=.*$/m";

    if (preg_match($pattern, $env)) {
        // Replace existing (even if commented)
        $env = preg_replace($pattern, "{$key}={$value}", $env);
    } else {
        // Append to end if not found
        $env .= PHP_EOL . "{$key}={$value}";
    }

    file_put_contents($path, $env);
}

function showAdminForm()
{
    $env = $_SESSION['env'];
    $name = $_POST['name'] ?? $env['name'] ?? '';
    $email = $_POST['email'] ?? $env['email'] ?? '';
    $password = $_POST['password'] ?? $env['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? $env['password_confirmation'] ?? '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($passwordConfirmation != $password) {
            $error = '<div class="error"><span>⚠</span>Passwords do not match</div>';
            $form = getAdminForm($name, $email, $password, $passwordConfirmation, $error);
            renderLayout($form);
            exit;
        }
        if (strlen($password) < 8) {
            $error = '<div class="error"><span>⚠</span>Password must be at least 8 characters long</div>';
            $form = getAdminForm($name, $email, $password, $passwordConfirmation, $error);
            renderLayout($form);
            exit;
        }
        $IsDigits = preg_match('/[0-9]/', $password);
        $IsLower = preg_match('/[a-z]/', $password);
        $IsUpper = preg_match('/[A-Z]/', $password);
        if (!$IsDigits || !$IsLower || !$IsUpper) {
            $error = '<div class="error"><span>⚠</span>Password must contain at least one digit, one lowercase letter, and one uppercase letter</div>';
            $form = getAdminForm($name, $email, $password, $passwordConfirmation, $error);
            renderLayout($form);
            exit;
        }
        try {
            // Ensure storage directories exist and are writable
            $storagePath = __DIR__ . '/../storage';
            $cachePath = __DIR__ . '/../bootstrap/cache';
            $logsPath = $storagePath . '/logs';

            if (!is_dir($logsPath)) {
                mkdir($logsPath, 0755, true);
            }

            // Set proper permissions
            chmod($storagePath, 0755);
            chmod($cachePath, 0755);
            chmod($logsPath, 0755);

            // Create SQLite database file if using SQLite
            if ($env['dbType'] === 'sqlite') {
                $dbPath = __DIR__ . '/../database/database.sqlite';
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                    chmod($dbPath, 0644);
                }
            }
            $envExample = file_get_contents(__DIR__ . '/../.env.example');
            file_put_contents(__DIR__ . '/../.env', $envExample);
            updateEnvValue('APP_NAME', preg_replace('/\s+/', '', $env['siteName']));
            updateEnvValue('APP_URL', preg_replace('/\s+/', '', $env['appUrl']));
            updateEnvValue('DB_CONNECTION', preg_replace('/\s+/', '', $env['dbType']));
            if ($env['dbType'] == 'mysql') {
                updateEnvValue('DB_HOST', preg_replace('/\s+/', '', $env['dbHost']));
                updateEnvValue('DB_DATABASE', preg_replace('/\s+/', '', $env['dbName']));
                updateEnvValue('DB_USERNAME', preg_replace('/\s+/', '', $env['dbUser']));
                updateEnvValue('DB_PASSWORD', preg_replace('/\s+/', '', $env['dbPass']));
            }
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($host, ':') !== false) {
                $host = explode(':', $host)[0];
            }
            $host = str_replace('www.', '', $host);
            updateEnvValue('SESSION_DOMAIN', preg_replace('/\s+/', '', $host));
            $_SERVER['argv'] = ['artisan'];
            $_SERVER['argc'] = 1;
            require __DIR__ . '/../vendor/autoload.php';
            $app = require __DIR__ . '/../bootstrap/app.php';
            $app->loadEnvironmentFrom('.env');
            set_time_limit(0);
            ini_set('max_execution_time', 0);

            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
            $kernel->call('key:generate');
            $kernel->call('migrate:fresh', ['--force' => true]);
            $kernel->call('storage:link');

            // Create admin user
            $userModel = config('auth.providers.admin.model');
            $userModel::create([
                'username' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);
            file_put_contents(__DIR__ . '/../storage/installed', now());
            SectionService::make()->init();
            LayoutService::make()->init();
            InitMenuSections::run();
            setting([sconfig('company_name') => $env['siteName']]);
            header('Location: /admin');
            exit;
        } catch (\Exception $e) {
            $error = "Installation failed: " . $e->getMessage();
            $form = getAdminForm($name, $email, $password, $passwordConfirmation, $error);
            renderLayout($form);
            exit;
        }
    }

    $form = getAdminForm($name, $email, $password, $passwordConfirmation, $error);

    renderLayout($form);
}

function getAdminForm(string $name, string $email, string $password, string $passwordConfirmation, string $error): string
{
    return <<<HTML
        <form class="step2" method="POST" >
                <div class="step-field">
                    <label>Admin Username</label>
                    <input name="name" value="{$name}">
                </div>
                <div class="step-field">
                    <label>Admin Email</label>
                    <input name="email" value="{$email}">
                </div>
                <div x-data="passwordValidation()" class="step-field">
                    <label>Password</label>

                    <div class="pass-req">
                        <input name="password" value="{$password}" type="password" id="password" x-model="password" x-on:input="validatePassword()">

                        <div class="requirements">
                            <div class="requirement" :class="hasMinLength ? 'valid' : 'invalid'">
                                ✓ Min 8 characters
                            </div>
                            <div class="requirement" :class="hasUpperCase ? 'valid' : 'invalid'">
                                ✓ Different registers
                            </div>
                            <div class="requirement" :class="hasNumber ? 'valid' : 'invalid'">
                                ✓ Numbers
                            </div>
                        </div>
                    </div>


                </div>

                <div class="step-field">
                    <label>Repeat password</label>
                    <input name="password_confirmation" type="password">
                </div>
                {$error}
                <div class="button-group">
                    <a href="?step=2" class="back-step">Back</a>
                    <button type="submit" @click="validateForm" class="next-step">Install</button>
                </div>
            </form>

        HTML;
}

function showFinish()
{
    $content = "<p class='text-green-700'>CMS installed successfully! You may now delete <code>install.php</code>.</p><a href='/' class='inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded'>Go to site</a>";
    renderLayout($content);
}
if ($step == 1) {
    showRequirements();
    exit;
}
if ($step == 2) {
    showEnvForm();
    exit;
}
if ($step == 3) {
    showAdminForm();
    exit;
}
if ($step == 4) {
    showFinish();
    exit;
}
showRequirements();
