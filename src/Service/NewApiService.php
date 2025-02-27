<?php

namespace App\Service;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewApiService
{
    
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getDataById(string $eventUid): array {

        //  $response = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22&' . $eventUid);
         $response = $this->client->request('GET', "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22&where=uid%3D%22{$eventUid}%22");

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return null;
        }

        $results = $response->toArray();

        foreach ($results['results'] as $result) {
            if ($result['uid'] === $eventUid) {
                $imageUrl = !empty($result['image']) ? $result['image'] : '/assets/img/nopicture.jpg';
                return [
                    'title' => $result['title_fr'],
                    'description' => $result['description_fr'],
                    'image' => $imageUrl,
                    'address' => $result['location_address'],
                    'eventUid' => $result['uid'], 
                    'date' => $result['daterange_fr'], 
                    'orga' => $result['location_name'], 
                    'eventUid' => $result['uid'],
                    'location_coordinates' => [
                        'long' => $result['location_coordinates']['lon'],
                        'lat' => $result['location_coordinates']['lat']
                    ],
                ];

            }
        }
        return null;
    
    }

    public function getDatas(): array // attention array, pas une response car y'a pas de render
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
                'title' => $result['title_fr'], 
                'description' => $result['description_fr'], 
                'image_url' => $imageUrl, 
                'address' => $result['location_address'], 
                'eventId' => $result['uid'], 
                'orga' => $result['location_name'], 
                'date' => $result['daterange_fr'], 
                'location_coordinates' => [
                    'long' => $result['location_coordinates']['lon'],
                    'lat' => $result['location_coordinates']['lat']
                ], 
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
                    "Comedie",
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
        return $completeData;
    }

}