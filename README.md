# ElectronicInvoicing
Facturación Electrónica V2.0

1. Clone project
2. Go to the folder application using ```cd``` command on your cmd or terminal
3. Run ```composer install``` on your cmd or terminal
4. Copy ```.env.example``` file to ```.env``` on the root folder. You can type ```copy .env.example .env``` if using command prompt Windows or ```cp .env.example .env``` if using terminal, Ubuntu
5. Open your .env file and change the database name (DB_DATABASE) to whatever you have, username (DB_USERNAME) and password (DB_PASSWORD) field correspond to your configuration.
6. By default, the username is root and you can leave the password field empty. (This is for Xampp)
7. By default, the username is root and password is also root. (This is for Lamp)
8. Run php artisan key:generate
9. Run php artisan migrate
10. Run php artisan serve
11. Go to localhost:8000

