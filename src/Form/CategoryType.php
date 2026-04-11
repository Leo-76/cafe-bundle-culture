<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Form;

use CafeCulture\BlogBundle\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr'  => ['placeholder' => 'Ex : Torréfaction', 'class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 3, 'class' => 'form-control'],
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur (hex)',
                'attr'  => ['placeholder' => '#6B3F2A', 'type' => 'color', 'class' => 'form-control form-control--color'],
                'data'  => '#6B3F2A',
            ])
            ->add('icon', TextType::class, [
                'label'    => 'Classe d\'icône FontAwesome',
                'required' => false,
                'attr'     => ['placeholder' => 'fa-coffee', 'class' => 'form-control'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position dans le menu',
                'attr'  => ['min' => 0, 'class' => 'form-control'],
                'data'  => 0,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => ['class' => 'btn btn-coffee'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Category::class]);
    }
}
