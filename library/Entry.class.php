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
    use Woahlife\User;
    use Woahlife\Logging;    
    use Woahlife\MailgunClient;

    class Entry
    {   
        private $tableName = "entries";

        /** 
         * Since a user can respond to the email at any time, we have parse the subject line
         * instead of looking at the date headers from Mailgun.
         * Typically, our subject line is this format "<DATE> - how was your day?"
         *
         * @return int the strtotime representation of the entry
         */
        private function determineEntryDateTimestampFromSubjectLine($subject) 
        {
            Logging::getLogger()->addDebug("determining journal post date from subject line: {$subject}");
            $subject = preg_replace("/RE:\s?/i", "", $subject);
            $subject = preg_replace("/ - .*$/", "", $subject);

            $timestamp = strtotime($subject);

            Logging::getLogger()->addDebug("determined journal post date to be " . date("r", $timestamp));

            /**
             * Only use the strtotime result if it's a valid timestamp in the last 12 months.
             * It's better to assume the entry is for today, than for 12/31/1969
             */
            if ($timestamp < strtotime("-12 months")) {
                $timestamp = time();
            }

            return $timestamp;
        }

        /**
         * given an array of data (typically posted from mailgun), save the journal entry.
         * @param array $postData an array containing enough information to store a journal entry
         * @return bool
         */
        public function saveEntry($postData) 
        {
            if (empty($postData['sender'])) {
                throw new \Exception("Invalid post data for journal entry. empty or missing 'sender' field.");
            } else if (empty($postData['Message-Id'])) { 
                throw new \Exception("Invalid post data for journal entry. empty or missing 'Message-Id' field.");
            } else if (empty($postData['stripped-text'])) { 
                throw new \Exception("Invalid post data for journal entry. empty or missing 'stripped-text' field.");
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
            $woahlifeUser = new User();
            $userId = $woahlifeUser->getUserByEmail($postData['sender']);
            Logging::getLogger()->addDebug("found user id for {$_POST['sender']}: {$userId}");

            if (empty($userId)) {
                throw new \Exception("unable to find user id for {$_POST['sender']}");
            }

            $dbRecord = [
                "user_id" => $userId,
                "entry_text" => $postData['stripped-text'],
                "entry_date" => date("Y-m-d", $this->determineEntryDateTimestampFromSubjectLine($postData['Subject'])),
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