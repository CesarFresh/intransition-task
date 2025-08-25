<?php
namespace App\Domain\Rule;

use App\Domain\Entity\Product;

class DiscontinuedTransformRule
{
	public function apply(Product $p)
	{
		if ($p->isDiscontinued() && !$p->getDiscontinuedAt()) {
			$p->setDiscontinuedAt(date('Y-m-d H:i:s'));
		}
	}
}