I was a big fan of ohlife.com, but it'll be shutdown soon...

For those that don't know, ohlife.com was a really great way to maintain an journal.  Everyday, at a user configured time, ohlife would
send you an email asking about your day. All you had to do was respond to that email, and ohlife would save the response as a journal entry.
Ohlife provided a site, with a way to search and download your entries.  They also had a premium version that let you customize the 
email message that was sent to you and a way to auto backup your entries to dropbox.

I chose to dumb down the functionality a bit since I only plan on using this for myself.  I don't need a web interface or an easy export button,
since I have full access.

Feel free to clone it and use your own mailgun account.  Here is how to set it up for yourself....sorry it's so many steps, 
but we have a lot of moving pieces...

- Domain:
    1) Buy a domain or use an existing domain that doesn't currently have mx records setup.
- Mailgun:
    2) Signup for an account.  The first 10k emails per month are free.
    3) Add the domain to Mailgun.  They will require you to add some dns records to verify the domain.
    4) Setup the email routes in Mailgun.  These routes will let Mailgun handle the emails that people will send to your domain.
        At a minimum, you need two routes setup.  example:

        Filter Expression = match_recipient("signup@yourdomain.com")  
        Action = store(notify="http://www.yourdomain.com/receiveSignup.php")
        and
        Filter Expression = match_recipient("post@yourdomain.com")    
        Action = store(notify="http://www.yourdomain.com/receivePost.php")

        When someone emails post@yourdomain.com, mailgun will accept the email, store it, and will HTTP POST the entire email 
        message to that url.
- PHP:
    5) You'll need a lamp/lemp stack.  If you're completely lost, this is a pretty good walkthrough, https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04
    6) Once you have it up and running, with at least one virtual host configured, set the A record for your domain to your server
    7) Git clone this repo into the site directory you setup.
        For example, if you configured your document root to be /var/www/mysite.com/
        Go ahead and clone this repo into /var/ww/mysite.com  This will expose the entire codebase, which is bad, so update
        your nginx configuration to set the document root to /var/ww/mysite.com/webroot/ and then restart nginx.
    8) From the /var/www/mysite.com/ directory, run 'composer install' to get all of the dependcies up to date.  If you don't have composer, 
        go here, https://getcomposer.org/
- MySql:
    9) Create a mysql database. Run some sql.  The sql to create the required tables are in the config/bootstrap.sql file.
    10) Update the config/mailgun-sample.ini file with your actual mailgun information and the domain you plan on using.
        Then, rename the file to "mailgun.ini" in the same directory
    11) Update the config/mysql-sample.ini" file with your actual mysql database information and the domain you plan on using.
        Then, rename the file to "mysql.ini" in the same directory
    12) Give the 'logs' directory the correct group ownership and/or file permissions to be writable by your webserver.  

If you've made it this far, congratulations. There are only two more things to do...
- Cron:
    13) Configure a cron to run every day, or as often as you'd like, to run the app/sendDailyEmail.php script.  
        That script will fire off the email to all active registered users in your database.  Upon replying to that email
        Mailgun will post the email to the route you setup in step 4.  That step will save the message to your mysql database.
    14) Send an email to signup@yourdomain.com
        Within a few seconds, you should see it get posted to your site and create a new user.


Troubleshooting:
- If you aren't getting daily emails, make sure the cron is running.
- If your emails aren't getting added to the database, check the app logs, as well as the Mailgun logs.  
    The Mailgun logs are hosted at mailgun.com
- If you don't want to use mysql, choose whatever datastore you like.  Just create a new config file
    and update the Db.class.php to pull in the correct config file. 
- composer is freaking out...please contact me and I can help you out.
