![screen1](./screenshots/GNUAIS-web-interface_screen1.JPG)
![screen2](./screenshots/GNUAIS-web-interface_screen2.JPG)
![screen3](./screenshots/GNUAIS-web-interface_screen3.JPG)
# GNUAIS-web-interface
This script is a Simple Web interface for GNUAIS receiver which runs on Linux Machines or Raspberry and shows data of received AIS Stations on a table and on a Map.

# INSTALLATION
- Install GNUAIS as described here: https://github.com/rubund/gnuais
- Install MySQL Server. I used MariaDB: https://mariadb.org/ . Follow this guide: https://pimylifeup.com/raspberry-pi-mysql/
- Create gnuais database using this script: https://github.com/rubund/gnuais/blob/master/create_table.sql ( mysql < create_table.sql )
- Configure the MySQL parameters of /etc/gnuais.conf in order to access to the database created in the previous step
- Install a web server (I used Lighttpd):
```
sudo apt-get install lighttpd
```
- Install php:
```
sudo apt-get install php7.3-common php7.3-cgi php  (valid for raspbian today...always verify last version before installation!)
```
- Install Openlayers, the javascript library for displaying maps into web page. Follow this steps:
```
go into the main folder of your web server
sudo apt-get update
sudo apt-get install npm
npm install ol
```
- Verify that a sub-folder named "modules" has been created into web server main folder
- Enable cgi-php:
```
sudo lighty-enable-mod fastcgi
sudo lighty-enable-mod fastcgi-php
sudo service lighttpd force-reload
```
- Download the content of this github in the main folder of your web server (es: /var/www/html/ )
- Edit config.php with your station coordinates (used for ships distance calculations) and database credentials
- Enjoy

For further info about my AIS station: https://iz7boj.wordpress.com/2023/02/22/my-ais-receiving-station/

For help write to iz7boj [at] gmail.com

# Software stability

This is a BETA software. It can contain some bugs and may be written in non-efficient way. Please contact author if you find any bug.

# License
You can modify this program, but please give a credit to original author (IZ7BOJ). Project is free for non-commercial use. You can modify and publish this software, but you have to put an information about original author.
