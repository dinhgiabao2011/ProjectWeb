<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
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

    private $slugger;

    public function __construct(ManagerRegistry $registry, SluggerInterface $slugger)
    {
        $this->em = $registry;
        $this->slugger = $slugger;
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
            // $product_image = $product->getImage();

            $product_image = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($product_image) {
                $originalFilename = pathinfo($product_image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$product_image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $product_image->move(
                        $this->getParameter('product_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }
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
    
        if ($this->saveChanges($form, $request, $product)) {
            $this->addFlash(
                'notice',
                'Product Edited'
            );
            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
