<?php
use PHPUnit\Framework\TestCase;
use App\Infrastructure\Service\ProductImporter;
use App\Infrastructure\Csv\CsvRowMapper;
use App\Domain\Rule\LowPriceLowStockRule;
use App\Domain\Rule\OverPriceLimitRule;
use App\Domain\Rule\DiscontinuedTransformRule;
use App\Domain\Entity\Product;

class FakeRepo
{
	public $count = 0;

	public function upsert(Product $p)
	{
		$this->count++;
	}
}

class ProductImporterTest extends TestCase
{
	public function testImport()
	{
		$csv = tempnam(sys_get_temp_dir(), 'csv');
		file_put_contents($csv,
			"Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued\n" .
			"P1,Name,Desc,9,4.99,\n" .
			"P2,Name,Desc,1,1001,\n" .
			"P3,Name,Desc,5,25,yes\n"
		);

		$repo = new FakeRepo();
		$mapper = new CsvRowMapper(
			array(',', '.'),
			array(
				'Product Code' => 'sku',
				'Product Name' => 'name',
				'Product Description' => 'description',
				'Stock' => 'stock',
				'Cost in GBP' => 'price',
				'Discontinued' => 'discontinued'
			)
		);

		$rules = array(
			new LowPriceLowStockRule(5.00, 10),
			new OverPriceLimitRule(1000.00)
		);
		$trs = array(new DiscontinuedTransformRule());

		$imp = new ProductImporter($repo, $mapper, $rules, $trs);
		$r = $imp->import($csv, false, ',');

		$this->assertEquals(3, $r->getProcessedCount());
		$this->assertEquals(2, $r->getSkippedCount());
		$this->assertEquals(1, $r->getSuccessCount());

		@unlink($csv);
	}
}