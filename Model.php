<?php
require_once 'Medoo.php';
class Model 
{
    private $dbInstance;

    private $config;

    public function __construct($host, $dbname, $username, $password, $port = 3306) 
    {
        $this->config = [
            'type' => 'mysql',
            'host' => $host,
            'database' => $dbname,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'port' => $port,
            'error' => PDO::ERRMODE_EXCEPTION,
        ];
    }

    public function getInstance()
    {
        if (!$this->dbInstance) {
            $this->dbInstance = new Medoo\Medoo($this->config);
        }
        return $this->dbInstance;
    }

    public function batchInsert($tableName, $data)
    {
        try {
            $result = $this->getInstance()->insert($tableName, $data);
            echo "Inserting $tableName rows:".count($data)."; success rows:".$result->rowCount().PHP_EOL;
        } catch (\Throwable $e) {
            echo \json_encode([
                "level" => "[error]",
                "function" => "Model batchInsert",
                "message" => $e->getMessage(),
                "lines" => $e->getLine(),
            ]).PHP_EOL;
        }
    }
}

