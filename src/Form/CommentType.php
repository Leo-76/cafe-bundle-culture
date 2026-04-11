<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Form;

use CafeCulture\BlogBundle\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authorName', TextType::class, [
                'label'       => 'Votre nom',
                'attr'        => ['placeholder' => 'Jean Dupont', 'class' => 'form-control'],
            ])
            ->add('authorEmail', EmailType::class, [
                'label'       => 'Votre adresse e-mail',
                'attr'        => ['placeholder' => 'jean@example.com', 'class' => 'form-control'],
            ])
            ->add('content', TextareaType::class, [
                'label'       => 'Votre commentaire',
                'attr'        => ['placeholder' => 'Partagez vos impressions sur cet article…', 'rows' => 5, 'class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier mon commentaire',
                'attr'  => ['class' => 'btn btn-coffee'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
