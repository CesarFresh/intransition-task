<?php
namespace App\Domain\Rule;

use App\Domain\Entity\Product;

class OverPriceLimitRule implements FilterRuleInterface
{
	private $max;

	public function __construct($max)
	{
		$this->max = (float)$max;
	}

	public function shouldSkip(Product $p, &$reason)
	{
		$price = (float)$p->getPrice();

		if ($price > $this->max) {
			$reason = sprintf('Price %.2f > %.2f', $price, $this->max);
			return true;
		}

		return false;
	}
}