<?php
namespace App\Infrastructure\Service;

use App\Domain\Rule\FilterRuleInterface;
use App\Domain\Service\ImportReport;
use App\Infrastructure\Csv\CsvReader;
use App\Infrastructure\Csv\CsvRowMapper;

class ProductImporter
{
	private $repo;
	private $mapper;
	private $filters;
	private $transforms;

	public function __construct($repo, CsvRowMapper $mapper, array $filters, array $transforms)
	{
		$this->repo = $repo;
		$this->mapper = $mapper;
		$this->filters = $filters;
		$this->transforms = $transforms;
	}

	public function import($file, $test = false, $delimiter = null, $encoding = null)
	{
		$r = new ImportReport();
		$reader = new CsvReader($file, $delimiter, $encoding);

		foreach ($reader as $row) {
			$r->incProcessed();
			$line = isset($row['__line']) ? $row['__line'] : null;

			try {
				$p = $this->mapper->map($row);

				foreach ($this->transforms as $t) {
					$t->apply($p);
				}

				$reason = '';
				$skip = false;
				foreach ($this->filters as $f) {
					if ($f->shouldSkip($p, $reason)) {
						$skip = true;
						break;
					}
				}

				if ($skip) {
					$r->addSkipped($line, $p->getCode(), $reason);
					continue;
				}

				if (!$test) {
					$this->repo->upsert($p);
				}
				$r->incSuccess();

			} catch (\Exception $e) {
				$sku = isset($row['Product Code']) ? $row['Product Code'] : (isset($row['sku']) ? $row['sku'] : '');
				$r->addFailed($line, $sku, $e->getMessage());
			}
		}

		return $r;
	}
}
