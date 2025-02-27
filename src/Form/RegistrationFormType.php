<?php

namespace App\Form;

use App\Document\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le champ 'email' de type EmailType
        $builder
        ->add('destination', HiddenType::class, [
            'data' => '/registration',
            'mapped' => false
        ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail (*)',
                // 'placeholder' => 'Entrez votre email',
                'attr' => [
                    'class' => 'inputsub label-margin',
                    'placeholder' => 'Patrick@hotmail.com'
                ]

            ])

            // Ajoute le champ 'password' de type RepeatedType pour la saisie du mot de passe et sa confirmation
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe',
                    // 'attr' => ['placeholder' => 'Mot de passe']
                ],
                'second_options' => [
                    'label' => 'Confirmer votre mot de passe',
                    // 'attr' => ['placeholder' => 'Confirmez votre mot de passe']
                ],
            ])

            // Ajoute le champ 'CGU' de type CheckboxType pour accepter les CGU
            ->add('CGU', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    // Ajoute une contrainte IsTrue pour vérifier si la case est cochée
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation pour créer un compte.'
                    ]),
                ],
                'label' => 'J\'accepte les CGU',
            ])

            // Ajoute le bouton de soumission du formulaire
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
                'attr' => [
                    'class' => 'inputsub btninscrire colorWhite registration'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définit la classe des données utilisées par le formulaire
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
