# ElectronicInvoicing
Facturación Electrónica V2.0

1. Clone project
2. Go to the folder application using ```cd``` command on your cmd or terminal
3. Run ```composer install``` on your cmd or terminal
4. Copy ```.env.example``` file to ```.env``` on the root folder. You can type ```copy .env.example .env``` if using command prompt Windows or ```cp .env.example .env``` if using terminal, Ubuntu
5. Open your ```.env``` file and change the following parameters: ```DB_DATABASE```, ```DB_USERNAME```, ```DB_PASSWORD```, and other parameters that you consider.
6. Create your database in your MySQL server with the previous parameters.
7. Run ```php artisan key:generate```.
8. Run ```php artisan migrate```.
9. Run ```php artisan serve```.
10. Go to ```localhost:8000```

