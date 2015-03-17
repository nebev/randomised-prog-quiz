Randomised Programming Quiz System
====================

What is it?
-----------
Randomised Programming Quiz System Provides a framework for testing Students by giving them a set of Randomised Quiz Questions based off an XML template defined by an educator.
Uses Active Directory for authentication and group membership.
Supports both C++ and Java for question testing.

The project was initially developed by Ben Evans for a Project for a Macquarie University Unit <http://www.mq.edu.au> in 2010.
It was designed to generate randomised questions for quizzes based on fundamental programming concepts in C++.
The initial proposal and motivation behind this program is outlined in initial_proposal.pdf

Since then, the project has been 'ported' (roughly) to the Zend Framework (2011/2012) and has also expanded to include Java Compatibility.
It is important to note that although the program has been refined since that time, it still has some pretty major security holes - use at your own risk! (would recommend a dedicated server)

How to Install
--------------
### Disclaimer
To install this software, you should have a basic knowledge of how DNS, Web Servers, MySQL and the command line works (especially in Linux). The install procedure is outlined in detail below, but you should be prepared to troubleshoot things. If you've got the money, I highly recommend installing [Zend Server](http://www.zend.com/en/products/server/downloads) as it makes installation and configuration *much* easier. The only additional thing you'd have to install is Java.


### Prerequisites
* A Linux or Windows machine (Preferably a dedicated Linux Machine)
* Git [(instructions)](https://www.digitalocean.com/community/articles/how-to-install-git-on-ubuntu-12-04)
+ Apache 2 (nginx should work too)
	- Must also have Apache Mod Rewrite extension installed 
+ PHP with Zend Framework 1 installed
	- ZF1 Can be cloned at <https://github.com/zendframework/zf1>
	- ZF1 must also be in the PHP Include Path - See This [article](http://www.cyberciti.biz/faq/how-do-i-set-php-include-path-in-php-ini-file/  "How to setup PHPs Include Path") for more information
	- GD Must also be installed and enabled
	- LDAP Extensions must also be enabled
* JDK
+ A MySQL Instance
	- Other databases may work too, but none have been tested
* An Active Directory server for Authentication 


### Installation Steps
#### 1. Setting up the prerequisites on an Ubuntu 12.04 box
This section assumes you have a bare-bones Ubuntu box with an internet connection. It walks you through installing everything needed for the Randomised Quiz Software. If you already know how to install all the prerequisites, then you can skip everything here and move onto the next section.

##### 1.1 Apache, MySQL, Git and PHP
Ubuntu makes installation of all these requirements a breeze. Simply run the following:
```bash
sudo apt-get install apache2 apache2-mpm-prefork apache2-utils apache2 libapache2-mod-php5 libapr1 libaprutil1 libdbd-mysql-perl libdbi-perl libnet-daemon-perl libplrpc-perl libpq5 mysql-client-5.5 mysql-common mysql-server mysql-server-5.5 php5-common php5-mysql php5-gd php5-ldap git
```

Answer Yes to anything that asks to be downloaded or installed. MySQL will ask you for a root password. Choose a strong password and remember it, as we're going to have to use it later.

After things are installed, enable the Apache rewrite module
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

##### 1.2 Java
```bash
sudo add-apt-repository ppa:webupd8team/java
sudo apt-get update
sudo apt-get install oracle-java7-installer
```

##### 1.3 Zend Framework
Installing Zend this way in Ubuntu automatically adds Zend to the PHP Include path. You shouldn't have to do anything more.
```bash
sudo apt-get install zend-framework
```


#### 2. Cloning the Randomised Quiz System
Firstly, choose a directory for the quiz system. I'm going to use <code>/var/www/quiz</code>
```bash
sudo mkdir /opt/quizsystem
sudo chown {your_linux_username} /opt/quizsystem
cd /opt/quizsystem
git clone https://github.com/nebev/randomised-prog-quiz.git
```
So now, we actually have a directory called at <code>/opt/quizsystem/randomised-prog-quiz/</code> with the latest Randomised Quiz code. Take note of what directory you used, because you're going to need it for the next section.

#### 3. Setting up an appropriate VHOST entry for Apache
```bash
sudo nano /etc/apache2/sites-enabled/000-default.conf
```
If you were following earlier instructions, Apache will have set up a default website for you. If this is the case, comment out (by prefixing every line with a #) all the existing lines in this file.

Now, put in the following information, substituting the file paths where appropriate
```
# Listen for virtual host requests on all IP addresses - This is only really necessary if you want to run multiple sites on the one machine
NameVirtualHost *:80

<VirtualHost *:80>
	DocumentRoot /opt/quizsystem/randomised-prog-quiz/public
	ServerName rqz.development.com

	<Directory /opt/quizsystem/randomised-prog-quiz/public/>
		Options FollowSymLinks MultiViews
		AllowOverride None
		Order allow,deny
		allow from all
		
		RewriteEngine On

		# Rules
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteRule ^(.*) index.php
	</Directory>
</VirtualHost>
```
Of course, you can configure apache any way you want (custom log locations, SSL etc) Most of that is beyond the scope of this install guide. The important thing to note is the rewrite rules, as well as the fact that we're telling Apache to serve the site in the <code>public</code> sub-directory.

**MAKE SURE YOU TELL THE WEB SERVER TO USE THE <code>public</code> SUB-DIRECTORY; FAILING TO DO SO WILL RESULT IN A MAJOR SECURITY RISK**

#### 4. Preparing your MySQL Database
Next, we're going to create a database and database user user specifically for the Randomised Quiz System.
To do this in Ubuntu, we simply type:
```bash
sudo mysql -u root -p

mysql> CREATE DATABASE quizdb;
mysql> GRANT ALL ON quizdb.* TO quizuser@localhost IDENTIFIED BY 'myquizuserpassword';
```

Now we need to import the Quiz Database Schema.
```
mysql> USE quizdb;
mysql> SOURCE /opt/quizsystem/randomised-prog-quiz/db/schema.sql;
mysql> exit;
Bye
```

#### 5. Creating our Configuration File
The Randomised Quiz System has 2 configuration files associated with it. They are in the <code>application/configs</code> directory and called <code>general.php</code> and <code>application.ini</code>

The Randomised Quiz System comes with example files. It's probably best to copy them and rename them.
```bash
cd /opt/quizsystem/randomised-prog-quiz/application/configs
cp application.example.ini application.ini
cp general.example.php general.php
```

Open up your newly copied <code>application.ini</code> in your favourite Text editor, and begin configuring everything according to your requirements.
I recommend that you set <code>phpSettings.display_startup_errors</code> and <code>phpSettings.display_errors</code> to <code>1</code> when setting the system up; but be sure to disable them once you're satisfied everything is working.

Other Parts that you'll *definitely* have to modify include:
* resources.db.params (The username and password should match what you set up in Installation step 4)
* phpSettings.date.timezone
+ ldap
	- I also recommend setting <code>ldap.usessl</code> and <code>ldap.usetls</code> to <code>0</code> when testing your initial setup.
	- Remember though, that after you verified that authentication works, you should change these back to <code>1</code>

#### 6. Populate the 'xml' directory
Some sample XML files are provided in the `xml-samples` directory. Simply copy this directory and paste it with the name of `xml`. Then tweak the xml files in the `xml` directory as desired.

#### 7. Testing the initial page
Start by navigating your browser (preferably on a client machine) to the address of the Server. If you're using FQDNs and DNS is all working, it should be what you set <code>ServerName</code> in Installation Step 3.

If all has gone according to plan, you should see the welcome page (well done). If not, then see the troubleshooting section.

#### 8. Configuring LDAPS
Once you're up and running with unsecured LDAP, the time will come when you want to change the configuration to Secured LDAP (LDAPS). The appropriate options are in <code>application.ini</code> under the <code>ldap</code> section.
However, usually on Linux and Windows boxes, it's not a simple case of just enabling these things, due to the fact that LDAPS requires signed certificates.

If you're using Ubuntu, you should start with
```bash
sudo apt-get install libsasl2-modules-gssapi-mit
```
Then you should download the appropriate certificates for your Active Directory server and configure them with OpenLDAP.
The instructions for doing this are really beyond this install guide, but eventually your <code>/etc/openldap/ldap.conf</code> file will look something like this:
```
TLS_CACERT      /etc/ssl/certs/ca-certificates.crt
```
There's also the less secure (but much simpler way) of making your <code>/etc/openldap/ldap.conf</code> file look like this:
```
# Instruct client to NOT request a server's cert.
TLS_REQCERT never
```


Troubleshooting
---------------
+ I get a 404 when I visit the page
	- This could be because Apache isn't running (<code>sudo service apache2 restart</code>)
	- You may have configured the Virtual server incorrectly
	- Make sure you can Ping the server
+ I get a 500 error
	- 500 errors are quite difficult to troubleshoot, so turn on <code>phpSettings.display_startup_errors</code> and <code>phpSettings.display_errors</code> in your <code>application.ini</code>
	- Look at the Apache Error Logs
	- Look at the PHP Error Logs
+ I get include errors mentioning Zend
	- Either the Zend Framework isn't installed properly, or it isn't in your PHP include path
+ I get LDAP Errors
	- If you're using LDAPS (Secure), ensure you've followed the instructions in Installation step 7
	- Make sure that your server can Ping your Active Directory server
	- Ensure the PHP LDAP extension is configured working (use phpinfo)
+ I can't login using my Active Directory credentials
	- This can be complex. More than likely, you haven't configured your LDAP settings correctly. Consult the example file and/or your Active Directory Administrator to make sure you've got the right settings
+ Everything works, but the question code never shows up for students
	- More than likely GD isn't configured correctly. Ensure it's working by using phpinfo
+ I get SQLSTATE[HY000] [2002] Connection refused or similar errors
	- Your database configuration is wrong/bad
+ I can log in, but can't see any administration options
	- Administrators are determined by Active Directory groups.
	- Essentially, you'll need to modify the defined constant <code>QUIZ_ADMINISTRATORS</code> found in <code>general.php</code>. Typically in a University environment, this group is something like <code>All Staff</code>
	- For further investigation, click on your Username once you've logged in - this should show you what groups the System believe you're part of
	- If you don't get any groups in this screen (but can log in), then your Base DN is probably wrong.


More Information
----------------
For More Information and background, see initial_proposal.pdf
All stylesheets are Copyright Macquarie University

Authors:
	Ben Evans <ben@nebev.net>
	
Question Contributors:
	Christophe Doche <christophe.doche@mq.edu.au>
	Gaurav Gupta <gaurav.gupta@mq.edu.au>
	Ben Evans <ben@nebev.net>
