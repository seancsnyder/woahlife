<?php

    namespace Woahlife;

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use Monolog\Formatter\LineFormatter;

    class Logging
    {
        protected function __construct() {}

        public static function getLogger()
        {
            static $logger = null;

            if ($logger == null) {
                $dateFormat = "Y-m-d H:i:s";
                $output = "[%datetime%] [%level_name%] %message%\n";

                $formatter = new LineFormatter($output, $dateFormat);
                $stream = new StreamHandler('logs/app.log', Logger::DEBUG);
                $stream->setFormatter($formatter);

                $logger = new Logger('woahlog');
                $logger->pushHandler($stream);
            }
            
            return $logger;
        }
    }
?>