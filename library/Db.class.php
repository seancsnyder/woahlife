<?php

    namespace Woahlife;

    use Doctrine\DBAL;
    use Doctrine\DBAL\DriverManager;

    class Db
    {
        private $connection;
        private $configuration;

        public function __construct()
        {
            Logging::getLogger()->addDebug("parsing mysql config file");
            $this->mysqlConfiguration = parse_ini_file('config/mysql.ini');
            Logging::getLogger()->addDebug("done parsing mysql config file");

            $dbalConfig = new \Doctrine\DBAL\Configuration();
            
            Logging::getLogger()->addDebug("connection to mysql server {$this->mysqlConfiguration['host']}");
            $this->connection = \Doctrine\DBAL\DriverManager::getConnection($this->mysqlConfiguration, $dbalConfig);
            Logging::getLogger()->addDebug("got connection to mysql server {$this->mysqlConfiguration['host']}");
        }

        public function getConnection()
        {
            return $this->connection;
        }
    }

?>