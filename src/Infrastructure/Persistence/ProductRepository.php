<?php
namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Product;
use PDO;

class ProductRepository
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function upsert(Product $p)
	{
		$sql = 'INSERT INTO tblProductData 
			(strProductCode, strProductName, strProductDesc, decPrice, intStockLevel, dtmAdded, dtmDiscontinued)
			VALUES (:code, :name, :desc, :price, :stock, :added, :discontinued)
			ON DUPLICATE KEY UPDATE 
				strProductName = VALUES(strProductName), 
				strProductDesc = VALUES(strProductDesc), 
				decPrice = VALUES(decPrice), 
				intStockLevel = VALUES(intStockLevel), 
				dtmDiscontinued = VALUES(dtmDiscontinued)';

		$stmt = $this->pdo->prepare($sql);
		$now = date('Y-m-d H:i:s');

		$stmt->bindValue(':code', $p->getCode());
		$stmt->bindValue(':name', $p->getName());
		$stmt->bindValue(':desc', $p->getDesc());
		$stmt->bindValue(':price', $p->getPrice());
		$stmt->bindValue(':stock', $p->getStock(), PDO::PARAM_INT);
		$stmt->bindValue(':added', $now);
		$stmt->bindValue(':discontinued', $p->isDiscontinued() ? ($p->getDiscontinuedAt() ?: $now) : null);

		$stmt->execute();
	}
}