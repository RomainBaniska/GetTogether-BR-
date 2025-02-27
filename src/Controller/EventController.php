<?php

namespace App\Controller;

use App\Document\Events;
use App\Document\ChatMessage;
use App\Service\NewApiService;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\ChatMessageRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/event')]
class EventController extends AbstractController
{
    // Affichage de tous les événements en BDD (pas spécielement utile)
    #[Route('/affichage', name: 'app_event_affichage')]
    
    public function AfficheEvent(EventRepository $eventRepository, CacheInterface $cache, DocumentManager $dm): Response
    {

        $dataTabForJs = $cache->get('events_cache', function ($item) use ($eventRepository) {
            
                $events = $eventRepository->findAll();
                $dataTabForJs = [];

                // Itération sur chaque événement pour construire le tableau final
                foreach ($events as $event) {
                $dataForJs = [
                    'title' => $event->getTitle(),
                    'description' => $event->getDescription(),
                    'adresse' => $event->getAddress(),
                    'image' => $event->getImageUrl(),
                    'unique_id' => $event->getEventId(),
                    'dateFormat' => $event->getDateFormat(),
                    'category' => $event->getCategory(),
                                ];

                    // On ajoute toutes les données des events (title, image...) dans un tableau final de tous les événements
                    $dataTabForJs[] = $dataForJs;
                                        }

            return $dataTabForJs;
    });

        // Dump du contenu du tableau final
        // dump($dataTabForJs);
        
        return $this->render('event/affichage.html.twig', [
            'jsonData' => $dataTabForJs,
        ]);
    }

    // affichage events via API en direct
    #[Route('/affichage2', name: 'app_event_affichage2')]
    public function AfficheEvent2(NewApiService $newApi, CacheInterface $cache): Response
    {

    $data = $newApi->getDatas();

    dump($data);
      
    return $this->render('event/affichage2.html.twig', [
        'data' => $data,
    ]);
    }


    // Envoie des évenements en BDD
    #[Route('/to_bdd', name: 'app_event_bdd')]
    public function EventToBDD(EventRepository $eventRepository): Response
    {

        // Récupération du lien de l'api (json)
        $jsonData = './assets/json/event.json';

        // Decodage en string pour la lecture en php
        $eventsData = json_decode(file_get_contents($jsonData, true));

        // Boucle sur $eventData pour permettre l'insertion des données de l'événements dans la BDD
        foreach ($eventsData as $category => $events) {
            foreach ($events as $event) {
                foreach ($event as $e) {

                    // Variable qui va permettre la vérification de non doublons dans la BDD
                    $existingEvent = $eventRepository->findOneBy(['title' => $e->Titre]);

                    // Condition pour eviter des doublons d'événements dans la base de données 
                    if (!$existingEvent) {
                        $eventDoc = new Events();
                        $eventDoc->setCategory($category);
                        $eventDoc->setTitle($e->Titre);
                        $eventDoc->setDescription($e->Description);
                        $eventDoc->setEventDate($e->Date_de_l_evenement);
                        $eventDoc->setAddress($e->Adresse);
                        $eventDoc->setImageUrl($e->URL_d_image);
                        $eventDoc->seteventId($e->eventId);
                        $eventDoc->setDateFormat($e->date_format);
                        $eventDoc->setLong($e->Longitude);
                        $eventDoc->setLat($e->Latitude);
                        $eventDoc->setOrga($e->Organisateur);

                        // Enregistre dans la base de données
                        $eventRepository->save($eventDoc);
                    }
                }
            }
        }

        // Réponse si bon injection des données
        return new Response('Events inserted successfully!');
    }

    #[Route('/{eventUid}', name: 'app_event_show')]
    public function show(ChatMessageRepository $chatMessageRepository, string $eventUid, SessionInterface $sessionInterface, NewApiService $newApi): Response
    {

        $event = $newApi->getDataById($eventUid);
        if (!$event) {
        return $this->render('event/error.html.twig', [], new Response('', 404));
    }

        // Récupère les messages de chat associés à l'événement
        $chatMessages = $chatMessageRepository->findBy(['eventId' => $eventUid]);

        // Récupère l'email de l'utilisateur connecté depuis la session
        $emailSession = $sessionInterface->get('email');

        // Affiche la page d'affichage de l'événement avec les messages de chat
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'chatMessages' => $chatMessages,
            'email' => $emailSession,
            'parentId' => null // Pour le premier niveau de messages, parentId est nul
            
        ]);
    }

    #[Route('/{eventUid}/post_chat_message', name: 'app_event_post_chat_message', methods: ['GET', 'POST'])]
    public function postChatMessage(Request $request, UserRepository $userRepository, ChatMessageRepository $chatMessageRepository, string $eventUid, SessionInterface $session, NewApiService $newApi): Response
    {

        $emailSession = $session->get('email');
        // $userId = $session->getId();

        $authenticatedUser = $userRepository->findOneBy(['email' => $emailSession]);
        // $authenticatedUser = $userRepository->findOneBy(['id' => $userId]);

        // Récupère le contenu du message
        $messageContent = $request->request->get('content');

        if ($messageContent === null) {
            $messageContent = ''; 
        }

        // Récupérer l'ID du message parent (s'il y en a un)
        $parentMessageId = $request->request->get('parentMessageId');

        // Si un parentMessageId est fourni, recherche le message parent associé
        $parentMessage = null;
        if ($parentMessageId) {
            $parentMessage = $chatMessageRepository->find($parentMessageId);
        }

        // Créer un nouveau message
        $chatMessage = new ChatMessage();
        $chatMessage->setContent($messageContent);
        $chatMessage->setEventId($eventUid);

        // Associe l'utilisateur & message parent
        $chatMessage->setUser($authenticatedUser);
        $chatMessage->setParentMessage($parentMessage);
        // Persiste en BDD
        $chatMessageRepository->save($chatMessage);

        // return $this->redirectToRoute('app_event_show', ['eventId' => $eventUid]);
        $url = $this->generateUrl('app_event_show', ['eventUid' => $eventUid]);
        
        return $this->redirect($url);
    }

}