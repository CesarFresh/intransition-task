<?php
namespace App\Domain\Service;

class ImportReport
{
	private $processed = 0;
	private $success = 0;
	private $skipped = array();
	private $failed = array();

	public function incProcessed() { $this->processed++; }
	public function incSuccess() { $this->success++; }

	public function addSkipped($line, $sku, $reason)
	{
		$this->skipped[] = array(
			'line' => $line,
			'sku' => $sku,
			'reason' => $reason
		);
	}

	public function addFailed($line, $sku, $error)
	{
		$this->failed[] = array(
			'line' => $line,
			'sku' => $sku,
			'error' => $error
		);
	}

	public function getProcessedCount() { return $this->processed; }
	public function getSuccessCount() { return $this->success; }
	public function getSkippedCount() { return count($this->skipped); }
	public function getFailedCount() { return count($this->failed); }
	public function getSkipped() { return $this->skipped; }
	public function getFailed() { return $this->failed; }
}