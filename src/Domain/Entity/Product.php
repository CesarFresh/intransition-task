<?php
namespace App\Domain\Entity;

class Product
{
	private $code; // strProductCode (VARCHAR 10)
	private $name; // strProductName (VARCHAR 50)
	private $desc; // strProductDesc (VARCHAR 255)
	private $price; // decPrice (DECIMAL 12,2)
	private $stock; // intStockLevel (INT UNSIGNED)
	private $discontinued; // bool
	private $discontinuedAt; // dtmDiscontinued (nullable)

	public function __construct($code, $name, $desc, $price, $stock, $discontinued, $discontinuedAt)
	{
		$this->code = $code;
		$this->name = $name;
		$this->desc = $desc;
		$this->price = $price;
		$this->stock = (int)$stock;
		$this->discontinued = (bool)$discontinued;
		$this->discontinuedAt = $discontinuedAt;
	}

	public function getCode() { return $this->code; }
	public function getName() { return $this->name; }
	public function getDesc() { return $this->desc; }
	public function getPrice() { return $this->price; }
	public function getStock() { return $this->stock; }
	public function isDiscontinued() { return $this->discontinued; }
	public function getDiscontinuedAt() { return $this->discontinuedAt; }

	public function setDiscontinued($val) { $this->discontinued = (bool)$val; }
	public function setDiscontinuedAt($dt) { $this->discontinuedAt = $dt; }
}