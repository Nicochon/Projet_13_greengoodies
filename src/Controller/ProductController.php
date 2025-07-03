<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ProductController extends AbstractController
{
    /**
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/products.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Display product by id
     * @param Product $product
     * @return Response
     */
    #[Route('/products/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        return $this->render('product/product.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function getProductApi(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();

        $json = $serializer->serialize($products, 'json', ['groups' => 'product:read']);

        return new JsonResponse($json, 200, [], true);
    }
}