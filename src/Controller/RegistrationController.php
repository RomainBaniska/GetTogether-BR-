<?php

namespace App\Controller;

use App\Document\Users;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $message = '';
        // Créer une nouvelle instance de l'entité Users
        $user = new Users();

        // Créer le formulaire d'inscription en utilisant RegistrationFormType et l'entité Users
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et s'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();

            
        // Vérifier si l'adresse e-mail existe déjà dans la base de données
        $existingUser = $userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            // Si un utilisateur avec l'e-mail existe déjà, afficher une modal d'erreur
            $response = $this->render('registration/error.html.twig', [
                'messageError' => 'Un compte existe déjà avec cette adresse e-mail.',
            ]);
            
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $response;
        }

            // Si l'Email n'existe pas déjà :

            // Hashe le MDP
            $hashedPassword = $userPasswordHasher->hashPassword($user, $user->getPassword());
            // Défini le mot de passe hashé dans l'objet Users
            $user->setPassword($hashedPassword);

            // Enregistre l'utilisateur dans la base de données en utilisant la fonction save dans le UserRepository
            $userRepository->save($user);

            // Affiche la modal de succès
            return $this->render('registration/success.html.twig', [
                'messageSuccess' => 'Votre compte a été créé.',
            ]);
        }

        // Affiche le formulaire d'inscription à la vue Twig
        return $this->render('registration/index.html.twig', [
            'message' => $message,
            'registrationForm' => $form->createView(),
        ]);
    }

}
