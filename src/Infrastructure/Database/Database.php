<?php
namespace App\Infrastructure\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    private Capsule $capsule;

    public function __construct(array $config)
    {
        $this->capsule = new Capsule;
        $this->capsule->addConnection([
            'driver'    => $config['driver'] ?? 'mysql',
            'host'      => $config['host'],
            'database'  => $config['dbname'],
            'username'  => $config['user'],
            'password'  => $config['password'],
            'charset'   => $config['charset'] ?? 'utf8mb4',
            'collation' => $config['collation'] ?? 'utf8mb4_unicode_ci',
        ]);

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}
