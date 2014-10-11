<?php

    /**
     * This class is meant to handle the connection to the database.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */ 

    namespace Woahlife;

    use Doctrine\DBAL;
    use Doctrine\DBAL\DriverManager;

    class Db
    {
        /**
         * Using a singleton pattern for the connection.
         */
        protected function __construct() {}           

        /**
         * get the connection to use for querying the db
         * @return Doctrine\DBAL\Connection
         */
        public static function getConnection()
        {
            static $connection = null;

            if ($connection == null) {
                Logging::getLogger()->addDebug("parsing mysql config file");
                $mysqlConfiguration = parse_ini_file(APP_DIRECTORY . 'config/mysql.ini');
                Logging::getLogger()->addDebug("done parsing mysql config file");

                $dbalConfig = new \Doctrine\DBAL\Configuration();
                
                Logging::getLogger()->addDebug("connectiong to mysql server {$mysqlConfiguration['host']}");
                $connection = \Doctrine\DBAL\DriverManager::getConnection($mysqlConfiguration, $dbalConfig);
                Logging::getLogger()->addDebug("got connection to mysql server {$mysqlConfiguration['host']}");
            }

            return $connection;
        }
    }

?>