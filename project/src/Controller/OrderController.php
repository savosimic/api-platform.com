<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class OrderController extends AbstractController
{
    #[Route('/orders', name: 'order_create', methods:"POST")]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        // Get the products to add to the order from the request body
        $productIds = $request->get('productIds');
        if (empty($productIds)) {
            // Return an error response if there are no products in the request body
            return new JsonResponse([
                'error' => 'No products found in the request',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $entityManager = $doctrine->getManager();

        // Add each product to the order
        foreach ($productIds as $productId) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if (!$product) {
                // Return an error response if the product doesn't exist
                return new JsonResponse([
                    'error' => sprintf('Product with ID %d not found', $productId),
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            $order->addProduct($product);
            $order->setAmount($order->getTotal());
        }

        $entityManager->persist($order);
        $entityManager->flush();

        // Return a success response with the ID of the new order
        return new JsonResponse([
            'orderId' => $order->getId(),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/orders/{id}/add-product', name: 'order_add_product', methods: ['POST'])]
    public function addProduct(ManagerRegistry $doctrine, Request $request, Order $order): JsonResponse
    {
        // Get the product ID from the request body
        $productId = $request->get('productId');
        if (empty($productIds)) {
            // Return an error response if there are no products in the request body
            return new JsonResponse([
                'error' => 'No products found in the request',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check if the product exists
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            // Return an error response if the product doesn't exist
            return new JsonResponse([
                'error' => sprintf('Product with ID %d not found', $productId),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // Add the product to the order
        $order->addProduct($product);
        $order->setAmount($order->getTotal());

        // Save the changes to the database
        $entityManager->persist($order);
        $entityManager->flush();

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('Product with ID %d added to order with ID %d', $productId, $order->getId()),
        ]);
    }

    #[Route('/product/{id}/orders', name: 'product_orders', methods:"GET")]
    public function orders(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            // Return an error response if the product doesn't exist
            return new JsonResponse([
                'error' => sprintf('Product with ID %d not found', $id),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [];
        foreach ($product->getOrders() as $order) {
            $data[] = [
                'id' => $order->getId(),
            ];
        }

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('All the orders that have a  product in them with ID %d ', $id),
            'data'    => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/order/{id}/products', name: 'order_products', methods:"GET")]
    public function products(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $order = $entityManager->getRepository(Order::class)->find($id);
        if (!$order) {
            // Return an error response if the order doesn't exist
            return new JsonResponse([
                'error' => sprintf('Order with ID %d not found', $id),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [];
        foreach ($order->getProducts() as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
            ];
        }

        // Return a success response
        return new JsonResponse([
            'message' => sprintf('All the products in a specific order with ID %d ', $id),
            'data'    => $data
        ], JsonResponse::HTTP_OK);
    }
}
