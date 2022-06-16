<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Controller\CategoryController;
use App\Form\CategoryFormType;
use Doctrine\Persistence\ManagerRegistry;

class CategoryController extends AbstractController
{
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        $this->em = $registry;
    }

    /**
     * @Route("/category/create", name="category_create", methods={"GET","POST"})
     */
    public function createCategory(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);
        
        if ($this->saveChanges($form, $request, $category)) {
            $this->addFlash('notice','Category Added');
            
            return $this->redirectToRoute('category_list');
        }
        
        return $this->render('category/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function saveChanges($form, $request, $category)
    {
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $category_name = $category->getName();
            $em = $this->em->getManager();
            $em->persist($category);
            $em->flush();
            
            return true;
        }
        return false;
    }

    /**
     * @Route("/category", name="category_list")
     */
    public function listCategory()
    {
        $categorys = $this->em->getRepository('App\Entity\Category')->findAll();
        return $this->render('category/index.html.twig', [
            'categorys' => $categorys
        ]);
    }

    /**
     * @Route("/category/details/{id}", name="category_details")
     */
    public function detailsCategory($id)
    {
        $categorys = $this->em
            ->getRepository('App\Entity\Category')
            ->find($id);
        return $this->render('category/details.html.twig', [
            'categorys' => $categorys
        ]);
    }

    /**
     * @Route("/category/edit/{id}", name="category_edit")
     */
    public function editCategory(Request $request, int $id)
    {
        $em = $this->em->getManager();
        $category = $em->getRepository('App\Entity\Category')->find($id);
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->getManager()->flush();
            return $this->redirectToRoute('category_list');
        }
        return $this->render('category/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/category/delete/{id}", name="category_delete")
     */
    public function deleteCategory($id)
    {
        $em = $this->em->getManager();
        $category = $em->getRepository('App\Entity\Category')->find($id);
        $em->remove($category);
        $em->flush();
        
        $this->addFlash(
            'error',
            'Category deleted'
        );
        return $this->redirectToRoute('category_list');
    }
}
