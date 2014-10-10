<?php

    /**
     * Class to contain the functionality for storing journal entries.  In the future, possibly editing and exporting.
     * Right now, every entry gets it's own database record.  That means, if you continually reply to today's journal
     * entry email, there will be a separate record for each.  
     * 
     * @author Sean Snyder <sean@snyderitis.com>
     */

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
        public function saveEntry($postData) 
        {
            if (empty($postData['sender'])) {
                throw new \Exception("Invalid post data for journal entry. empty or missing sender");
            } else if (empty($postData['Message-Id'])) { 
                throw new \Exception("Invalid post data for journal entry. empty or missing Message-Id");
            } else if (empty($postData['stripped-text'])) { 
                throw new \Exception("Invalid post data for journal entry. empty/missing stripped-text");
            }

            Logging::getLogger()->addDebug("processing journal post {$postData['Message-Id']}");

            /** 
             * so, we're not supposedly to totally rely on the mailgun posted fields for html sanitatizing.
             * Mailgun simply puts the text/plain content from the email in that field.  it should always 
             * be populated, but they warn that it may contain html
             */
            $postData['stripped-text'] = trim(strip_tags($postData['stripped-text']));           

            $db = new Db();
            $connection = $db->getConnection();

            Logging::getLogger()->addDebug("finding user id for {$_POST['sender']}");
            $userId = $connection->fetchColumn("SELECT id FROM users WHERE email = ?", array($postData['sender']));
            Logging::getLogger()->addDebug("found user id for {$_POST['sender']}: {$userId}");

            if (empty($userId)) {
                throw new \Exception("unable to find user id for {$_POST['sender']}");
            }

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