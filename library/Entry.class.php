<?php

    namespace Woahlife;

    use Woahlife\Db;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    class Entry
    {
        /**
         * given an array of data (typically posted from mailgun), save the journal entry.
         * @param array $postData an array containing enough information to store a journal entry
         * @return bool
         */
        public function saveEntry($postData) {

            if (empty($postData['sender']) || empty($postData['Message-Id'])) {
                throw new \Exception("Invalid post data for journal entry. missing sender and Message-Id");
            }

            Logging::getLogger()->addDebug("processing journal post {$postData['Message-Id']}");

            $db = new Db();
            $connection = $db->getConnection();

            Logging::getLogger()->addDebug("finding user id for {$_POST['sender']}");
            $userId = $connection->fetchColumn("SELECT id FROM users WHERE email = ?", array($postData['sender']));
            Logging::getLogger()->addDebug("found user id for {$_POST['sender']}: {$userId}");

            if (empty($userId)) {
                throw new \Exception("unable to find user id for {$_POST['sender']}");
            }

            //TODO don't rely on the mailgun posted fields...
            $dbRecord = [
                "user_id" => $userId,
                "entry_text" => $postData['stripped-text'],
                "entry_date" => date("Y-m-d", strtotime($postData['Date'])),
                "message_id" => $postData['Message-Id'],
                "message_url" => $postData['message-url'],
                "create_date" => date("Y-m-d H:i:s")
            ];

            Logging::getLogger()->addDebug("saving journal post for {$postData['sender']} ({$dbRecord['user_id']}) entry date {$dbRecord['entry_date']}");

            $saved = $connection->insert('entries', $dbRecord);

            // the db insert returns 1 when it successfully inserts 1 record, nicer to work with booleans though.
            return ($saved === 1) ? true : false;
        }
    }
?>