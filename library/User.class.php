<?php
    /**
     * This class is meant to handle the user signup and storage to the database.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */

    namespace Woahlife;

    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    class User
    {
        private $tableName = "users";

        /**
         * Get an array of all the active users
         *
         * @return array
         */
        public function getAllActiveUsers()
        {
            $connection = Db::getConnection();

            $users = $connection->fetchAll(
                "SELECT name, email 
                FROM {$this->tableName} 
                WHERE active = ?", 
                [1]
            );

            return $users;
        }

        /**
         * Find the user id by email address
         *
         * @return array
         */
        public function getUserByEmail($email)
        {
            $connection = Db::getConnection();

            $user = $connection->fetchAssoc(
                "SELECT id, email, create_date
                FROM {$this->tableName}
                WHERE email = ?", 
                [$email]
            );

            return $user;
        }

        /**
         * Given an array of data (typically posted from mailgun), signup a new user
         *
         * @param array $postData an array containing enough information to signup a new user
         * @return bool
         */
        public function signupUser($postData) 
        {
            if (empty($postData['sender'])) {
                throw new \Exception("Invalid post data for signup. missing or empty 'sender' field.");
            } else if (empty($postData['Message-Id'])) {
                throw new \Exception("Invalid post data for signup. missing or empty 'Message-Id' field.");
            }

            Logging::getLogger()->addDebug("processing signup {$postData['Message-Id']}");

            $connection = Db::getConnection();

            Logging::getLogger()->addDebug("attempting to find user id for {$postData['sender']}");
            $existingUser = $this->getUserByEmail($postData['sender']);
            Logging::getLogger()->addDebug("found user id for {$postData['sender']}: {$existingUser['id']}");

            if ($existingUser != null) {
                // don't throw an exception. could give someone information that a email address is a user...
                return false;
            }

            Logging::getLogger()->addDebug("determining name by manipulating the FROM header {$postData['From']}");
            $name = preg_replace("/\s*<?" . $postData['sender'] . ">?/", '', $postData['From']);
            Logging::getLogger()->addDebug("determined name to be {$name}");

            // if we couldn't parse out the user's name, from the formatted 'From' header, assume they're a friend.
            if (preg_match("/<|>/", $name)) {
                Logging::getLogger()->addDebug("unable to parse out the name from the header. assuming name to be 'Friend'");
                $name = 'Friend';
            }

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
            if ($saved === 1) {
                $mailgunner = new MailgunClient();

                $mailgunned = $mailgunner->sendWelcomeEmail($name, $postData['sender']);

                return true;
            }
            
            return false;
        }
    }
?>