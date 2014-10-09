<?php

    namespace Woahlife;

    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    class User
    {
        /**
         * given an array of data (typically posted from mailgun), signup a new user
         * @param array $postData an array containing enough information to signup a new user
         * @return bool
         */
        public function signupUser($postData) {

            if (empty($postData['sender']) || empty($postData['Message-Id'])) {
                throw new Exception("Invalid post data for signup. missing sender and Message-Id");
            }

            Logging::getLogger()->addDebug("processing signup {$postData['Message-Id']}");

            $db = new Db();
            $connection = $db->getConnection();

            Logging::getLogger()->addDebug("attempting to find user id for {$postData['sender']}");
            $existingUserId = $connection->fetchColumn("SELECT id FROM users WHERE email = ?", array($postData['sender']));
            Logging::getLogger()->addDebug("found user id for {$postData['sender']}: {$existingUserId}");

            if ($existingUserId > 0) {
                // don't throw an exception. could give someone information that a email address is a user...
                return false;
            }

            Logging::getLogger()->addDebug("determining name by manipulating the FROM header {$postData['From']}");
            $name = preg_replace("/\s*<?" . $postData['sender'] . ">?/", '', $postData['From']);
            Logging::getLogger()->addDebug("determined name to be {$name}");

            $dbRecord = [
                "name" => $name,
                "email" => $postData['sender'],
                "active" => 1,
                "message_id" => $postData['Message-Id'],
                "message_url" => $postData['message-url'],
                "create_date" => date("Y-m-d H:i:s")
            ];

            Logging::getLogger()->addDebug("saving signup for {$postData['sender']}");

            $saved = $connection->insert('users', $dbRecord);

            // the db insert returns 1 when it successfully inserts 1 record, nicer to work with booleans though.
            return ($saved === 1) ? true : false;
        }
    }
?>