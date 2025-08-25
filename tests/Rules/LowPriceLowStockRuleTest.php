<?php
use PHPUnit\Framework\TestCase;
use App\Domain\Rule\LowPriceLowStockRule;
use App\Domain\Entity\Product;

class LowPriceLowStockRuleTest extends TestCase
{
	public function testSkipsBothUnder()
	{
		$r = new LowPriceLowStockRule(5.00, 10);
		$p = new Product('P', 'N', '', '4.99', 9, false, null);
		$reason = '';
		$this->assertTrue($r->shouldSkip($p, $reason));
		$this->assertNotEmpty($reason);
	}
}