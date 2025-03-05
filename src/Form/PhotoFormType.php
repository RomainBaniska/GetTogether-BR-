<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class PhotoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Ajouter les autres champs du formulaire si nécessaire
            // ...
            ->add('photo', FileType::class, [
                'label' => '',
                'attr' => [
                    'class' => 'custom-file-input'
                ],
                'mapped' => false,
                // 'required' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Taille maximale du fichier (5 Mo dans cet exemple)
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image JPEG ou PNG valide.',
                    ]),
                    new Image([
                        'maxSize' => '5M', // Taille maximale de l'image (5 Mo dans cet exemple)
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => '',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configurez les options du formulaire si nécessaire
        ]);
    }
}