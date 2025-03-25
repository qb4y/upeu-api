<?php
use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */
    'default' =>'oracle',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => 'bethel.upeu',
            'port' => '3306',
            'database' => 'bindery',
            'username' => 'lambdev',
            'password' => 'Fude69fdW',            
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => '192.168.13.99',
            'port' => '1433',
            'database' => 'APS',
            'username' => 'consultasweb',
            'password' => 'consultitas',
            'charset' => 'utf8',
            'prefix' => '',
        ],
        
        'sqlsrvupn' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '10.171.11.3'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'APS'),
            'username' => env('DB_USERNAME', 'saweb'),
            'password' => env('DB_PASSWORD', 'saas'),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'oracle' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => 'oradatoslamb.upeu',
            'port' => '1521',
            'database' => 'upeu',
            'username' => 'eliseo',
            'password' => 'S4LVAC10Nen3l',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => '',
            // 'prefix_schema' => 'eliseo',
        ],
        'oracleapp' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.13.112',
            'port' =>'1521',
            'database' => 'upeu',
            'username' => 'ARON',
            'password' => 'elmarrojo',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'aron',
        ],
        'oracleappjul' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.97.111',
            'port' =>'1521',
            'database' => 'upeu',
            'username' => 'ARON',
            'password' => 'elmarrojo',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'aron',
        ],
        'oracledev' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.13.14',
            'port' =>'1521',
            'database' => 'upeu',
            'username' => 'ARON',
            'password' => 'elmarrojo',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'aron',
        ],
        'oracleaacad' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.13.112',
            //'host' => '192.168.13.14',
            'port' =>'1521',
            'database' => 'upeu',
            'username' => 'NOE',
            'password' => 'arcaDsalvaci0n',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'noe',
        ],
        'pyd' => [//Produccion
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.13.112',
            'port' =>'1521',
            'database' => 'upeu',
            'username' => 'SPE',
            'password' => 'Strat3g4',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'spe',
        ],
        'siscop' => [//Produccion
            'driver' => 'oracle',
            'tns' => '',
            'host' => '192.168.13.208',
            'port' =>'1521',
            'database' => 'ASISTDB',
            'username' => 'asist',
            'password' => 'AsistenciaUPEU',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => 'asist',
        ],
        'jose' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => 'oradatoslamb.upeu',
            'port' => '1521',
            'database' => 'upeu',
            'username' => 'eliseo',
            'password' => 'S4LVAC10Nen3l',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => '',
            'schema'=>'jose',
        ],
        'moises' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => 'oradatoslamb.upeu',
            'port' => '1521',
            'database' => 'upeu',
            'username' => 'eliseo',
            'password' => 'S4LVAC10Nen3l',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => '',
            'schema'=>'moises',
        ],
        'eliseo' => [
            'driver' => 'oracle',
            'tns' => '',
            'host' => 'oradatoslamb.upeu',
            'port' => '1521',
            'database' => 'upeu',
            'username' => 'eliseo',
            'password' => 'S4LVAC10Nen3l',
            'charset' => 'AL32UTF8',
            'prefix' => '',
            'prefix_schema' => '',
        ],
        'efacapp' => [//POSTGRES DEV - EFAC
            'driver' => 'pgsql',
            'host' => 'pgdatacpe.upeu',
            'port' => '5432',
            'database' => 'invoicec_union_v2',
            'username' => 'postgres',
            'password' => 'dBpSQLupEu$#',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],
    // 'redis' => [

    //     'client' => env('REDIS_CLIENT', 'predis'),

    //     'options' => [
    //         'cluster' => env('REDIS_CLUSTER', 'redis'),
    //         'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    //     ],

    //     'default' => [
    //         'url' => env('REDIS_URL'),
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'password' => env('REDIS_PASSWORD', null),
    //         'port' => env('REDIS_PORT', 6379),
    //         'database' => env('REDIS_DB', 0),
    //     ],

    //     'cache' => [
    //         'url' => env('REDIS_URL'),
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'password' => env('REDIS_PASSWORD', null),
    //         'port' => env('REDIS_PORT', 6379),
    //         'database' => env('REDIS_CACHE_DB', 1),
    //     ],

    // ],

];
