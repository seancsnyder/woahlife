<?php
    /**
     * This class is meant to handle the user browsing sessions and storage to the database.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */

    namespace Woahlife;

    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;
    use Woahlife\User;

    class BrowsingSession
    {
        private $tableName = "browsing_sessions";
        
        /**
         * Create a browsing session key, valid for a short amount of time.
         * 
         * @param array $postData an array containing enough information to create a browsing session key for a user
         * @return bool
         */
        public function createBrowseSession($postData)
        {
            if (empty($postData['sender'])) {
                throw new \Exception("Invalid post data for signup. missing or empty 'sender' field.");
            } else if (empty($postData['Message-Id'])) {
                throw new \Exception("Invalid post data for signup. missing or empty 'Message-Id' field.");
            }

            Logging::getLogger()->addDebug("processing browsing session generation {$postData['Message-Id']}");

            Logging::getLogger()->addDebug("attempting to find user id for {$postData['sender']}");
            $woahlifeUser = new User();
            $user = $woahlifeUser->getUserByEmail($postData['sender']);
            Logging::getLogger()->addDebug("found user id for {$postData['sender']}: {$user->id}");

            if ($user == null) {
                // don't throw an exception. could give someone information that a email address is a user...
                return false;
            }
            
            $browsingKey = $this->generateBrowsingSessionKey();

            $connection = Db::getConnection();
            
            $dbRecord = [
                "user_id" => $user->id,
                "key" => $browsingKey,
                "create_date" => date("Y-m-d H:i:s"),
                "valid_until" => date("Y-m-d H:i:s", strtotime("+3 hours"))
            ];

            Logging::getLogger()->addDebug("saving browsing session for {$user->email}");

            $saved = $connection->insert('browsing_sessions', $dbRecord);
            
            // the db insert returns 1 when it successfully inserts 1 record, nicer to work with booleans though.
            if ($saved === 1) {
                $mailgunner = new MailgunClient();

                $mailgunned = $mailgunner->sendBrowsingSessionEmail($user, $browsingKey);

                return true;
            }
            
            return false;
        }
        
        /**
         * Generate a random string to use for the browsing session
         * 
         * @return string
         */
        protected function generateBrowsingSessionKey()
        {
            return "randd";
        }
    }
?>