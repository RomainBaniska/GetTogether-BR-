<?php

namespace App\Controller;

use App\Document\Users;
use App\Form\LoginFormType;
use App\Form\PhotoFormType;
use App\Form\ProfilFormType;
use App\Form\TagsFormType;
use App\Repository\UserRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, ManagerRegistry $managerRegistry, AuthenticationUtils $authenticationUtils, UserRepository $userRepository, SessionInterface $sessionInterface): Response
    {
        // Récupère les erreurs d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère le dernier nom d'utilisateur utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        // Crée une nouvelle instance de l'entité Users
        $user = new Users();
        // Pré-remplit le champ d'e-mail avec le dernier nom d'utilisateur utilisé
        $user->setEmail($lastUsername);
        // Crée le formulaire de connexion en utilisant LoginFormType et l'entité Users
        $form = $this->createForm(LoginFormType::class, $user);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'utilisateur correspondant à l'adresse e-mail fournie depuis la base de données
            $userRepository = $managerRegistry->getRepository(Users::class);
            $authenticatedUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if (!$authenticatedUser) {
                // Si aucun utilisateur n'est trouvé on affiche un message d'erreur
                throw new CustomUserMessageAuthenticationException('Adresse e-mail incorrecte.');
            }

            // Compare si le mot de passe fourni correspond au mot de passe de l'utilisateur
            if (password_verify($user->getPassword(), $authenticatedUser->password)) {
                // Stocke l'e-mail de l'utilisateur connecté dans la session
                $sessionInterface->set('email', $user->getEmail());

                // Vérifie si l'utilisateur a déjà rempli les informations de profil
                if ($authenticatedUser->hasFilledProfile()) {
                    // Redirige vers le dashboard
                    return new RedirectResponse($this->generateUrl('app_dashboard'));
                }
            }

            // Redirige vers la page profil
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('login/index.html.twig', [
            'loginForm' => $form->createView(),
            'error' => $error,

        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $sessionInterface): Response
    {
        // Supprime les données de la session
        $sessionInterface->clear();

        // Redirige vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(Request $request, UserRepository $userRepository, SessionInterface $sessionInterface): Response
    {
        // Récupère l'email de l'utilisateur connecté depuis la session
        $email = $sessionInterface->get('email');
        if(!$email)
            return $this->redirectToRoute('app_login');

        // Récupère l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepository->findOneBy(['email' => $email]);

        // Créer le formulaire de profil en utilisant ProfilFormType et l'utilisateur récupéré
        $form = $this->createForm(ProfilFormType::class, $user);
        
        // Gère la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistre les modifications de l'utilisateur dans la base de données
            $user->fill();
            $userRepository->save($user);

            // Redirige l'utilisateur vers une autre page 
            return $this->redirectToRoute('app_login_avatar');
        }

        return $this->render('login/profil.html.twig', [
            'profilForm' => $form->createView(),
        ]);
    }

    #[Route('/avatar', name: 'app_login_avatar')]
    public function avatar(Request $request, UserRepository $userRepository, SessionInterface $sessionInterface): Response
    {
        // Récupérer l'email de l'utilisateur connecté depuis la session
        $email = $sessionInterface->get('email');

        // Récupérer l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé / Non connecté.');
            return $this->redirectToRoute('app_login_avatar');
        }        

        // Créer le formulaire
        $form = $this->createForm(PhotoFormType::class);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier téléchargé
            $photoFile = $form->get('photo')->getData();
            // Récupérer l'avatar sélectionné (si sélectionné)
            $selectedAvatar = $request->request->get('selected_avatar');

            // Vérifier si un fichier a été téléchargé
            if ($photoFile) {
                // Déplacer le fichier vers le répertoire d'upload
                $uploadDir = $this->getParameter('photos_upload_directory');
                $fileName = md5(uniqid()) . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($uploadDir, $fileName);
                } catch (\Exception $e) {
                    // Gérer les erreurs éventuelles liées au téléchargement
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo.');
                    return $this->redirectToRoute('app_login_avatar');
                }
                 // Enregistrer le nom du fichier de la photo de profil dans l'utilisateur
                //  $user->setProfilPicture($fileName);
                 $user->setProfilPicture($fileName);

            } elseif ($selectedAvatar) {
                // Si un avatar prédéfini a été choisi, l'utiliser comme photo de profil
                // $user->setProfilPicture('/assets/img/avatar/' . $selectedAvatar);
                $user->setProfilPicture($selectedAvatar);
            } else {
                $this->addFlash('error', 'Veuillez choisir une image ou un avatar.');
                return $this->redirectToRoute('app_login_avatar');
            }
               
                $user->fill();
                $userRepository->save($user);

                // Rediriger ou afficher un message de succès
                $this->addFlash('success', 'La photo de profil a été téléchargée avec succès !');
                return $this->redirectToRoute('app_login_tags');
            // }
        }

        return $this->render('login/avatar.html.twig', [
            'photoForm' => $form->createView(),
        ]);
    }

    // Redirection vers le choix des tags
    #[Route('/tags', name: 'app_login_tags')]
    public function tags(Request $request, UserRepository $userRepository, SessionInterface $sessionInterface, TagsFormType $tagsFormType): Response
    {
        // Récupère l'email de l'utilisateur connecté depuis la session
        $email = $sessionInterface->get('email');

        // Récupère l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepository->findOneBy(['email' => $email]);

        return $this->render('login/tags.html.twig', [
            // 'tagsForm' => $form->createView(),
            'tagsForm' => '$form->createView()',
        ]);
    }

    #[Route('/tags/save/{jsontags}', name: 'app_login_tags_save')]
    public function saveTags($jsontags, UserRepository $userRepository, SessionInterface $sessionInterface): JsonResponse
    {
        // Récupérer l'email de l'utilisateur connecté depuis la session
        $email = $sessionInterface->get('email');
    
        // Récupérer l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepository->findOneBy(['email' => $email]);
    
        // récupère la liste complète des tags de l'utilisateur
        $tags = json_decode($jsontags);
    
        // Mettre à jour la propriété tagsByCategory de l'utilisateur avec les tags sélectionnés
        $user->setTagsByCategory($tags);
        $user->fill();
    
        // faire ici l'ajout à la bdd
        $userRepository->save($user);
    
        // renvoie la réponse
        return new JsonResponse(['ok']);
    }

}
