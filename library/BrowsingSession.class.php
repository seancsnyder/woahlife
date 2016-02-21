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
        
        public $user;
        public $validUntil;

        /**
         * Create an object from the data
         * 
         * @param array
         * 
         * @return \Woahlife\User|null
         */
        private function initializeObject($dataRecord)
        {
            $object = null;
            
            if (is_array($dataRecord) 
               && count($dataRecord) > 0
            ) {
                $object = new BrowsingSession();
                
                $user = new User();
                $object->user = $user->getUserById($dataRecord['id']);
                $object->validUntil = $dataRecord['valid_until'];
            }
            
            return $object;
        }
        
        /**
         * Find the browsing session by token
         *
         * @return \Woahlife\BrowsingSession
         */
        public function getBrowsingSessionByToken($sessionToken)
        {
            $connection = Db::getConnection();

            $data = $connection->fetchAssoc(
                "SELECT user_id, valid_until
                FROM {$this->tableName}
                WHERE session_token = ?", 
                [$sessionToken]
            );

            return $this->initializeObject($data);
        }
        
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

            Logging::getLogger()->addDebug("Processing browsing session generation {$postData['Message-Id']}");

            Logging::getLogger()->addDebug("Attempting to find user id for {$postData['sender']}");
            $user = new User();
            $user = $user->getUserByEmail($postData['sender']);
            Logging::getLogger()->addDebug("Found user id for {$postData['sender']}: {$user->id}");

            if ($user == null) {
                // don't throw an exception. could give someone information that a email address is a user...
                return false;
            }
            
            $sessionToken = $this->generateBrowsingSessionToken();

            $connection = Db::getConnection();
            
            $dbRecord = [
                "user_id" => $user->id,
                "session_token" => $sessionToken,
                "create_date" => date("Y-m-d H:i:s"),
                "valid_until" => date("Y-m-d H:i:s", strtotime("+3 hours"))
            ];

            Logging::getLogger()->addDebug("Saving browsing session for {$user->email}");

            $saved = $connection->insert('browsing_sessions', $dbRecord);
            
            // the db insert returns 1 when it successfully inserts 1 record, nicer to work with booleans though.
            if ($saved === 1) {
                $mailgunner = new MailgunClient();

                $mailgunned = $mailgunner->sendBrowsingSessionEmail($user, $sessionToken);

                return true;
            }
            
            return false;
        }
        
        /**
         * Generate a random string to use for the browsing session
         * 
         * @return string
         */
        protected function generateBrowsingSessionToken()
        {
            return sha1(date("r") . mt_rand(0, 100000));
        }
        
        /**
         * Validate the browsing session. if it's valid, return the session
         * otherwise, throw.
         * 
         * @param string $sessionToken The token we want to validate
         * 
         * @return \Woahlife\BrowsingSession
         */
        public function validateBrowsingSession($sessionToken)
        {
            Logging::getLogger()->addDebug("Validating browsing session {$sessionToken}");
            
            $browsingSession = $this->getBrowsingSessionByToken($sessionToken);
            
            if ($browsingSession != null) {
                Logging::getLogger()->addDebug(
                    "Found browsing session for {$sessionToken}."
                    . " Valid until {$browsingSession->validUntil}"
                );
                
                if (strtotime($browsingSession->validUntil) < time()) {
                    return $browsingSession;
                }
            }
            
            Logging::getLogger()->addDebug("Unable to validate browsing session {$sessionToken}");
            
            throw new \Exception("Invalid browsing session");
        }
    }
?>