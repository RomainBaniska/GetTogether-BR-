<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(EventRepository $eventRepository): Response
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
    
        // dump($calendarEvents);
        // dump($calendarEvent);

        $datas = json_encode($calendarEvents);
        
        return $this->render('calendar/calendar.html.twig', [
            'controller_name' => 'CalendarController',
            'datas' => $datas,
        ]);
    }
}
