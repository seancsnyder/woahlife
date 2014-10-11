<?php
    /**
     * This class is meant to handle all of the posting to mailgun. This is currently limited to 
     * sending the daily journal entry email prompt as well as the signup welcome message.
     *
     * @author Sean Snyder <sean@snyderitis.com>
     */

    namespace Woahlife;

    use Mailgun\Mailgun;

    class MailgunClient 
    {
        const SUBJECT_LINE_SUFFIX = " - what's up?";

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
         * @param array data to use during the email creation and send
         * @return stdClass
         */
        public function sendDailyEmail($data)
        {
            Logging::getLogger()->addDebug("sending email to {$data['email']}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['postFromAddress']}>", 
                'to' => "{$data['name']} <{$data['email']}>", 
                'subject' => date("l M d, Y") . self::SUBJECT_LINE_SUFFIX, 
                'text' =>  "hello..."
            ];

            if (APP_MODE === "DEV") {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("woahlife application is in test mode, not sending email");
            }

            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("done sending email to {$data['email']}");

            return $mailgunned;
        }

        /**
         * Send a welcome email to the user.
         *
         * @param string name the name of the user
         * @param string email address to welcome
         * @return stdClass
         */
        public function sendWelcomeEmail($name, $email)
        {
            Logging::getLogger()->addDebug("sending welcome email to {$email}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['signupFromAddress']}>", 
                'to' => "{$name} <{$email}>", 
                'subject' => "Welcome!", 
                'text' =>  "Thanks for signing up! You should now begin to receive emails on a daily basis."
            ];

            if (APP_MODE === "DEV") {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("woahlife application is in test mode, not sending email");
            }
 
            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("done sending email to {$email}");

            return $mailgunned;
        }
    }
?>