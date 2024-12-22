# psiptus

Install & Run Phinx untuk membuat tabel database
Install Phinx:

- php composer.phar require robmorgan/phinx

Your first migration:

- vendor/bin/phinx init .
- vendor/bin/phinx migrate -e development

Membuat folder

- mkdir -p db/migrations db/seeds

Instalasi Library PhpSpreadsheet

- composer require phpoffice/phpspreadsheet
