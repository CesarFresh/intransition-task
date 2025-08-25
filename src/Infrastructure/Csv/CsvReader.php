<?php
namespace App\Infrastructure\Csv;

class CsvReader implements \IteratorAggregate
{
	private $path;
	private $delimiter;
	private $encoding;

	public function __construct($path, $delimiter = null, $encoding = null)
	{
		$this->path = $path;
		$this->delimiter = $delimiter;
		$this->encoding = $encoding ?: 'auto';
	}

	public function getIterator()
	{
		if (!is_readable($this->path)) {
			throw new \RuntimeException('CSV not readable: ' . $this->path);
		}

		$h = fopen($this->path, 'rb');
		if (!$h) {
			throw new \RuntimeException('Unable to open CSV: ' . $this->path);
		}

		$first = '';
		if (($chunk = fgets($h)) !== false) {
			$first = $chunk;
		}

		if ($first === '') {
			fclose($h);
			return new \ArrayIterator(array());
		}

		if (substr($first, 0, 3) === "\xEF\xBB\xBF") {
			$first = substr($first, 3);
		}

		$del = $this->delimiter;
		if ($del === null) {
			$c = array(',', ';', "\t");
			$cnt = array();
			foreach ($c as $x) {
				$cnt[$x] = substr_count($first, $x);
			}
			arsort($cnt);
			$del = key($cnt);
		}

		$enc = $this->encoding;
		$convert = false;
		if ($enc === 'auto') {
			if (function_exists('mb_detect_encoding')) {
				$d = mb_detect_encoding($first, 'UTF-8, ISO-8859-1, Windows-1252', true);
				$enc = $d ? $d : 'UTF-8';
			} else {
				$enc = 'UTF-8';
			}
		}
		if (strtoupper($enc) !== 'UTF-8') {
			$convert = true;
		}

		rewind($h);
		$header = null;
		$ln = 0;
		$rows = array();

		while (($data = fgetcsv($h, 0, $del)) !== false) {
			$ln++;
			if ($ln === 1) {
				if (isset($data[0])) {
					$data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
				}
				$header = $this->norm($data, $convert, $enc);
				continue;
			}
			$row = $this->norm($data, $convert, $enc);
			$assoc = $this->assoc($header, $row);
			$assoc['__line'] = $ln;
			$rows[] = $assoc;
		}

		fclose($h);
		return new \ArrayIterator($rows);
	}

	private function norm($row, $convert, $enc)
	{
		$out = array();
		for ($i = 0; $i < count($row); $i++) {
			$v = $row[$i];
			if ($convert && function_exists('iconv')) {
				$v = @iconv($enc, 'UTF-8//TRANSLIT', $v);
			}
			$v = rtrim($v, "\r\n");
			$out[] = $v;
		}
		return $out;
	}

	private function assoc($header, $row)
	{
		$a = array();
		$n = count($header);
		for ($i = 0; $i < $n; $i++) {
			$k = isset($header[$i]) ? $header[$i] : 'col_' . $i;
			$a[$k] = isset($row[$i]) ? $row[$i] : null;
		}
		return $a;
	}
}