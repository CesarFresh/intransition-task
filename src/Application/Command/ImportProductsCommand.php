<?php
namespace App\Application\Command;

use App\Infrastructure\Service\ProductImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProductsCommand extends Command
{
	protected static $defaultName = 'products:import';
	private $importer;
	public function __construct(ProductImporter $importer)
	{
		parent::__construct();
		$this->importer = $importer;
	}

	protected function configure()
	{
		$this
			->setDescription('Import supplier CSV into importTest.tblProductData with business rules & reporting.')
			->addArgument('file', InputArgument::REQUIRED, 'Path to supplier CSV file')
			->addOption('test', null, InputOption::VALUE_NONE, 'Run without writing to DB (test mode)')
			->addOption('delimiter', null, InputOption::VALUE_REQUIRED, 'CSV delimiter override (auto-detect by default)')
			->addOption('encoding', null, InputOption::VALUE_REQUIRED, 'Source file encoding (auto by default)');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$file = $input->getArgument('file');
		$testMode = (bool)$input->getOption('test');
		$delimiter = $input->getOption('delimiter');
		$encoding = $input->getOption('encoding');

		$io->title('Supplier Products Import');
		$io->text('File: ' . $file);

		if ($testMode) {
			$io->comment('TEST mode — no DB writes will occur.');
		}

		$report = $this->importer->import($file, $testMode, $delimiter, $encoding);

		$io->section('Summary');
		$io->listing(array(
			'Processed: ' . $report->getProcessedCount(),
			'Inserted/Updated: ' . $report->getSuccessCount(),
			'Skipped (rules): ' . $report->getSkippedCount(),
			'Failed (invalid/DB): ' . $report->getFailedCount(),
		));

		if ($report->getSkippedCount() > 0) {
			$io->section('Skipped Items');
			$rows = array();
			foreach ($report->getSkipped() as $s) {
				$rows[] = array($s['line'], $s['sku'], $s['reason']);
			}
			$io->table(array('Line', 'Product Code', 'Reason'), $rows);
		}

		if ($report->getFailedCount() > 0) {
			$io->section('Failed Rows');
			$rows = array();
			foreach ($report->getFailed() as $f) {
				$rows[] = array($f['line'], $f['sku'], $f['error']);
			}
			$io->table(array('Line', 'Product Code', 'Error'), $rows);
		}

		return $report->getFailedCount() > 0 ? 1 : 0;
	}
}