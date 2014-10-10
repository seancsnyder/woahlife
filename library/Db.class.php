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
            $this->mysqlConfiguration = parse_ini_file(APP_DIRECTORY . 'config/mysql.ini');
            Logging::getLogger()->addDebug("done parsing mysql config file");

            $dbalConfig = new \Doctrine\DBAL\Configuration();
            
            Logging::getLogger()->addDebug("connectiong to mysql server {$this->mysqlConfiguration['host']}");
            $this->connection = \Doctrine\DBAL\DriverManager::getConnection($this->mysqlConfiguration, $dbalConfig);
            Logging::getLogger()->addDebug("got connection to mysql server {$this->mysqlConfiguration['host']}");
        }

        /**
         * get the connection to use for querying the db
         * @return Doctrine\DBAL\Connection
         */
        public function getConnection()
        {
            return $this->connection;
        }
    }

?>