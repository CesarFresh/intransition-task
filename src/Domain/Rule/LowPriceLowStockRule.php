<?php
namespace App\Domain\Rule;

use App\Domain\Entity\Product;

class LowPriceLowStockRule implements FilterRuleInterface
{
	private $price;
	private $stock;

	public function __construct($price, $stock)
	{
		$this->price = (float)$price;
		$this->stock = (int)$stock;
	}

	public function shouldSkip(Product $p, &$reason)
	{
		$price = (float)$p->getPrice();
		$stock = (int)$p->getStock();

		if ($price < $this->price && $stock < $this->stock) {
			$reason = sprintf(
				'Price %.2f < %.2f AND Stock %d < %d',
				$price,
				$this->price,
				$stock,
				$this->stock
			);
			return true;
		}

		return false;
	}
}
