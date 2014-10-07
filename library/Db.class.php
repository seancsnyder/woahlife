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
            //TODO put this stuff in a bootstrap APP_PATH
            $baseDirectory = "";

            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $baseDirectory = $_SERVER['DOCUMENT_ROOT'] . "/../";
            }

            Logging::getLogger()->addDebug("parsing mysql config file");
            $this->mysqlConfiguration = parse_ini_file($baseDirectory . 'config/mysql.ini');
            Logging::getLogger()->addDebug("done parsing mysql config file");

            $dbalConfig = new \Doctrine\DBAL\Configuration();
            
            Logging::getLogger()->addDebug("connectiong to mysql server {$this->mysqlConfiguration['host']}");
            $this->connection = \Doctrine\DBAL\DriverManager::getConnection($this->mysqlConfiguration, $dbalConfig);
            Logging::getLogger()->addDebug("got connection to mysql server {$this->mysqlConfiguration['host']}");
        }

        public function getConnection()
        {
            return $this->connection;
        }
    }

?>