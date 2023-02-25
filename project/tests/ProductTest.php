<?php

namespace App\Tests;

use App\Controller\ProductController;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductTest extends WebTestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }

    public function testIndex()
    {
        // Mock the EntityManagerInterface
        $entityManager = $this->createMock(EntityManagerInterface::class);

        // Create an array of mock Product objects
        $products = [
            (new Product())->setName('Product 1'),
            (new Product())->setName('Product 2'),
            (new Product())->setName('Product 3'),
        ];

        // Mock the getRepository method to return the mock products array
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->createMock(ProductRepository::class));

        $entityManager->getRepository(Product::class)
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        // Mock the ManagerRegistry to return the EntityManagerInterface mock
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        // Create an instance of the controller and call the index action
        $controller = new ProductController();
        $response = $controller->index($doctrine);

        // Assert that the response has the expected data
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);

        $this->assertEquals('All the products', $data['message']);

        $this->assertCount(3, $data['data']);

        $this->assertArrayHasKey('id', $data['data'][0]);
        $this->assertArrayHasKey('name', $data['data'][0]);
    }

    public function testDeleteProduct(): void
    {
        // Create a mock EntityManager
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();

        // Create a mock ManagerRegistry and configure it to return the mock EntityManager
        $doctrine = $this->getMockBuilder('Doctrine\Persistence\ManagerRegistry')->getMock();
        $doctrine->method('getManager')->willReturn($entityManager);

        // Create a mock Product entity
        $product = (new Product())->setName('Product 1');

        // Configure the mock EntityManager to find the Product entity and remove it
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->getMockBuilder('Doctrine\Persistence\ObjectRepository')->getMock());
        $entityManager->getRepository(Product::class)->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($product);
        $entityManager->expects($this->once())
            ->method('flush');

        // Create a ProductController instance and call the delete() method with ID 1
        $productController = new ProductController();
        $response = $productController->delete($doctrine, 1);

        // Assert that the response is a success response with the correct message
        $expectedResponse = new JsonResponse([
            'message' => 'Deleted a Product successfully with ID 1 ',
        ], JsonResponse::HTTP_OK);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testShowProduct(): void
    {
        // Create a mock Product object
        $product = $this->getMockBuilder('App\Entity\Product')->getMock();
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getPrice')->willReturn(10.99);

        // Create a mock repository and configure it to return the mock Product object
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->method('find')->willReturn($product);


        // Create a mock EntityManager
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);

        // Create a mock ManagerRegistry and configure it to return the mock EntityManager
        $doctrine = $this->getMockBuilder('Doctrine\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->method('getManager')->willReturn($entityManager);

        // Create an instance of the controller and call the method being tested
        $controller = new ProductController();
        $response = $controller->show($doctrine, 1);

        // Assert that the response is a JsonResponse with a 200 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        // Assert that the response data matches the expected data
        $expectedData = [
            'message' => 'Product with ID 1',
            'data' => [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 10.99
            ],
        ];
        $this->assertEquals($expectedData, json_decode($response->getContent(), true));
    }

    public function testShowProduct2(): void
    {
        // Create a mock Product object
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getPrice')->willReturn(10.99);

        // Create a mock repository and configure it to return the mock Product object
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn($product);

        // Create a mock EntityManager and configure it to return the mock repository
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        // Create a mock ManagerRegistry and configure it to return the mock EntityManager
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($entityManager);

        // Create an instance of the controller and call the method being tested
        $controller = new ProductController();
        $response = $controller->show($doctrine, 1);

        // Assert that the response is a JsonResponse with a 200 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        // Assert that the response data matches the expected data
        $expectedData = [
            'message' => 'Product with ID 1',
            'data' => [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 10.99
            ],
        ];
        $this->assertEquals($expectedData, json_decode($response->getContent(), true));
    }

    public function testShowProductNotFound(): void
    {
        // Create a mock repository that returns null
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        // Create a mock EntityManager
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        // Create a mock ManagerRegistry and configure it to return the mock EntityManager
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($entityManager);

        // Create an instance of the controller and call the method being tested
        $controller = new ProductController();
        $response = $controller->show($doctrine, 1);

        // Assert that the response is a JsonResponse with a 404 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert that the response data matches the expected data
        $expectedData = [
            'error' => 'Product with ID 1 not found',
        ];

        $this->assertEquals($expectedData, json_decode($response->getContent(), true));
    }

    public function testDeleteProductNotFound(): void
    {
        // Create a mock repository that returns null
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        // Create a mock EntityManager
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        // Create a mock ManagerRegistry and configure it to return the mock EntityManager
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($entityManager);

        // Create an instance of the controller and call the method being tested
        $controller = new ProductController();
        $response = $controller->delete($doctrine, 1);

        // Assert that the response is a JsonResponse with a 404 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());

        // Assert that the response data matches the expected data
        $expectedData = [
            'error' => 'Product with ID 1 not found',
        ];

        $this->assertEquals($expectedData, json_decode($response->getContent(), true));
    }
}
