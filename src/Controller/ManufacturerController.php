<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Manufacturer;
use App\Controller\ManufacturerController;
use App\Form\ManufacturerFormType;
use Doctrine\Persistence\ManagerRegistry;

class ManufacturerController extends AbstractController
{
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        $this->em = $registry;
    }

    /**
     * @Route("/manufacturer/create", name="manufacturer_create", methods={"GET","POST"})
     */
    public function createManufacturer(Request $request)
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerFormType::class, $manufacturer);
        
        if ($this->saveChanges($form, $request, $manufacturer)) {
            $this->addFlash('notice','Manufacturer Added');
            
            return $this->redirectToRoute('manufacturer_list');
        }
        
        return $this->render('manufacturer/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function saveChanges($form, $request, $manufacturer)
    {
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $manufacturer = $form->getData();
            $manufacturer_name = $manufacturer->getName();
            $manufacturer_address = $manufacturer->getAddress();
            $manufacturer_telephone = $manufacturer->getTelephone();
            $manufacturer_email = $manufacturer->getEmail();
            $manufacturer_description = $manufacturer->getDescription();
            $em = $this->em->getManager();
            $em->persist($manufacturer);
            $em->flush();
            
            return true;
        }
        return false;
    }

    /**
     * @Route("/manufacturer", name="manufacturer_list")
     */
    public function listManufacturer()
    {
        $manufacturers = $this->em->getRepository('App\Entity\Manufacturer')->findAll();
        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturers
        ]);
    }

    /**
     * @Route("/manufacturer/details/{id}", name="manufacturer_details")
     */
    public
    function detailsManufacturer($id)
    {
        $manufacturers = $this->em
            ->getRepository('App\Entity\Manufacturer')
            ->find($id);
        return $this->render('manufacturer/details.html.twig', [
            'manufacturers' => $manufacturers
        ]);
    }

    /**
     * @Route("/manufacturer/edit/{id}", name="manufacturer_edit")
     */
    public function editManufacturer(Request $request, int $id)
    {
        $em = $this->em->getManager();
        $manufacturer = $em->getRepository('App\Entity\Manufacturer')->find($id);
        $form = $this->createForm(ManufacturerFormType::class, $manufacturer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->getManager()->flush();
            return $this->redirectToRoute('manufacturer_list');
        }
        return $this->render('manufacturer/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/manufacturer/delete/{id}", name="manufacturer_delete")
     */
    public function deleteManufacturer($id)
    {
        $em = $this->em->getManager();
        $manufacturer = $em->getRepository('App\Entity\Manufacturer')->find($id);
        $em->remove($manufacturer);
        $em->flush();
        
        $this->addFlash(
            'error',
            'Manufacturer deleted'
        );
        return $this->redirectToRoute('manufacturer_list');
    }

}
