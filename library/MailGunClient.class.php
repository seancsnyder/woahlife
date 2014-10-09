<?php
    namespace Woahlife;

    use Mailgun\Mailgun;

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
         * @param array data to use during the email creation and send
         * @param bool whether this is a test mode or not.
         * @return stdClass
         */
        public function sendMessage($data, $testmode = false)
        {
            Logging::getLogger()->addDebug("sending email to {$data['email']}");

            $postData = [
                'from' => "{$this->mailgunConfig['fromName']} <{$this->mailgunConfig['postFromAddress']}>", 
                'to' => "{$data['name']} <{$data['email']}>", 
                'subject' => date("M d, Y") . " - How was your day?", 
                'text' =>  "hello, how was your day? Simply reply to this email to document your day."
            ];

            if ($testmode === true) {
                $postData['o:testmode'] = "yes";
                Logging::getLogger()->addDebug("test mode, not sending email");
            }

            $mailgunned = $this->mailgunner->sendMessage($this->mailgunConfig['domain'], $postData);

            Logging::getLogger()->addDebug("done sending email to {$data['email']}");

            return $mailgunned;
        }
    }
?>