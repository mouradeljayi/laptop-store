<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProductController extends AbstractController
{
    private $productRepository;
    private $entityManager;

    public function __construct(ProductRepository $productRepository, ManagerRegistry $doctrine)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/products', name: 'product_list')]
     /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     * 
     */
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/store/product', name: 'product_store')]
     /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     * 
     */
    public function store(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product) ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image  = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Your product was saved'
            );
            return $this->redirectToRoute('product_list');
        }
        return $this->renderForm('product/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/show/product/{id}', name: 'product_show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/edit/product/{id}', name: 'product_edit')]
     /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     * 
     */
    public function edit(Product $product, Request $request): Response
    {
        $form = $this->createForm(ProductType::class, $product) ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $filesystem = new Filesystem();
                $imagePath = './uploads/'.$product->getImage();
                if($filesystem->exists($imagePath)) {
                    $filesystem->remove($imagePath);
                }
                $image  = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'), $image_name);
                $product->setImage($image_name);
            }
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Your product was updated'
            );
            return $this->redirectToRoute('product_list');
        }

        return $this->renderForm('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/product/{id}', name: 'product_delete')]
     /**
     * @IsGranted("ROLE_ADMIN", statusCode=404, message="Page not found")
     * 
     */
    public function delete(Product $product): Response
    {
        $filesystem = new Filesystem();
        $imagePath = './uploads/'.$product->getImage();
        if($filesystem->exists($imagePath)) {
            $filesystem->remove($imagePath);
        }
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->addFlash(
            'success',
            'Your product was deleted'
        );
        return $this->redirectToRoute('product_list');
    }
}
