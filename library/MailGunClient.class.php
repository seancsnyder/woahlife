<?php
    /**
     * This class is meant to handle all of the posting to mailgun. This is currently limited to 
     * sending the daily journal entry email prompt as well as the signup welcome message.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */

    namespace Woahlife;

    use Mailgun\Mailgun;
    use Woahlife\Entry;

    class MailgunClient 
    {
        private $mailgunner;
        private $mailgunConfig;

        public function __construct()
        {
            $this->mailgunConfig = parse_ini_file(APP_DIRECTORY . 'config/mailgun.ini');

            $this->mailgunner = new Mailgun($this->mailgunConfig['apiKey']);
        }

        /**
         * Send an email to the user.
         *
         * @param \Woahlife\User $user The user object
         * @return stdClass
         */
        public function sendDailyEmail($user)
        {
            Logging::getLogger()->addDebug("Sending email to {$user->email}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['postFromAddress']}>", 
                'to' => "{$user->name} <{$user->email}>", 
                'subject' => date("l M d, Y") . Entry::SUBJECT_LINE_SUFFIX, 
                'text' =>  "yo. how you doing today?"
            ];

            if (APP_MODE === "DEV") {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("Application is in test mode, not sending email");
                Logging::getLogger()->addDebug(print_r($postData, true));
            }

            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("Done sending email to {$user->email}");

            return $mailgunned;
        }

        /**
         * Send a welcome email to the user.
         *
         * @param \Woahlife\User $user The user object
         * @return stdClass
         */
        public function sendWelcomeEmail($user)
        {
            Logging::getLogger()->addDebug("Sending welcome email to {$user->email}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['signupFromAddress']}>", 
                'to' => "{$user->name} <{$user->email}>", 
                'subject' => "Welcome!", 
                'text' =>  "Thanks for signing up! You should now begin to receive emails on a daily basis."
            ];

            if (APP_MODE === "DEV") {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("Application is in test mode, not sending email");
                Logging::getLogger()->addDebug(print_r($postData, true));
            }
 
            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("Done sending email to {$user->email}");

            return $mailgunned;
        }
        
        /**
         * Send an email to the user containing the temporary browsing
         * session url.
         *
         * @param \Woahlife\User $user The user object
         * @param string $sessionToken The browsing session key to use in the link
         * @return stdClass
         */
        public function sendBrowsingSessionEmail($user, $sessionToken)
        {
            Logging::getLogger()->addDebug("Sending browsing session email to {$user->email}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['signupFromAddress']}>", 
                'to' => "{$user->name} <{$user->email}>", 
                'subject' => "Browse Your Journal!", 
                'text' =>  "Click this link to browse your journal: {$this->mailgunConfig['webUrl']}/viewEntries.php?token={$sessionToken}"
            ];

            if (APP_MODE === "DEV") {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("Application is in test mode, not sending email");
                Logging::getLogger()->addDebug(print_r($postData, true));
            }
 
            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("Done sending browsing session email to {$user->email}");

            return $mailgunned;
        }
    }
?>