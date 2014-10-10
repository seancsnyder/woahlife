<?php

    /**
     * This class is meant to handle all of the file/console logging throughout the application.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */

    namespace Woahlife;

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use Monolog\Formatter\LineFormatter;

    class Logging
    {
        const APP_LOG_FILE_NAME = "app.log";
        const APP_LOG_DIRECTORY = "logs";

        protected function __construct() {}

        /**
         * singleton pattern, get the static logger from anywhere in the application
         * @return Monolog\Logger;
         */
        public static function getLogger()
        {
            static $logger = null;

            if ($logger == null) {
                $dateFormat = "Y-m-d H:i:s";
                $output = "[%datetime%] [%level_name%] %message%\n";

                $formatter = new LineFormatter($output, $dateFormat);

                if (!is_dir(APP_DIRECTORY . self::APP_LOG_DIRECTORY)) {
                    throw new \Exception(
                        "'logs' directory does not exist. "
                        . " please create it with the appropriate permissions for the webserver to write to it."
                    );
                }

                $appLogFullPath = APP_DIRECTORY . self::APP_LOG_DIRECTORY . "/" . self::APP_LOG_FILE_NAME;

                // if the app log doesn't exists, create the file.
                if (!file_exists($appLogFullPath)) {
                    $fh = fopen($appLogFullPath, 'w');
                    fclose($fh);
                }

                $stream = new StreamHandler($appLogFullPath, Logger::DEBUG);
                $stream->setFormatter($formatter);

                $logger = new Logger('woahlog');
                $logger->pushHandler($stream);

                if (APP_MODE === "DEV") {
                    $stdout = new StreamHandler('php://output', Logger::DEBUG);
                    $stdout->setFormatter($formatter);
                    $logger->pushHandler($stdout);
                }
            }
            
            return $logger;
        }
    }
?>