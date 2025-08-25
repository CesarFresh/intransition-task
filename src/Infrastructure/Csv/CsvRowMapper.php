<?php
namespace App\Infrastructure\Csv;

use App\Domain\Entity\Product;

class CsvRowMapper
{
	private $decimalSeparators;
	private $aliases;

	public function __construct(array $decimalSeparators, array $aliases)
	{
		$this->decimalSeparators = $decimalSeparators;
		$this->aliases = $aliases;
	}

	public function map(array $row)
	{
		$line = isset($row['__line']) ? $row['__line'] : null;

		// Normalize keys to internal
		$n = array();
		foreach ($row as $k => $v) {
			if ($k === '__line') continue;
			$key = isset($this->aliases[$k]) ? $this->aliases[$k] : $k;
			$n[$key] = $v;
		}

		$code = $this->req($n, 'sku', 'Product Code', $line);
		$name = $this->req($n, 'name', 'Product Name', $line);
		$desc = isset($n['description']) ? trim($n['description']) : '';

		$stockRaw = isset($n['stock']) ? trim($n['stock']) : '';
		$stock = ($stockRaw === '' ? 0 : $this->toInt($stockRaw, 'Stock', $line));

		$priceRaw = $this->req($n, 'price', 'Cost in GBP', $line);
		$price = $this->toPrice($priceRaw);
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException(
				'Invalid price: ' . $priceRaw . ($line ? (' (line ' . $line . ')') : '')
			);
		}
		$price = number_format((float)$price, 2, '.', '');

		$discRaw = isset($n['discontinued']) ? strtolower(trim($n['discontinued'])) : '';
		$disc = in_array($discRaw, array('1', 'true', 't', 'y', 'yes'), true);
		$discAt = null;

		// Enforce legacy widths
		$code = substr($code, 0, 10);
		$name = substr($name, 0, 50);
		$desc = substr($desc, 0, 255);

		return new Product($code, $name, $desc, $price, $stock, $disc, $discAt);
	}

	private function req($row, $key, $label, $line)
	{
		if (!isset($row[$key]) || trim($row[$key]) === '') {
			throw new \InvalidArgumentException(
				$label . ' is required' . ($line ? (' (line ' . $line . ')') : '')
			);
		}
		return trim($row[$key]);
	}

	private function toInt($raw, $label, $line)
	{
		$raw = preg_replace('/\s+/u', '', $raw);
		if ($raw === '' || !preg_match('/^-?\d+$/', $raw)) {
			throw new \InvalidArgumentException(
				'Invalid ' . $label . ': ' . $raw . ($line ? (' (line ' . $line . ')') : '')
			);
		}
		return (int)$raw;
	}

	private function toPrice($raw)
	{
		$n = preg_replace('/[^0-9,\.\-]/', '', trim($raw));
		if (strpos($n, ',') !== false && strpos($n, '.') !== false) {
			return str_replace(',', '', $n);
		}
		if (strpos($n, ',') !== false) {
			$n = str_replace('.', '', $n);
			$n = str_replace(',', '.', $n);
		}
		return $n;
	}
}
