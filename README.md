# Music Collaboration Environment MVP

A collaborative online music platform that can be deployed to a web server that supports PHP and has a MariaDB (Fork of MySQL) database.
This platform allows musicians and music educators to publish their music and collaborate with each other.
This is still a demo/mvp version, which does not allow for user registration so example user login buttons are provided
to show the platform functionality. Users are given the roles of "Group Leader" or "Member," allowing Group Leaders
control of what is published publicly and limiting Member abilities to only participating in collaborations between other users.

## Installation/Deployment

Before Installation: Have a web server that supports PHP and a MariaDB database (online or hosted locally). If there are
any issues during or after deployment, then compare your PHP and MariaDB version with the one used during development,
listed in the "Development Environment" section below.

### File Setup
1. Download the repository. Extract all repository files inside of your web server root directory, often called "public_html."
2. Extract all items in "dependencies.zip" inside of the "songshome" directory. You can delete "dependencies.zip" after extraction.

### Database Schema Import
Use the "songshome.sql" file to import the database schema inside of your MariaDB database environment. After import, the "songshome.sql"
can be removed. Currently, the "songshome.sql" file does not know the name of your database. You can specify the name of your database by
updating the following line with the name of your empty database:

  ```USE `your_database_name`;```
  
  Alternatively, you can remove the above line and paste the following lines to have the "songshome.sql" file create a new database for you 
  during import:
  
  ```CREATE DATABASE IF NOT EXISTS `songshome` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
  USE `songshome`;``` 

### Connecting to Your Database
Edit the file inside "/songshome/includes/" titled "db.php" to connect the website with the database you imported in the previous step.
The following lines need to be updated:
```
$host = 'your_website_domain_name';
$db   = 'your_database_name';
$user = 'your_database_username';
$pass = 'your_database_password';
$charset = 'utf8mb4'; // Leave this as is.
```

### Done!

## Development Environment
-PHP Version: 7.4.7
-MariaDB Version: 10.4.13 
-phpMyAdmin Version: 5.0.2

