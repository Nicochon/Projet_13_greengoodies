<?php

namespace App\Service;

use App\Repository\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getLastProducts(int $limit = 9): array
    {
        return $this->productRepository->findBy([], ['id' => 'DESC'], $limit);
    }
}
