<?php

// CONTROLLEUR POUR TESTER L'API

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// le but de cette classe est de faire apparaitre les résultats de l'API 

class ApiController extends AbstractController
{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/api', name: 'app_api')]
    public function getDatas(): Response
    {

        $response = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22');

        $statusCode = $response->getStatusCode();
        // $statusCode = 200

        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'

        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'

        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        // Récupérer les résultats de la réponse
        $results = $content['results'];

        $completeData = [];

        foreach ($results as $result) {
            
            $imageUrl = !empty($result['image']) ? $result['image'] : '/assets/img/nopicture.jpg';
            $data = [
                'title' => $result['title_fr'], // ok
                'description' => $result['description_fr'], // ok
                'image_url' => $imageUrl, // ok
                'address' => $result['location_address'], // ok
                'eventId' => $result['uid'], // ok
                'orga' => $result['location_name'], //ok
                'date' => $result['daterange_fr'], // ok
                'location_coordinates' => [
                    'long' => $result['location_coordinates']['lon'],
                    'lat' => $result['location_coordinates']['lat']
                ], // ok
                'longdescription' => strip_tags($result['longdescription_fr']), 

                'start' => $result['firstdate_begin'],
                'end' => $result['firstdate_end'],
                'last_start' => $result['lastdate_begin'],
                'last_end' => $result['lastdate_end'],
                'tags' => null, // Initialisation à null, au cas où le tag n'est pas détecté       
                'categories' => null, // Initialisation à null, au cas où la catégorie n'est pas détectée
                'firstCategory' => null, // Initialisation à null, au cas où la catégorie n'est pas détectée           
            ];

            $categories = [
                "Arts" => [
                    "Comédie",
                    "Atelier",
                    "Sculpture",
                    "Design",
                    "Bijoux",
                    "Ballet",
                    "Chorales",
                    "Comédie Musicale",
                    "Danse",
                    "Littérature",
                    "Orchestres",
                    "Peinture",
                ],
                "Business" => [
                    "ONG",
                    "Start Ups",
                    "Associations",
                    "Carrières",
                    "Investissement",
                    "Immobilier",
                    "Marketing",
                    "Medias",
                    "Petites entreprises"
                ],
                "Brunch-apéro" => [
                    "Apéro",
                    "Bière",
                    "Brunch",
                    "Culinaire",
                    "Restaurants",
                    "Spiritueux"
                ],
                "Communauté" => [
                    "Actions Locales",
                    "Bénévolat",
                    "Cours particuliers",
                    "Histoire",
                    "Langues",
                    "Nationalité",
                    "Parrainages",
                    "Participatif",
                ],
                "Film-médias" => [
                    "Anime",
                    "Adult",
                    "Ciné-débat",
                    "Comédie",
                    "Comics",
                    "Film",
                    "Gaming",
                ],
                "Musique" => [
                    "Alternatif",
                    "Blues",
                    "Classique",
                    "Dj/Dance",
                    "Concert",
                    "Electro",
                    "Festival",
                    "Folk",
                    "Hip Hop/Rap",
                    "Jazz",
                    "Jam",
                    "Techno",
                    "Reggae",
                ],
                "Mode" => [
                    "Accesoires",
                    "Beauté",
                    "Vide-grenier",
                    "Maquillage",
                ],
                "Sports-Fitness" => [
                    "Arts Martiaux",
                    "Basket",
                    "Cyclisme",
                    "Football",
                    "Golf",
                    "Hockey sur Gazon",
                    "Marche",
                    "Moto",
                    "Tennis",
                    "Yoga",
                ],
                "Santé" => [
                    "Bien-être",
                    "Hypnose",
                    "Méditation",
                    "Santé mentale",
                    "Spa"
                ],
            ];

            foreach ($categories as $category => $tags) {
                foreach ($tags as $tag) {
                if (stripos($result['title_fr'], $tag) !== false|| stripos($result['description_fr'], $tag) !== false || stripos($result['longdescription_fr'], $tag) !== false) {
                    $data['tags'][] = $tag;
                    if (!isset($data['categories'][$category])) {
                        $data['categories'][$category] = true;                   
                    }
                  }
                }
            }
                     // Choix d'une catégorie aléatoire parmi celles détectées
                     if (!empty($data['categories'])) {
                        $randomCategory = array_rand($data['categories']);
                        $data['firstCategory'] = $randomCategory;
                    } else {
                        $data['firstCategory'] = 'Inclassable';
                    }

            $completeData[] = $data;
        }
        
        // dump($completeData);
        
        return $this->render('api/api.html.twig', [
            'controller_name' => 'ApiController',
            // 'data' => $jsonContent,
            'data' => $completeData,
        ]);
    }
}
