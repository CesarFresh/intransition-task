USE importTest;

ALTER TABLE `tblProductData`
ADD COLUMN `decPrice` DECIMAL(12,2) NULL AFTER `strProductDesc`,
ADD COLUMN `intStockLevel` INT(10) UNSIGNED NULL AFTER `decPrice`;