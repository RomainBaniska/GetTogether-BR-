<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(SessionInterface $sessionInterface, UserRepository $userRepository, EventRepository $eventRepository): Response
    {

        // Récupère l'email de l'utilisateur connecté depuis la session
        $email = $sessionInterface->get('email');

        // Récupère l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepository->findOneBy(['email' => $email]);
        // Récupérer les tags de l'utilisateur
        $tagsByCategory = $user->getTagsByCategory();

        // Récupérer tous les événements
        $events = $eventRepository->findAll();

        // Reformater les données pour organiser les tags par catégorie
        $tagsGroupedByCategory = [];
        foreach ($tagsByCategory as $tag) {
            $tagsGroupedByCategory[] = $tag; // Utilise $tag à la fois comme clé et valeur
        }
        
           // Initialisez le tableau pour stocker tous les événements
           $calendarEvents = [];
       
           // Boucle sur tous les événements pour récupérer les données et les afficher
           foreach ($events as $event) {
               $calendarEvent = [
                   'title' => $event->getTitle(),
                   'start' => $event->getDateFormat()->format('Y-m-d'),
                   'end' => $event->getDateFormat()->format('Y-m-d'),
               ];
               $calendarEvents[] = $calendarEvent;
           }
   
           $datas = json_encode($calendarEvents);
        //    dump($calendarEvents);

        return $this->render('dashboard/index.html.twig', [
            'tagsByCategory' => $tagsGroupedByCategory,
            'user' => $user,
            'events' => $events,
            'datas' => $datas,
        ]);
    }

    #[Route('/mestags', name: 'app_dashboard_mestags')]
    public function mestags(SessionInterface $session, UserRepository $userRepo): Response
    {


        // Récupérer l'email de l'utilisateur connecté depuis la session
        $email = $session->get('email');

        // Récupérer l'utilisateur depuis la base de données en utilisant l'email
        $user = $userRepo->findOneBy(['email' => $email]);

        $tags = [];

        // Passez les données à votre modèle Twig et générez la vue
        return $this->render('dashboard/mestags.html.twig', [
            'TagsData' => $tags,
            'user' => $user,
        ]);
    }

    #[Route('/mestags/save/{jsontags}', name: 'app_dashboard_mestags_save')]
    /**
     * Route de sauvegarde de la lsite des tags du user
     *
     * @param [type] $jsontags
     * @param UserRepository $userRepo
     * @return JsonResponse
     */
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

    #[Route('/calendar', name: 'app_calendar')]
    public function calendar(EventRepository $eventRepository): Response
    {
        // Recherche des événements dans la base de données
        $events = $eventRepository->findAll();
        
        // Initialisez le tableau pour stocker tous les événements
        $calendarEvents = [];
    
        // Boucle sur tous les événements pour récupérer les données et les afficher
        foreach ($events as $event) {
            $calendarEvent = [
                'title' => $event->getTitle(),
                'start' => $event->getDateFormat()->format('Y-m-d'),
                'end' => $event->getDateFormat()->format('Y-m-d'),
            ];
            $calendarEvents[] = $calendarEvent;
        }
    
        dump($calendarEvents);
        dump($calendarEvent);

        $datas = json_encode($calendarEvents);
        dump($datas);
        
        return $this->render('dashboard/calendar.html.twig', [
            'datas' => $datas,
        ]);
    }

}
