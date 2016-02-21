<?php
    /**
     * Class to contain the functionality for storing journal entries.  In the future, possibly editing and exporting.
     * Right now, every entry gets it's own database record.  That means, if you continually reply to today's journal
     * entry email, there will be a separate record for each.  
     *
     * A note about why we encrypt journal entries:
     * This is just one of those features that makes us feel good.  Anyone with access to the 
     * codebase, which is everyone since i posted this on github, and presumably database access,
     * since they're running the site, would have the ability to read anyone's journal entries.
     * Encrypting them just allows us to work on the site, work on the database, without accidently
     * reading someone's entries.  It does not protect users from a malicious entity. 
     * It just allows me to host the site and let my friends use it without them having to 
     * fear that i'll constantly be seeing everything they post.  at the end of the day, it's just to
     * obfuscate the content from developers. you may disagree...
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
        const SUBJECT_LINE_SUFFIX = " - what's up?";

        /**
         * Export the journal entries for a user.
         * Paginate through the entries in groups of 50
         *
         * @param string $email The email address we're exporting entries
         */
        public function getAllEntriesForUser($email)
        {
            if (empty($email)) {
                throw new \Exception("Invalid email address provided for exporting");
            }

            $connection = Db::getConnection();

            $user = new User();
            $user = $user->getUserByEmail($email);

            $totalEntries = $connection->fetchColumn(
                "SELECT COUNT(*) AS total 
                FROM {$this->tableName} 
                WHERE user_id = ?", 
                [$user->id]
            );

            Logging::getLogger()->addDebug("There are {$totalEntries} entries for {$email}");

            $entriesPerIteration = 50;
            $totalIterations = ceil($totalEntries / $entriesPerIteration);

            $encryptionPassword = $this->determineEncryptionPassword($user);

            $allEntries = [];

            for ($i = 0; $i < $totalIterations; $i++) {
                $offset = $i * $entriesPerIteration;

                $entries = $connection->fetchAll(
                    "SELECT * 
                    FROM {$this->tableName} 
                    WHERE user_id = ?
                    ORDER by entry_date ASC
                    LIMIT {$offset}, {$entriesPerIteration}", 
                    [$user->id]
                );

                foreach($entries as $entry) {
                    $entry['entry_text'] = $this->decryptJournalEntry($entry['entry_text'], $encryptionPassword);

                    array_push($allEntries, $entry);
                }
            }

            return $allEntries;
        }

        /**
         * Given an array of data (typically posted from mailgun), save the journal entry.
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

            Logging::getLogger()->addDebug("Processing journal post {$postData['Message-Id']}");

            /** 
             * so, we're not supposed to totally rely on the mailgun posted fields for html sanitatizing.
             * Mailgun simply puts the text/plain content from the email in that field.  it should always 
             * be populated, but they warn that it may contain html...
             */
            $postData['stripped-text'] = trim(strip_tags($postData['stripped-text']));           

            $connection = Db::getConnection();

            Logging::getLogger()->addDebug("Finding user id for {$_POST['sender']}");
            $user = new User();
            $user = $user->getUserByEmail($postData['sender']);
            Logging::getLogger()->addDebug("Found user id for {$_POST['sender']}: {$user->id}");

            if (empty($user)) {
                throw new \Exception("Unable to find user id for {$_POST['sender']}");
            }

            $encryptionPassword = $this->determineEncryptionPassword($user);
            $encryptedText = $this->encryptJournalEntry($postData['stripped-text'], $encryptionPassword);

            $dbRecord = [
                "user_id" => $user->id,
                "entry_text" => $encryptedText,
                "entry_date" => date("Y-m-d", $this->determineEntryDateTimestampFromSubjectLine($postData['Subject'])),
                "message_id" => $postData['Message-Id'],
                "message_url" => $postData['message-url'],
                "create_date" => date("Y-m-d H:i:s")
            ];

            Logging::getLogger()->addDebug("Saving journal post for {$postData['sender']} ({$dbRecord['user_id']}) entry date {$dbRecord['entry_date']}");

            $saved = $connection->insert($this->tableName, $dbRecord);

            // the db insert returns 1 when it successfully inserts 1 record, nicer to work with booleans though.
            return ($saved === 1) ? true : false;
        }

        /** 
         * Since a user can respond to the email at any time, we have parse the subject line
         * instead of looking at the date headers from Mailgun.
         * Typically, our subject line is this format "<DATE> - how was your day?"
         *
         * @param string the subject line of the email we received
         * @return int the strtotime representation of the entry
         */
        private function determineEntryDateTimestampFromSubjectLine($subject) 
        {
            Logging::getLogger()->addDebug("Determining journal post date from subject line: {$subject}");

            // remove the RE: prefix
            $subject = preg_replace("/RE:\s?/i", "", $subject);
            // remove the subject line we typically use
            $subject = preg_replace("/" . self::SUBJECT_LINE_SUFFIX . "$/", "", $subject);
            // in case the subject line ever changes, just strip everything past the dash
            $subject = preg_replace("/ - .*$/", "", $subject);

            // parse the text to determine the actual date of the journal entry
            $timestamp = strtotime($subject);

            Logging::getLogger()->addDebug("Determined journal post date to be " . date("r", $timestamp));

            /**
             * Only use the strtotime result if it's a valid timestamp in the last 36 months.
             * It's better to assume the entry is for today, than for 12/31/1969
             */
            if ($timestamp < strtotime("-36 months")) {
                $timestamp = time();
            }

            return $timestamp;
        }

        /**
         * Determine the encryption password per user. 
         * One could make this way more complicated, but since our intent isn't security, rather
         * hidding data from developers of the site, this is good enough.
         * 
         * @param \Woahlife\User The user object
         * @return string the password we use for encryption/decryption
         */
        private function determineEncryptionPassword($user) 
        {
            $password = $user->email;

            return $password;
        }

        /**
         * Encrypt the journal entry text so that we aren't storing plain text journal entries 
         * in our database.
         *
         * @param string the text to encrypt
         * @param string password used to encrypt the text
         * @return string the encrypted text
         */
        private function encryptJournalEntry($plainText, $password)
        {
            $encrypted = openssl_encrypt($plainText, 'aes-128-cbc', $password);

            return $encrypted;
        }

        /**
         * decrypt the stored journal entry
         *
         * see phpdoc for encryptJournalEntry for important caveats to how and why we are
         * bothering to encrypt/decrypt the journal entries.
         *
         * @param string the text to decrypt
         * @param string the password used to encrypt the text
         * @return string the decrtyped text
         */
        private function decryptJournalEntry($encryptedText, $password)
        {
            $decrypted = openssl_decrypt($encryptedText, 'aes-128-cbc', $password);

            return $decrypted;
        }
    }
?>