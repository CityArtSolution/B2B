<?php return array (
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'acl' => 
  array (
    'permissions' => 
    array (
      'adminMultiShop' => 
      array (
        'shop' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'status.toggle',
          4 => 'show',
          5 => 'orders',
          6 => 'products',
          7 => 'reset.password',
        ),
        'product' => 
        array (
          0 => 'index',
          1 => 'approve',
          2 => 'show',
          3 => 'destroy',
        ),
        'coupon' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
        ),
        'withdraw' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'show',
        ),
        'subscription-plan' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
          5 => 'subscription.list',
          6 => 'subscription.status',
        ),
      ),
      'admin' => 
      array (
        'dashboard' => 
        array (
          0 => 'index',
          1 => 'notification',
        ),
        'banner' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'ad' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'order' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'status.change',
          3 => 'payment.status.toggle',
          4 => 'assign.rider',
        ),
        'review' => 
        array (
          0 => 'index',
          1 => 'toggle',
        ),
        'brand' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'color' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'size' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'unit' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'category' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'subcategory' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'flashSale' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
          4 => 'toggle',
        ),
        'generale-setting' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'business-setting' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'verification' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'socialLink' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'toggle',
        ),
        'socialAuth' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'toggle',
        ),
        'menu' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'remove',
          4 => 'destroy',
        ),
        'page' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'show',
          4 => 'destroy',
          5 => 'generate.AI.data',
        ),
        'country' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
        ),
        'currency' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'themeColor' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'change',
        ),
        'deliveryCharge' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
        ),
        'pusher' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'mailConfig' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'paymentGateway' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'toggle',
        ),
        'sms-gateway' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'googleReCaptcha' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'contactUs' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'firebase' => 
        array (
          0 => 'index',
          1 => 'update',
        ),
        'profile' => 
        array (
          0 => 'index',
          1 => 'update',
          2 => 'change-password',
        ),
        'rider' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'show',
          3 => 'edit',
          4 => 'destroy',
          5 => 'toggle',
          6 => 'assign.order',
        ),
        'customer' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'show',
          3 => 'edit',
          4 => 'destroy',
          5 => 'toggle',
          6 => 'reset.password',
        ),
        'customerNotification' => 
        array (
          0 => 'index',
          1 => 'send',
        ),
        'language' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
          4 => 'export',
          5 => 'import',
        ),
        'employee' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
          4 => 'toggle',
          5 => 'reset.password',
          6 => 'permission',
          7 => 'permission.update',
        ),
        'role' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
          4 => 'permission',
          5 => 'permission.update',
        ),
        'ticketIssueType' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'delete',
        ),
        'supportTicket' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'setScheduled',
          3 => 'sendMessage',
          4 => 'updateStatus',
          5 => 'pinMessage',
          6 => 'chatToggle',
        ),
        'support' => 
        array (
          0 => 'index',
          1 => 'destroy',
        ),
        'vatTax' => 
        array (
          0 => 'index',
          1 => 'order.update',
          2 => 'store',
          3 => 'update',
          4 => 'toggle',
          5 => 'destroy',
        ),
        'blog' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
          5 => 'generate.AI.data',
        ),
        'aiPrompt' => 
        array (
          0 => 'index',
          1 => 'configure',
          2 => 'configure.update',
          3 => 'update',
        ),
        'returnOrder' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'payment.status',
          3 => 'reject',
        ),
        'conversation' => 
        array (
          0 => 'customer.chat.index',
          1 => 'getUsers',
          2 => 'getMessageAdmin',
          3 => 'sendMessageAdmin',
        ),
      ),
      'shop' => 
      array (
        'order' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'status.change',
        ),
        'product' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'show',
          3 => 'edit',
          4 => 'toggle',
          5 => 'destroy',
          6 => 'barcode',
          7 => 'generate.AI.data',
        ),
        'flashSale' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'productStore',
          3 => 'productRemove',
          4 => 'product.edit',
        ),
        'voucher' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'toggle',
          4 => 'destroy',
        ),
        'bulk-product-import' => 
        array (
          0 => 'index',
          1 => 'store',
        ),
        'bulk-product-export' => 
        array (
          0 => 'index',
          1 => 'demo',
          2 => 'export',
        ),
        'gallery' => 
        array (
          0 => 'index',
          1 => 'store',
        ),
        'pos' => 
        array (
          0 => 'index',
          1 => 'sales',
          2 => 'draft',
        ),
        'employee' => 
        array (
          0 => 'index',
          1 => 'create',
          2 => 'edit',
          3 => 'destroy',
          4 => 'toggle',
          5 => 'reset.password',
          6 => 'permission',
          7 => 'permission.update',
        ),
        'profile' => 
        array (
          0 => 'index',
          1 => 'edit',
          2 => 'change-password',
        ),
        'returnOrder' => 
        array (
          0 => 'index',
          1 => 'show',
          2 => 'status.change',
        ),
      ),
      'shopMultiShop' => 
      array (
        'dashboard' => 
        array (
          0 => 'index',
          1 => 'notification',
        ),
        'subscription' => 
        array (
          0 => 'index',
          1 => 'purchase',
          2 => 'renew',
          3 => 'switch',
          4 => 'cancel',
        ),
        'brand' => 
        array (
          0 => 'index',
        ),
        'color' => 
        array (
          0 => 'index',
        ),
        'size' => 
        array (
          0 => 'index',
        ),
        'unit' => 
        array (
          0 => 'index',
        ),
        'category' => 
        array (
          0 => 'index',
        ),
        'subcategory' => 
        array (
          0 => 'index',
        ),
        'withdraw' => 
        array (
          0 => 'index',
          1 => 'store',
          2 => 'show',
        ),
      ),
    ),
    'customerReadableNames' => 
    array (
      'index' => 'list',
      'destroy' => 'delete',
      'toggle' => 'enable/disable',
      'status.toggle' => 'enable/disable',
      'reset.password' => 'reset password',
      'show' => 'view details',
      'permission.update' => 'update permission',
      'voucher' => 'Promo Code',
      'bulk-product-import' => 'bulk product import',
      'store' => 'create',
      'bulk-product-export' => 'bulk product export',
      'gallery' => 'gallery import',
      'status.change' => 'change status',
      'payment.status.toggle' => 'payment update',
      'assign.rider' => 'assign rider',
      'subcategory' => 'sub category',
      'generale-setting' => 'general setting',
      'business-setting' => 'business setting',
      'socialLink' => 'social link',
      'legalPage' => 'legal page',
      'themeColor' => 'theme color',
      'deliveryCharge' => 'delivery charge',
      'pusher' => 'pusher config',
      'mailConfig' => 'mail config',
      'paymentGateway' => 'payment gateway',
      'sms-gateway' => 'sms gateway',
      'contactUs' => 'contact us',
      'firebase' => 'firebase config',
      'change-password' => 'change password',
      'assign.order' => 'assign order',
      'customer' => 'customer',
      'customerNotification' => 'customer notification',
      'language' => 'Language Settings',
      'ticketIssueType' => 'ticket issue type',
      'supportTicket' => 'support ticket',
      'updateStatus' => 'status update',
      'chatToggle' => 'chat enable/disable',
      'support' => 'contact messages',
      'role' => 'roles & permissions',
      'socialAuth' => 'social login',
      'productStore' => 'product store',
      'productRemove' => 'product remove',
      'product.edit' => 'product edit',
      'googleReCaptcha' => 'google reCAPTCHA',
      'vatTax' => 'VAT & Tax',
      'flashSale' => 'flash sale',
      'order.update' => 'Order base tax update',
      'aiPrompt' => 'OpenAI',
      'generate.AI.data' => 'generate AI data',
      'customer.chat.index' => 'Customer Chats',
      'getUsers' => 'Get Users List',
      'getMessageAdmin' => 'Get Messages from Shop',
      'sendMessageAdmin' => 'Send Messages to Admin',
    ),
  ),
  'app' => 
  array (
    'name' => 'Laravel',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://127.0.0.1:8000',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'Asia/Dhaka',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:a/vzJm7wEm/iNtgwl/RiqV5er1lsDHlYRh0h7FlN1O4=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'Milon\\Barcode\\BarcodeServiceProvider',
      24 => 'App\\Providers\\AppServiceProvider',
      25 => 'App\\Providers\\AuthServiceProvider',
      26 => 'App\\Providers\\EventServiceProvider',
      27 => 'App\\Providers\\RouteServiceProvider',
      28 => 'App\\Providers\\SmsServiceProvider',
      29 => 'App\\Providers\\PermissionServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Uri' => 'Illuminate\\Support\\Uri',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
      'DNS1D' => 'Milon\\Barcode\\Facades\\DNS1DFacade',
      'DNS2D' => 'Milon\\Barcode\\Facades\\DNS2DFacade',
    ),
    'version' => '1.0',
    'mail_mailer' => 'smtp',
    'mail_host' => 'mailpit',
    'mail_port' => '1025',
    'mail_username' => '',
    'mail_password' => '',
    'mail_encryption' => '',
    'mail_from_address' => 'hello@example.com',
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'sanctum',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'broadcasting' => 
  array (
    'default' => 'pusher',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '',
        'secret' => '',
        'app_id' => '1776074',
        'options' => 
        array (
          'cluster' => 'mt1',
          'host' => 'api-mt1.pusher.com',
          'port' => '443',
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
        'lock_connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\framework/cache/data',
        'lock_path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
      'apc' => 
      array (
        'driver' => 'apc',
      ),
    ),
    'prefix' => 'laravel_cache_',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'coqui',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'coqui',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'coqui',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'coqui',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'coqui',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'laravel_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'public',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\app',
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\app/public',
        'url' => 'http://127.0.0.1:8000/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
      ),
    ),
    'links' => 
    array (
      'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\public\\storage' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\app/public',
    ),
  ),
  'firebase' => 
  array (
    'default' => 'app',
    'projects' => 
    array (
      'app' => 
      array (
        'credentials' => 
        array (
          'file' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\app/public/firebase_credentials.json',
        ),
        'auth' => 
        array (
          'tenant_id' => NULL,
        ),
        'firestore' => 
        array (
        ),
        'database' => 
        array (
          'url' => NULL,
        ),
        'dynamic_links' => 
        array (
          'default_domain' => NULL,
        ),
        'storage' => 
        array (
          'default_bucket' => NULL,
        ),
        'cache_store' => 'file',
        'logging' => 
        array (
          'http_log_channel' => NULL,
          'http_debug_log_channel' => NULL,
        ),
        'http_client_options' => 
        array (
          'proxy' => NULL,
          'timeout' => NULL,
          'guzzle_middlewares' => 
          array (
          ),
        ),
      ),
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 12,
      'verify' => true,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'installer' => 
  array (
    'name' => 'Laravel Web Installer',
    'seeder_run' => true,
    'minPhpVersion' => '8.2.0',
    'php_extensions' => 
    array (
      0 => 'mysqli',
      1 => 'openssl',
      2 => 'pdo',
      3 => 'mbstring',
      4 => 'JSON',
      5 => 'cURL',
      6 => 'fileinfo',
      7 => 'gmp',
      8 => 'xml',
      9 => 'zip',
      10 => 'sodium',
      11 => 'bcMath',
    ),
    'permissions' => 
    array (
      'storage/' => 777,
      'bootstrap/' => 777,
      'app/Providers/' => 775,
      'routes/' => 775,
      'lang/' => 775,
    ),
    'environment_fields' => 
    array (
      0 => 
      array (
        'APP_NAME' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'App name',
          'placeholder' => 'e.g: Web-installer',
          'type' => 'text',
        ),
        'APP_URL' => 
        array (
          'rule' => 'required|url',
          'label' => 'App base url',
          'placeholder' => 'e.g: http://example.com',
          'type' => 'text',
        ),
        'APP_ENV' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'App eneverment',
          'placeholder' => 'Select app enverment',
          'type' => 'select',
          'option' => 
          array (
            0 => 'local',
            1 => 'production',
            2 => 'staging',
            3 => 'development',
          ),
        ),
        'FILESYSTEM_DISK' => 
        array (
          'rule' => 'required|string',
          'label' => 'App file system',
          'placeholder' => 'Select a file system',
          'type' => 'select',
          'option' => 
          array (
            0 => 'local',
            1 => 'public',
          ),
        ),
        'APP_DEBUG' => 
        array (
          'rule' => 'required|string',
          'label' => 'App debug:',
          'placeholder' => 'Choose app debug mode',
          'option' => 
          array (
            0 => true,
            1 => false,
          ),
          'type' => 'radio',
        ),
      ),
      1 => 
      array (
        'DB_CONNECTION' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'Database Connection',
          'placeholder' => 'Select Databese',
          'type' => 'select',
          'option' => 
          array (
            0 => 'mysql',
            1 => 'sqlite',
            2 => 'pgsql',
            3 => 'sqlsrv',
          ),
        ),
        'DB_HOST' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'Database Host',
          'type' => 'text',
          'placeholder' => 'e.g: 127.0.0.1',
        ),
        'DB_PORT' => 
        array (
          'rule' => 'required|numeric',
          'label' => 'Database Port',
          'type' => 'number',
          'placeholder' => 'e.g: 3306',
        ),
        'DB_DATABASE' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'Database Name',
          'type' => 'text',
          'placeholder' => 'e.g: web_installer',
        ),
        'DB_USERNAME' => 
        array (
          'rule' => 'required|string|max:50',
          'label' => 'Database Username',
          'type' => 'text',
          'placeholder' => 'e.g: root',
        ),
        'DB_PASSWORD' => 
        array (
          'rule' => 'nullable|string|max:50',
          'label' => 'Database Password',
          'type' => 'password',
          'placeholder' => 'e.g: **********',
        ),
      ),
    ),
    'need_to_know' => 
    array (
      0 => 'Codecanyon Purchase Code',
      1 => 'Database Name',
      2 => 'Database Username',
      3 => 'Database Password',
      4 => 'Database Hostname',
      5 => 'Database Port',
    ),
    'users' => 
    array (
      'root' => 
      array (
        'name' => 'Joynal Abedin',
        'email' => 'abedin.dev@gmail.com',
        'password' => 'secret',
        'email_verified_at' => 
        \Illuminate\Support\Carbon::__set_state(array(
           'endOfTime' => false,
           'startOfTime' => false,
           'constructedObjectId' => '00000000000006700000000000000000',
           'clock' => NULL,
           'localMonthsOverflow' => NULL,
           'localYearsOverflow' => NULL,
           'localStrictModeEnabled' => NULL,
           'localHumanDiffOptions' => NULL,
           'localToStringFormat' => NULL,
           'localSerializer' => NULL,
           'localMacros' => NULL,
           'localGenericMacros' => NULL,
           'localFormatFunction' => NULL,
           'localTranslator' => NULL,
           'dumpProperties' => 
          array (
            0 => 'date',
            1 => 'timezone_type',
            2 => 'timezone',
          ),
           'dumpLocale' => NULL,
           'dumpDateProperties' => NULL,
           'date' => '2025-11-26 06:17:39.749389',
           'timezone_type' => 3,
           'timezone' => 'Asia/Dhaka',
        )),
      ),
    ),
    'product' => 'Ready Ecommerce',
    'verify_code' => 'XUapCS7/OXM6rdPHHyTGYDgvN3VSaXptaG53Zy9wOWJZcVZ4UU1ocmVuMit1UVp1SGRyMFJFeWRoSjRVYVpnazZiNU9JRVJoQTNJTEVOREZ4ZHVZQ1BSdHpaOHJ6dVdCaFZ5QkN3PT0=',
    'verify_purchase' => true,
    'verify_rules' => 
    array (
      'email' => 
      array (
        'rule' => 'required|string',
        'label' => 'Your Email',
        'type' => 'email',
        'placeholder' => 'e.g: example@email.com',
      ),
      'purchase_code' => 
      array (
        'rule' => 'required|string',
        'label' => 'Purchase Code',
        'type' => 'text',
        'placeholder' => 'e.g: 040afd3f-4cxa-4241-9e70-4gde9e4t674b',
      ),
    ),
    'install_commands' => 
    array (
      0 => 'php artisan migrate:fresh --force',
      1 => 'php artisan db:seed --force',
      2 => 'php artisan storage:link',
    ),
    'update_commands' => 
    array (
      0 => 'composer update --no-interaction',
      1 => 'composer dumpa --no-interaction',
      2 => 'php artisan migrate --force',
      3 => 'php artisan cache:clear',
      4 => 'php artisan db:seed SocialLinkSeeder --force',
      5 => 'php artisan db:Seed VerifyManageSeeder --force',
      6 => 'php artisan db:seed CurrencySeeder --force',
      7 => 'php artisan db:Seed PermissionSeeder --force',
      8 => 'php artisan db:seed PageSeeder --force',
      9 => 'php artisan db:seed MenuSeeder --force',
      10 => 'php artisan db:seed CountrySeeder --force',
      11 => 'php artisan db:seed FooterSeeder --force',
    ),
    'regular_license' => 
    array (
      'link' => 'https://codecanyon.net/checkout/from_item/52519302/?license=regular&size=source&ref=pds',
      'price' => 39,
    ),
    'extende_license' => 
    array (
      'link' => 'https://codecanyon.net/checkout/from_item/52519302/?license=extended&size=source&ref=pds',
      'price' => 199,
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'url' => NULL,
        'host' => 'mailpit',
        'port' => '1025',
        'encryption' => NULL,
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => NULL,
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
      ),
      'mailgun' => 
      array (
        'transport' => 'mailgun',
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'Laravel',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'modules' => 
  array (
    'namespace' => 'Modules',
    'stubs' => 
    array (
      'enabled' => false,
      'path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\vendor/nwidart/laravel-modules/src/Commands/stubs',
      'files' => 
      array (
        'routes/web' => 'routes/web.php',
        'routes/api' => 'routes/api.php',
        'views/index' => 'resources/views/index.blade.php',
        'views/master' => 'resources/views/layouts/master.blade.php',
        'scaffold/config' => 'config/config.php',
        'composer' => 'composer.json',
        'assets/js/app' => 'resources/assets/js/app.js',
        'assets/sass/app' => 'resources/assets/sass/app.scss',
        'vite' => 'vite.config.js',
        'package' => 'package.json',
      ),
      'replacements' => 
      array (
        'routes/web' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'MODULE_NAMESPACE',
          3 => 'CONTROLLER_NAMESPACE',
        ),
        'routes/api' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'vite' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'json' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'MODULE_NAMESPACE',
          3 => 'PROVIDER_NAMESPACE',
        ),
        'views/index' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'views/master' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'scaffold/config' => 
        array (
          0 => 'STUDLY_NAME',
        ),
        'composer' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'VENDOR',
          3 => 'AUTHOR_NAME',
          4 => 'AUTHOR_EMAIL',
          5 => 'MODULE_NAMESPACE',
          6 => 'PROVIDER_NAMESPACE',
        ),
      ),
      'gitkeep' => true,
    ),
    'paths' => 
    array (
      'modules' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\Modules',
      'assets' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\public\\modules',
      'migration' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\database/migrations',
      'generator' => 
      array (
        'config' => 
        array (
          'path' => 'config',
          'generate' => true,
        ),
        'command' => 
        array (
          'path' => 'App/Console',
          'generate' => false,
        ),
        'channels' => 
        array (
          'path' => 'App/Broadcasting',
          'generate' => false,
        ),
        'migration' => 
        array (
          'path' => 'Database/migrations',
          'generate' => false,
        ),
        'seeder' => 
        array (
          'path' => 'Database/Seeders',
          'generate' => true,
        ),
        'factory' => 
        array (
          'path' => 'Database/Factories',
          'generate' => false,
        ),
        'model' => 
        array (
          'path' => 'App/Models',
          'generate' => false,
        ),
        'observer' => 
        array (
          'path' => 'App/Observers',
          'generate' => false,
        ),
        'routes' => 
        array (
          'path' => 'routes',
          'generate' => true,
        ),
        'controller' => 
        array (
          'path' => 'App/Http/Controllers',
          'generate' => true,
        ),
        'filter' => 
        array (
          'path' => 'App/Http/Middleware',
          'generate' => false,
        ),
        'request' => 
        array (
          'path' => 'App/Http/Requests',
          'generate' => false,
        ),
        'provider' => 
        array (
          'path' => 'App/Providers',
          'generate' => true,
        ),
        'assets' => 
        array (
          'path' => 'resources/assets',
          'generate' => false,
        ),
        'lang' => 
        array (
          'path' => 'lang',
          'generate' => false,
        ),
        'views' => 
        array (
          'path' => 'resources/views',
          'generate' => true,
        ),
        'test' => 
        array (
          'path' => 'tests/Unit',
          'generate' => false,
        ),
        'test-feature' => 
        array (
          'path' => 'tests/Feature',
          'generate' => false,
        ),
        'repository' => 
        array (
          'path' => 'App/Repositories',
          'generate' => false,
        ),
        'event' => 
        array (
          'path' => 'App/Events',
          'generate' => false,
        ),
        'listener' => 
        array (
          'path' => 'App/Listeners',
          'generate' => false,
        ),
        'policies' => 
        array (
          'path' => 'App/Policies',
          'generate' => false,
        ),
        'rules' => 
        array (
          'path' => 'App/Rules',
          'generate' => false,
        ),
        'jobs' => 
        array (
          'path' => 'App/Jobs',
          'generate' => false,
        ),
        'emails' => 
        array (
          'path' => 'App/Emails',
          'generate' => false,
        ),
        'notifications' => 
        array (
          'path' => 'App/Notifications',
          'generate' => false,
        ),
        'resource' => 
        array (
          'path' => 'App/resources',
          'generate' => false,
        ),
        'component-view' => 
        array (
          'path' => 'resources/views/components',
          'generate' => false,
        ),
        'component-class' => 
        array (
          'path' => 'App/View/Components',
          'generate' => false,
        ),
      ),
    ),
    'commands' => 
    array (
      0 => 'Nwidart\\Modules\\Commands\\ChannelMakeCommand',
      1 => 'Nwidart\\Modules\\Commands\\CheckLangCommand',
      2 => 'Nwidart\\Modules\\Commands\\CommandMakeCommand',
      3 => 'Nwidart\\Modules\\Commands\\ComponentClassMakeCommand',
      4 => 'Nwidart\\Modules\\Commands\\ComponentViewMakeCommand',
      5 => 'Nwidart\\Modules\\Commands\\ControllerMakeCommand',
      6 => 'Nwidart\\Modules\\Commands\\DisableCommand',
      7 => 'Nwidart\\Modules\\Commands\\DumpCommand',
      8 => 'Nwidart\\Modules\\Commands\\EnableCommand',
      9 => 'Nwidart\\Modules\\Commands\\EventMakeCommand',
      10 => 'Nwidart\\Modules\\Commands\\FactoryMakeCommand',
      11 => 'Nwidart\\Modules\\Commands\\InstallCommand',
      12 => 'Nwidart\\Modules\\Commands\\JobMakeCommand',
      13 => 'Nwidart\\Modules\\Commands\\LaravelModulesV6Migrator',
      14 => 'Nwidart\\Modules\\Commands\\ListCommand',
      15 => 'Nwidart\\Modules\\Commands\\ListenerMakeCommand',
      16 => 'Nwidart\\Modules\\Commands\\MailMakeCommand',
      17 => 'Nwidart\\Modules\\Commands\\MiddlewareMakeCommand',
      18 => 'Nwidart\\Modules\\Commands\\MigrateCommand',
      19 => 'Nwidart\\Modules\\Commands\\MigrateFreshCommand',
      20 => 'Nwidart\\Modules\\Commands\\MigrateRefreshCommand',
      21 => 'Nwidart\\Modules\\Commands\\MigrateResetCommand',
      22 => 'Nwidart\\Modules\\Commands\\MigrateRollbackCommand',
      23 => 'Nwidart\\Modules\\Commands\\MigrateStatusCommand',
      24 => 'Nwidart\\Modules\\Commands\\MigrationMakeCommand',
      25 => 'Nwidart\\Modules\\Commands\\ModelMakeCommand',
      26 => 'Nwidart\\Modules\\Commands\\ModelPruneCommand',
      27 => 'Nwidart\\Modules\\Commands\\ModelShowCommand',
      28 => 'Nwidart\\Modules\\Commands\\ModuleDeleteCommand',
      29 => 'Nwidart\\Modules\\Commands\\ModuleMakeCommand',
      30 => 'Nwidart\\Modules\\Commands\\NotificationMakeCommand',
      31 => 'Nwidart\\Modules\\Commands\\ObserverMakeCommand',
      32 => 'Nwidart\\Modules\\Commands\\PolicyMakeCommand',
      33 => 'Nwidart\\Modules\\Commands\\ProviderMakeCommand',
      34 => 'Nwidart\\Modules\\Commands\\PublishCommand',
      35 => 'Nwidart\\Modules\\Commands\\PublishConfigurationCommand',
      36 => 'Nwidart\\Modules\\Commands\\PublishMigrationCommand',
      37 => 'Nwidart\\Modules\\Commands\\PublishTranslationCommand',
      38 => 'Nwidart\\Modules\\Commands\\RequestMakeCommand',
      39 => 'Nwidart\\Modules\\Commands\\ResourceMakeCommand',
      40 => 'Nwidart\\Modules\\Commands\\RouteProviderMakeCommand',
      41 => 'Nwidart\\Modules\\Commands\\RuleMakeCommand',
      42 => 'Nwidart\\Modules\\Commands\\SeedCommand',
      43 => 'Nwidart\\Modules\\Commands\\SeedMakeCommand',
      44 => 'Nwidart\\Modules\\Commands\\SetupCommand',
      45 => 'Nwidart\\Modules\\Commands\\TestMakeCommand',
      46 => 'Nwidart\\Modules\\Commands\\UnUseCommand',
      47 => 'Nwidart\\Modules\\Commands\\UpdateCommand',
      48 => 'Nwidart\\Modules\\Commands\\UseCommand',
    ),
    'scan' => 
    array (
      'enabled' => false,
      'paths' => 
      array (
        0 => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\vendor/*/*',
      ),
    ),
    'composer' => 
    array (
      'vendor' => 'nwidart',
      'author' => 
      array (
        'name' => 'Nicolas Widart',
        'email' => 'n.widart@gmail.com',
      ),
      'composer-output' => false,
    ),
    'cache' => 
    array (
      'enabled' => false,
      'driver' => 'file',
      'key' => 'laravel-modules',
      'lifetime' => 60,
    ),
    'register' => 
    array (
      'translations' => true,
      'files' => 'register',
    ),
    'activators' => 
    array (
      'file' => 
      array (
        'class' => 'Nwidart\\Modules\\Activators\\FileActivator',
        'statuses-file' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\modules_statuses.json',
        'cache-key' => 'activator.installed',
        'cache-lifetime' => 604800,
      ),
    ),
    'activator' => 'file',
  ),
  'openai' => 
  array (
    'api_key' => '',
    'organization' => '',
    'project' => NULL,
    'base_uri' => NULL,
    'request_timeout' => 30,
  ),
  'passport' => 
  array (
    'guard' => 'web',
    'private_key' => NULL,
    'public_key' => NULL,
    'client_uuids' => true,
    'personal_access_client' => 
    array (
      'id' => NULL,
      'secret' => NULL,
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_has_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_has_permissions',
    ),
    'column_names' => 
    array (
      'role_pivot_key' => NULL,
      'permission_pivot_key' => NULL,
      'model_morph_key' => 'model_id',
      'team_foreign_key' => 'team_id',
    ),
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 
      \DateInterval::__set_state(array(
         'from_string' => true,
         'date_string' => '24 hours',
      )),
      'key' => 'spatie.permission.cache',
      'store' => 'default',
    ),
  ),
  'purifier' => 
  array (
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignoreNonStrings' => false,
    'cachePath' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\app/purifier',
    'cacheFileMode' => 493,
    'settings' => 
    array (
      'default' => 
      array (
        'HTML.Doctype' => 'HTML 4.01 Transitional',
        'HTML.Allowed' => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
        'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
        'AutoFormat.AutoParagraph' => true,
        'AutoFormat.RemoveEmpty' => true,
      ),
      'test' => 
      array (
        'Attr.EnableID' => 'true',
      ),
      'youtube' => 
      array (
        'HTML.SafeIframe' => 'true',
        'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
      ),
      'custom_definition' => 
      array (
        'id' => 'html5-definitions',
        'rev' => 1,
        'debug' => false,
        'elements' => 
        array (
          0 => 
          array (
            0 => 'section',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          1 => 
          array (
            0 => 'nav',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          2 => 
          array (
            0 => 'article',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          3 => 
          array (
            0 => 'aside',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          4 => 
          array (
            0 => 'header',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          5 => 
          array (
            0 => 'footer',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          6 => 
          array (
            0 => 'address',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          7 => 
          array (
            0 => 'hgroup',
            1 => 'Block',
            2 => 'Required: h1 | h2 | h3 | h4 | h5 | h6',
            3 => 'Common',
          ),
          8 => 
          array (
            0 => 'figure',
            1 => 'Block',
            2 => 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow',
            3 => 'Common',
          ),
          9 => 
          array (
            0 => 'figcaption',
            1 => 'Inline',
            2 => 'Flow',
            3 => 'Common',
          ),
          10 => 
          array (
            0 => 'video',
            1 => 'Block',
            2 => 'Optional: (source, Flow) | (Flow, source) | Flow',
            3 => 'Common',
            4 => 
            array (
              'src' => 'URI',
              'type' => 'Text',
              'width' => 'Length',
              'height' => 'Length',
              'poster' => 'URI',
              'preload' => 'Enum#auto,metadata,none',
              'controls' => 'Bool',
            ),
          ),
          11 => 
          array (
            0 => 'source',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'src' => 'URI',
              'type' => 'Text',
            ),
          ),
          12 => 
          array (
            0 => 's',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          13 => 
          array (
            0 => 'var',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          14 => 
          array (
            0 => 'sub',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          15 => 
          array (
            0 => 'sup',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          16 => 
          array (
            0 => 'mark',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          17 => 
          array (
            0 => 'wbr',
            1 => 'Inline',
            2 => 'Empty',
            3 => 'Core',
          ),
          18 => 
          array (
            0 => 'ins',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'cite' => 'URI',
              'datetime' => 'CDATA',
            ),
          ),
          19 => 
          array (
            0 => 'del',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'cite' => 'URI',
              'datetime' => 'CDATA',
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            0 => 'iframe',
            1 => 'allowfullscreen',
            2 => 'Bool',
          ),
          1 => 
          array (
            0 => 'table',
            1 => 'height',
            2 => 'Text',
          ),
          2 => 
          array (
            0 => 'td',
            1 => 'border',
            2 => 'Text',
          ),
          3 => 
          array (
            0 => 'th',
            1 => 'border',
            2 => 'Text',
          ),
          4 => 
          array (
            0 => 'tr',
            1 => 'width',
            2 => 'Text',
          ),
          5 => 
          array (
            0 => 'tr',
            1 => 'height',
            2 => 'Text',
          ),
          6 => 
          array (
            0 => 'tr',
            1 => 'border',
            2 => 'Text',
          ),
        ),
      ),
      'custom_attributes' => 
      array (
        0 => 
        array (
          0 => 'a',
          1 => 'target',
          2 => 'Enum#_blank,_self,_target,_top',
        ),
      ),
      'custom_elements' => 
      array (
        0 => 
        array (
          0 => 'u',
          1 => 'Inline',
          2 => 'Inline',
          3 => 'Common',
        ),
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => '127.0.0.1:8000',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'App\\Http\\Middleware\\EncryptCookies',
      'verify_csrf_token' => 'App\\Http\\Middleware\\VerifyCsrfToken',
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
      'endpoint' => 'api.mailgun.net',
      'scheme' => 'https',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'themeColors' => 
  array (
    'colors' => 
    array (
      'red' => '#EE456B',
      'violet' => '#8b5cf6',
      'purple' => '#a855f7',
    ),
    'shades' => 
    array (
      '#EE456B' => 
      array (
        50 => '#FFF1F3',
        100 => '#FEE5E8',
        200 => '#FCCFD6',
        300 => '#FAA7B5',
        400 => '#F7758F',
        500 => '#EE456B',
        600 => '#DD2C5C',
        700 => '#B91747',
        800 => '#9B1642',
        900 => '#84173E',
        950 => '#4A071D',
      ),
      '#8b5cf6' => 
      array (
        50 => '#f5f3ff',
        100 => '#ede9fe',
        200 => '#ddd6fe',
        300 => '#c4b5fd',
        400 => '#a78bfa',
        500 => '#8b5cf6',
        600 => '#7c3aed',
        700 => '#6d28d9',
        800 => '#5b21b6',
        900 => '#4c1d95',
        950 => '#2e1065',
      ),
      '#a855f7' => 
      array (
        50 => '#faf5ff',
        100 => '#f3e8ff',
        200 => '#e9d5ff',
        300 => '#d8b4fe',
        400 => '#c084fc',
        500 => '#a855f7',
        600 => '#9333ea',
        700 => '#7e22ce',
        800 => '#6b21a8',
        900 => '#581c87',
        950 => '#3b0764',
      ),
    ),
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\resources\\views',
    ),
    'compiled' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\framework\\views',
  ),
  'langscanner' => 
  array (
    'lang_dir_path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\lang',
    'translation_methods' => 
    array (
      0 => '__',
      1 => 'trans',
      2 => 'trans_choice',
      3 => '@lang',
      4 => 'Lang::get',
      5 => 'Lang::choice',
      6 => 'Lang::trans',
      7 => 'Lang::transChoice',
      8 => '@choice',
    ),
    'paths' => 
    array (
      0 => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\app',
      1 => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\resources',
    ),
    'excluded_paths' => 
    array (
    ),
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
        'output_encoding' => '',
        'test_auto_detect' => true,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => NULL,
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'guess',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
      'cells' => 
      array (
        'middleware' => 
        array (
        ),
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
      'default_ttl' => 10800,
    ),
    'transactions' => 
    array (
      'handler' => 'db',
      'db' => 
      array (
        'connection' => NULL,
      ),
    ),
    'temporary_files' => 
    array (
      'local_path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B\\storage\\framework/cache/laravel-excel',
      'local_permissions' => 
      array (
      ),
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'flare' => 
  array (
    'key' => NULL,
    'flare_middleware' => 
    array (
      0 => 'Spatie\\FlareClient\\FlareMiddleware\\RemoveRequestIp',
      1 => 'Spatie\\FlareClient\\FlareMiddleware\\AddGitInformation',
      2 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddNotifierName',
      3 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddEnvironmentInformation',
      4 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionInformation',
      5 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddDumps',
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddLogs' => 
      array (
        'maximum_number_of_collected_logs' => 200,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddQueries' => 
      array (
        'maximum_number_of_collected_queries' => 200,
        'report_query_bindings' => true,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddJobs' => 
      array (
        'max_chained_job_reporting_depth' => 5,
      ),
      6 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddContext',
      7 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionHandledStatus',
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestBodyFields' => 
      array (
        'censor_fields' => 
        array (
          0 => 'password',
          1 => 'password_confirmation',
        ),
      ),
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestHeaders' => 
      array (
        'headers' => 
        array (
          0 => 'API-KEY',
          1 => 'Authorization',
          2 => 'Cookie',
          3 => 'Set-Cookie',
          4 => 'X-CSRF-TOKEN',
          5 => 'X-XSRF-TOKEN',
        ),
      ),
    ),
    'send_logs_as_events' => true,
  ),
  'ignition' => 
  array (
    'editor' => 'phpstorm',
    'theme' => 'auto',
    'enable_share_button' => true,
    'register_commands' => false,
    'solution_providers' => 
    array (
      0 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\BadMethodCallSolutionProvider',
      1 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\MergeConflictSolutionProvider',
      2 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\UndefinedPropertySolutionProvider',
      3 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\IncorrectValetDbCredentialsSolutionProvider',
      4 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingAppKeySolutionProvider',
      5 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\DefaultDbNameSolutionProvider',
      6 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\TableNotFoundSolutionProvider',
      7 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingImportSolutionProvider',
      8 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\InvalidRouteActionSolutionProvider',
      9 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\ViewNotFoundSolutionProvider',
      10 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\RunningLaravelDuskInProductionProvider',
      11 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingColumnSolutionProvider',
      12 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownValidationSolutionProvider',
      13 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingMixManifestSolutionProvider',
      14 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingViteManifestSolutionProvider',
      15 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingLivewireComponentSolutionProvider',
      16 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UndefinedViewVariableSolutionProvider',
      17 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\GenericLaravelExceptionSolutionProvider',
      18 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\OpenAiSolutionProvider',
      19 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\SailNetworkSolutionProvider',
      20 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMysql8CollationSolutionProvider',
      21 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMariadbCollationSolutionProvider',
    ),
    'ignored_solution_providers' => 
    array (
    ),
    'enable_runnable_solutions' => NULL,
    'remote_sites_path' => 'C:\\Users\\VIP\\Desktop\\COQUI\\B2B',
    'local_sites_path' => '',
    'housekeeping_endpoint_prefix' => '_ignition',
    'settings_file_path' => '',
    'recorders' => 
    array (
      0 => 'Spatie\\LaravelIgnition\\Recorders\\DumpRecorder\\DumpRecorder',
      1 => 'Spatie\\LaravelIgnition\\Recorders\\JobRecorder\\JobRecorder',
      2 => 'Spatie\\LaravelIgnition\\Recorders\\LogRecorder\\LogRecorder',
      3 => 'Spatie\\LaravelIgnition\\Recorders\\QueryRecorder\\QueryRecorder',
    ),
    'open_ai_key' => NULL,
    'with_stack_frame_arguments' => true,
    'argument_reducers' => 
    array (
      0 => 'Spatie\\Backtrace\\Arguments\\Reducers\\BaseTypeArgumentReducer',
      1 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ArrayArgumentReducer',
      2 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StdClassArgumentReducer',
      3 => 'Spatie\\Backtrace\\Arguments\\Reducers\\EnumArgumentReducer',
      4 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ClosureArgumentReducer',
      5 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeArgumentReducer',
      6 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeZoneArgumentReducer',
      7 => 'Spatie\\Backtrace\\Arguments\\Reducers\\SymphonyRequestArgumentReducer',
      8 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\ModelArgumentReducer',
      9 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\CollectionArgumentReducer',
      10 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StringableArgumentReducer',
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
