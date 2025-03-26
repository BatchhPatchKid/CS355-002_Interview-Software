Hi :) - brian
1. To use, you must have XAMPP installed and working.
2. Once XAMPP is working and the test version of the project is in htdocs, then you can launch XAMPP
3. To login into the database:
mysql -u root -p
USE databaseCS355;
SHOW TABLES;
4. If no tables are shown, then the database has not been initialized. As of 3/25/2025, you must 
execute the file "initializeDatabase.php" by typing into the browser: http://localhost/CS355-Test-Website/initializeDatabase.php
5. The database and XAMPP should be working at this point. From here, you should be able to use

the website as normal.