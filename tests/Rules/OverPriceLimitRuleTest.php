<?php
use PHPUnit\Framework\TestCase;
use App\Domain\Rule\OverPriceLimitRule;
use App\Domain\Entity\Product;

class OverPriceLimitRuleTest extends TestCase
{
	public function testSkipsOver()
	{
		$r = new OverPriceLimitRule(1000.00);
		$p = new Product('P', 'N', '', '1000.01', 1, false, null);
		$reason = '';
		$this->assertTrue($r->shouldSkip($p, $reason));
	}
}
