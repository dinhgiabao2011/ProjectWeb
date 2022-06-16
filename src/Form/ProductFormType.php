<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategoryRepository;
use App\Entity\Category;

class ProductFormType extends AbstractType
{
    private $em;
    private $categoryRepository;

    public function __construct(ManagerRegistry $registry, CategoryRepository $categoryRepository)
    {
        $this->em = $registry;
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $e = $this->em->getManager();
      
        // Call CustomerRepo
        $cateRepo = $e->getRepository(Category::class);
        
        // Call function
        $result = $cateRepo->getCategory();
        $builder
            ->add('name', TextType::class)
            ->add('unitPrice', NumberType::class)
            ->add('quantity', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('category_id', EntityType::class, [
                'class' => Category::class
            ])
            ->add('manufacturer_id', ChoiceType::class, [
                'choices' => [
                    'Vinamilk' => "1",
                    'Masan' => "2",
                    'Sunhouse' => "3",
                ],
            ])
            ->add('image', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
