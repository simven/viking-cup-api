<?php

namespace App\Form\Type;

use App\Entity\Link;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('icon', ChoiceType::class, [
                'label' => 'Icône',
                'choices' => [
                    'Site' => 'fa-solid fa-link',
                    'Facebook' => 'fa-brands fa-facebook-f',
                    'Instagram' => 'fa-brands fa-instagram',
                    'Youtube' => 'fa-brands fa-youtube',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'attr' => ['placeholder' => 'https://'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Link::class,
        ]);
    }
}
