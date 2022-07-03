<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $entityManager;

    public function __construct(
        OrderRepository $orderRepository, 
        ManagerRegistry $doctrine)
    {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $doctrine->getManager();
    }
    
    #[Route('/order', name: 'order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    #[Route('/user/orders', name: 'user_order_list')]
    public function userOrders(): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('order/user.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/store/order/{product}', name: 'order_store')]
    public function store(Product $product): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $order = new Order();
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('processing...');
        $order->setUser($this->getUser());

        $this->entityManager->persist($order);
        $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Your order was saved'
            );

        return $this->redirectToRoute('user_order_list');

    }
}
