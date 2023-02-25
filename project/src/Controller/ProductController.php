<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class ProductController extends AbstractController
{
    #[Route('/products', name: 'products_show', methods:"GET")]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $products = $entityManager->getRepository(Product::class)->findAll();

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
            ];
        }

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('All the products'),
            'data'    => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/product', name: 'product_create', methods:"POST")]
    public function create(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();

        if ($request->request->get('name')) {
            $product->setName($request->request->get('name'));
        }

        if ($request->request->get('price')) {
            $product->setPrice($request->request->get('price'));
        }

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            // Return a JSON error response with a 400 status code
            return new JsonResponse([
                'error' => 'Invalid request data', 'errors' => $errors
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('Created new Product successfully with id ' . $product->getId()),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/product/{id}', name: 'product_show', methods:"GET")]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            // Return an error response if the product doesn't exist
            return new JsonResponse([
                'error' => sprintf('Product with ID %d not found', $id),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data =  [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
        ];

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('Product with ID %d', $id),
            'data'    => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/product/{id}', name: 'product_update', methods:"PUT")]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            // Return an error response if the product doesn't exist
            return new JsonResponse([
                'error' => sprintf('Product with ID %d not found', $id),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($request->request->get('name')) {
            $product->setName($request->request->get('name'));
        }

        if ($request->request->get('price')) {
            $product->setPrice($request->request->get('price'));
        }

        $entityManager->flush();

        $data =  [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
        ];

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('Updated a Product successfully with ID %d ', $id),
            'data'    => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/product/{id}', name: 'product_delete', methods:"DELETE")]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            // Return an error response if the product doesn't exist
            return new JsonResponse([
                'error' => sprintf('Product with ID %d not found', $id),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('Deleted a Product successfully with ID %d ', $id)
        ], JsonResponse::HTTP_OK);
    }
}
