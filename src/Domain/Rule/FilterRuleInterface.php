<?php

namespace App\Domain\Rule;

use App\Domain\Entity\Product;

interface FilterRuleInterface { 
    public function shouldSkip(Product $product, &$reason);
}
