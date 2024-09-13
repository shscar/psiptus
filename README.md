# psiptus


Install & Run Phinx untuk membuat tabel database
install Phinx:
    php composer.phar require robmorgan/phinx

your first migration:
    vendor/bin/phinx init .
    vendor/bin/phinx migrate -e development

    Membuat folder
        mkdir -p db/migrations db/seeds