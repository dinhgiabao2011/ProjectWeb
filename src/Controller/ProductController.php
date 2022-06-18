<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use App\Form\ProductFormType;
use App\Entity\Category;

class ProductController extends AbstractController
{
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        $this->em = $registry;
    }
    
    #[Route('/product', name: 'product_list')]
    public function listProduct()
    {
        $products = $this->em
            ->getRepository('App\Entity\Product')
            ->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/details/{id}', name: 'product_details')]
    public function detailsProduct($id)
    {
        $products = $this->em
            ->getRepository('App\Entity\Product')
            ->find($id);

        return $this->render('product/details.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/product/create", name="product_create", methods={"GET","POST"})
     */
    public function createProduct(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        
        if ($this->saveChanges($form, $request, $product)) {
            $this->addFlash(
                'notice',
                'Product Added'
            );
            
            return $this->redirectToRoute('product_list');
        }
        
        return $this->render('product/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function saveChanges($form, $request, $product)
    {
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $product_name = $product->getName();
            $product_unitPrice = $product->getUnitprice();
            $product_quantity = $product->getQuantity();
            $product_categoryId = (int)$product->getCategoryId();
            $product_manufacturerId = $product->getManufacturerId();
            $product_image = $product->getImage();
            $em = $this->em->getManager();
            $em->persist($product);
            $em->flush();
            
            return true;
        }
        return false;
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function deleteAction($id)
    {
        $em = $this->em->getManager();
        $product = $em->getRepository('App\Entity\Product')->find($id);
        $em->remove($product);
        $em->flush();
        
        $this->addFlash(
            'error',
            'Product deleted'
        );
        
        return $this->redirectToRoute('product_list');
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function editAction($id, Request $request)
    {
        $em = $this->em->getManager();
        $product = $em->getRepository('App\Entity\Product')->find($id);
        
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->getManager()->flush();
            return $this->redirectToRoute('product_list');
        }
        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
