<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Form;

use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Contenu principal ──────────────────────────────────────────
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr'  => ['placeholder' => 'Un guide complet sur l\'espresso…', 'class' => 'form-control'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu (Markdown supporté)',
                'attr'  => ['rows' => 20, 'class' => 'form-control form-control--editor', 'placeholder' => 'Rédigez votre article ici…'],
            ])
            ->add('excerpt', TextareaType::class, [
                'label'    => 'Extrait (résumé pour les listes)',
                'required' => false,
                'attr'     => ['rows' => 3, 'class' => 'form-control', 'placeholder' => 'Courte description affichée dans les listes d\'articles…'],
            ])

            // ── Classification ────────────────────────────────────────────
            ->add('category', EntityType::class, [
                'label'        => 'Catégorie',
                'class'        => Category::class,
                'choice_label' => 'name',
                'attr'         => ['class' => 'form-control'],
            ])
            ->add('tags', EntityType::class, [
                'label'        => 'Tags',
                'class'        => Tag::class,
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => false,
                'required'     => false,
                'attr'         => ['class' => 'form-control form-control--tags', 'multiple' => 'multiple'],
            ])

            // ── Spécifiques café ──────────────────────────────────────────
            ->add('coffeeOrigin', ChoiceType::class, [
                'label'       => 'Origine géographique du café',
                'required'    => false,
                'placeholder' => '— Sélectionner une origine —',
                'attr'        => ['class' => 'form-control'],
                'choices'     => [
                    'Afrique'        => [
                        'Éthiopie' => 'Éthiopie', 'Kenya'   => 'Kenya',
                        'Tanzanie' => 'Tanzanie', 'Rwanda'  => 'Rwanda',
                        'Burundi'  => 'Burundi',  'Ouganda' => 'Ouganda',
                    ],
                    'Amérique Latine' => [
                        'Colombie'   => 'Colombie',   'Brésil'    => 'Brésil',
                        'Guatemala'  => 'Guatemala',  'Costa Rica' => 'Costa Rica',
                        'Pérou'      => 'Pérou',      'Mexique'    => 'Mexique',
                        'Équateur'   => 'Équateur',   'Bolivie'    => 'Bolivie',
                        'Honduras'   => 'Honduras',   'Nicaragua'  => 'Nicaragua',
                        'Jamaïque'   => 'Jamaïque',
                    ],
                    'Asie & Océanie' => [
                        'Indonésie' => 'Indonésie', 'Vietnam' => 'Vietnam',
                        'Inde'      => 'Inde',      'PNG'     => 'PNG',
                        'Hawaï'     => 'Hawaï',
                    ],
                    'Moyen-Orient' => [
                        'Yémen' => 'Yémen',
                    ],
                ],
            ])
            ->add('aromaProfileText', TextType::class, [
                'label'    => 'Profil aromatique (séparés par des virgules)',
                'required' => false,
                'mapped'   => false,
                'attr'     => [
                    'placeholder' => 'fruité, floral, chocolaté, caramel…',
                    'class'       => 'form-control',
                ],
                'help' => 'Valeurs possibles : fruité, floral, chocolaté, noisette, caramel, épicé, terreux, fumé, vanillé, agrumes',
            ])
            ->add('readingTimeMinutes', IntegerType::class, [
                'label' => 'Temps de lecture (minutes)',
                'attr'  => ['min' => 1, 'max' => 60, 'class' => 'form-control'],
                'data'  => 5,
            ])

            // ── Image de couverture ───────────────────────────────────────
            ->add('coverImageFile', FileType::class, [
                'label'    => 'Image de couverture',
                'required' => false,
                'mapped'   => false,
                'attr'     => ['class' => 'form-control', 'accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new File([
                        'maxSize'   => '3M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format accepté : JPEG, PNG ou WebP.',
                    ]),
                ],
            ])

            // ── Publication ───────────────────────────────────────────────
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'attr'    => ['class' => 'form-control'],
                'choices' => [
                    'Brouillon'  => Article::STATUS_DRAFT,
                    'Publié'     => Article::STATUS_PUBLISHED,
                    'Archivé'    => Article::STATUS_ARCHIVED,
                ],
            ])
            ->add('featured', CheckboxType::class, [
                'label'    => 'Mettre en avant (hero)',
                'required' => false,
                'attr'     => ['class' => 'form-check-input'],
            ])
            ->add('authorName', TextType::class, [
                'label'    => 'Nom de l\'auteur',
                'required' => false,
                'attr'     => ['placeholder' => 'Jean Barista', 'class' => 'form-control'],
            ])

            // ── SEO ───────────────────────────────────────────────────────
            ->add('metaTitle', TextType::class, [
                'label'    => 'Titre SEO (balise <title>)',
                'required' => false,
                'attr'     => ['placeholder' => 'Titre optimisé pour Google (60 car. max)', 'class' => 'form-control', 'maxlength' => 70],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label'    => 'Description SEO (meta description)',
                'required' => false,
                'attr'     => ['rows' => 2, 'placeholder' => 'Description pour les moteurs de recherche (160 car. max)', 'class' => 'form-control', 'maxlength' => 170],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer l\'article',
                'attr'  => ['class' => 'btn btn-coffee btn--lg'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
