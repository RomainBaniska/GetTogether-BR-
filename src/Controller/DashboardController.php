<?php

namespace App\Controller;

use App\Service\NewApiService;
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
    public function index(SessionInterface $sessionInterface, NewApiService $api, UserRepository $userRepository): Response
    {
        $email = $sessionInterface->get('email');

        if (!$email) {
            return $this->redirectToRoute("app_login");
        } 
        else 
        {

        $user = $userRepository->findOneBy(['email' => $email]);

        // Récupérer les tags de l'utilisateur
        $tagsByCategory = $user->getTagsByCategory() ?? [];

        // Reformater les données pour organiser les tags par catégorie
        $tagsGroupedByCategory = [];

        if ($tagsByCategory) { 
        foreach ($tagsByCategory as $tag) {
            $tagsGroupedByCategory[] = $tag; // Utilise $tag à la fois comme clé et valeur
            }
        }
        
        // On charge les données de l'API 
        $RawApiDatas = $api->getDatas();

        // Si on veut mélanger ces résulats :
        shuffle($RawApiDatas);
        // On charge les 10 premiers résultats
        // $apiDatas = array_slice($api->getDatas(), 0, 10);
        $apiDatas = array_slice($RawApiDatas, 0, 10);
        // dump($apiDatas);
       
        // Récupérer tous les événements
        // $events = $eventRepository->findAll();

        // On initialise le FullCalendar
        $calendarEvents = [];
       
        // Boucle sur tous les événements pour récupérer les données et les afficher sur le fullcalendar
        foreach ($apiDatas as $apiData) {
            $calendarEvent = [
                'title' => $apiData['title'],
                'start' => (new \DateTime($apiData['start']))->format('Y-m-d'),
                'end' => (new \DateTime($apiData['end']))->format('Y-m-d'),
            ];
            $calendarEvents[] = $calendarEvent;
        }
   
           $calendarDatas = json_encode($calendarEvents);

        return $this->render('dashboard/index.html.twig', [
            'tagsByCategory' => $tagsGroupedByCategory,
            'pseudo' => $user,
            'user' => $user,
            'events' => $apiDatas,
            'datas' => $calendarDatas,
        ]);
    }
    }


    #[Route('/mestags', name: 'app_dashboard_mestags')]
    public function mestags(SessionInterface $sessionInterface, UserRepository $userRepository): Response
    {

         // Connexion
         $email = $sessionInterface->get('email');
         $user = $userRepository->findOneBy(['email' => $email]);

         // Récupérer les tags de l'utilisateur
         $tagsByCategory = $user->getTagsByCategory();

         // Reformater les données pour organiser les tags par catégorie
         $tagsGroupedByCategory = [];
 
         if ($tagsByCategory) { 
         foreach ($tagsByCategory as $tag) {
             $tagsGroupedByCategory[] = $tag; // Utilise $tag à la fois comme clé et valeur
             }
         }

        // Passez les données à votre modèle Twig et générez la vue
        return $this->render('dashboard/mestags.html.twig', [
            'TagsData' => $tagsByCategory,
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

    // $jsontags est récupérée via l'injection dans l'url lors de la soumission du formulaire
    public function saveTags($jsontags, UserRepository $userRepository, SessionInterface $sessionInterface): JsonResponse
    {

        // Connexion
        $email = $sessionInterface->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        // récupère la liste complète des tags de l'utilisateur
        $tags = json_decode($jsontags);

        // Mettre à jour la propriété tagsByCategory de l'utilisateur avec les tags sélectionnés
        $user->setTagsByCategory($tags);
        // $user->fill();

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
