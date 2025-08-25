<?php
use Dotenv\Dotenv;
use App\Application\Command\ImportProductsCommand;
use App\Domain\Rule\LowPriceLowStockRule;
use App\Domain\Rule\OverPriceLimitRule;
use App\Domain\Rule\DiscontinuedTransformRule;
use App\Infrastructure\Csv\CsvRowMapper;
use App\Infrastructure\Persistence\ProductRepository;
use App\Infrastructure\Service\ProductImporter;

// Load .env from project root
$dotenv = new Dotenv(__DIR__ . '/../../');
$dotenv->load();

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$charset = getenv('DB_CHARSET') ?: 'latin1';

$dsn = sprintf(
	'mysql:host=%s;port=%s;dbname=%s;charset=%s',
	$host,
	$port,
	$db,
	$charset
);

$pdo = new PDO(
	$dsn,
	$user,
	$pass,
	array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
	)
);

$repository = new ProductRepository($pdo);

$mapper = new CsvRowMapper(
	array(',', '.'),
	array(
		'Product Code' => 'sku',
		'Product Name' => 'name',
		'Product Description' => 'description',
		'Stock' => 'stock',
		'Cost in GBP' => 'price',
		'Discontinued' => 'discontinued',
	)
);

$filterRules = array(
	new LowPriceLowStockRule(5.00, 10),
	new OverPriceLimitRule(1000.00),
);

$transformRules = array(
	new DiscontinuedTransformRule()
);

$importer = new ProductImporter(
	$repository,
	$mapper,
	$filterRules,
	$transformRules
);

$command = new ImportProductsCommand($importer);

return array(
	'pdo' => $pdo,
	'repository.product' => $repository,
	'mapper.csv_row' => $mapper,
	'service.importer' => $importer,
	'command.import' => $command,
);