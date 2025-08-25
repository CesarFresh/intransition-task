# CSV Importer (.env) → importTest.tblProductData

**.env** driven DB config using `vlucas/phpdotenv` v2.

## Setup (bare metal)

```bash
composer install
cp .env.example .env # fill DB_* values

# Provision DB/table
mysql -u root -p < migrations/001_tblProductData_prepare.sql
mysql -u root -p importTest < migrations/002_tblProductData_add_price_stock.sql

# For testing:
php bin/console products:import samples/supplier_products.csv --test 

# To insert real values:
php bin/console products:import /path/to/your.csv
```
