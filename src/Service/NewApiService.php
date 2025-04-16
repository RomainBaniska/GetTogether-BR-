<?php

namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
// use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewApiService
{
    
    private $client;
    private $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function getEventDetail(string $eventUid): array {
        $responsePage = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222025%22&refine=location_city%3A%22Paris%22&' . $eventUid);
       
        $statusCode = $responsePage->getStatusCode();
        if ($statusCode !== 200) {
            return null;
        }

        $resultsPage = $responsePage->toArray();

        foreach ($resultsPage['results'] as $result) {
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

    public function getEventDetailBeyond(string $eventUid): ?array
{
    $responsePage = $this->client->request(
        'GET',
        'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222025%22&refine=location_city%3A%22Paris%22'
    );

    if ($responsePage->getStatusCode() !== 200) {
        return null;
    }

    $resultsPage = $responsePage->toArray();
    $results = $resultsPage['results'] ?? [];

    foreach ($results as $result) {
        if ($result['uid'] === $eventUid) {
            $data = [
                'title' => $result['title_fr'],
                'description' => $result['description_fr'],
                'image' => $result['image'] ?? '/assets/img/nopicture.jpg',
                'address' => $result['location_address'],
                'eventUid' => $result['uid'],
                'date' => $result['daterange_fr'],
                'orga' => $result['location_name'],
                'location_coordinates' => [
                    'long' => $result['location_coordinates']['lon'],
                    'lat' => $result['location_coordinates']['lat']
                ],
                'longdescription' => strip_tags($result['longdescription_fr']),
                'start' => $result['firstdate_begin'],
                'end' => $result['firstdate_end'],
                'last_start' => $result['lastdate_begin'],
                'last_end' => $result['lastdate_end'],
                'tags' => [],
                'categories' => [],
                'firstCategory' => null
            ];

            $categories = [
                "Arts" => ["Comedie", "Atelier", "Sculpture", "Design", "Bijoux", "Ballet", "Chorales", "Comédie Musicale", "Danse", "Littérature", "Orchestres", "Peinture"],
                "Business" => ["ONG", "Start Ups", "Associations", "Carrières", "Investissement", "Immobilier", "Marketing", "Medias", "Petites entreprises"],
                "Brunch-apéro" => ["Apéro", "Bière", "Brunch", "Culinaire", "Restaurants", "Spiritueux"],
                "Communauté" => ["Actions Locales", "Bénévolat", "Cours particuliers", "Histoire", "Langues", "Nationalité", "Parrainages", "Participatif"],
                "Film-médias" => ["Anime", "Adult", "Ciné-débat", "Comédie", "Comics", "Film", "Gaming"],
                "Musique" => ["Alternatif", "Blues", "Classique", "Dj/Dance", "Concert", "Electro", "Festival", "Folk", "Hip Hop/Rap", "Jazz", "Jam", "Techno", "Reggae"],
                "Mode" => ["Accesoires", "Beauté", "Vide-grenier", "Maquillage"],
                "Sports-Fitness" => ["Arts Martiaux", "Basket", "Cyclisme", "Football", "Golf", "Hockey sur Gazon", "Marche", "Moto", "Tennis", "Yoga"],
                "Santé" => ["Bien-être", "Hypnose", "Méditation", "Santé mentale", "Spa"],
            ];

            foreach ($categories as $category => $tags) {
                foreach ($tags as $tag) {
                    if (
                        stripos($result['title_fr'], $tag) !== false ||
                        stripos($result['description_fr'], $tag) !== false ||
                        stripos($result['longdescription_fr'], $tag) !== false
                    ) {
                        $data['tags'][] = $tag;
                        $data['categories'][$category] = true;
                    }
                }
            }

            // Nettoyage pour éviter doublons/vides
            $data['tags'] = array_unique($data['tags']);
            if (empty($data['categories'])) {
                $data['firstCategory'] = 'Inclassable';
            } else {
                $data['firstCategory'] = array_rand($data['categories']);
            }

            return $data;
        }
    }

    return null;
}

    // public function getDataById(string $eventUid): array {

    //     // return $this->cache->get('event_'.$eventUid, function (ItemInterface $item) use ($eventUid) {
    //     //     $item->expiresAfter(3600); // 1 heure de cache

    //     //  $response = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22&' . $eventUid);
    //     $response = $this->client->request('GET', "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22&where=uid%3D%22{$eventUid}%22");

    //     $statusCode = $response->getStatusCode();
    //     if ($statusCode !== 200) {
    //         return null;
    //     }

    //     $results = $response->toArray();

    //     foreach ($results['results'] as $result) {
    //         if ($result['uid'] === $eventUid) {
    //             $imageUrl = !empty($result['image']) ? $result['image'] : '/assets/img/nopicture.jpg';
    //             return [
    //                 'title' => $result['title_fr'],
    //                 'description' => $result['description_fr'],
    //                 'image' => $imageUrl,
    //                 'address' => $result['location_address'],
    //                 'eventUid' => $result['uid'], 
    //                 'date' => $result['daterange_fr'], 
    //                 'orga' => $result['location_name'], 
    //                 'eventUid' => $result['uid'],
    //                 'location_coordinates' => [
    //                     'long' => $result['location_coordinates']['lon'],
    //                     'lat' => $result['location_coordinates']['lat']
    //                 ],
    //             ];

    //         }
    //     }
    //     return null;
    // //  }); // Fin du cache
    // }

    public function getDatas(): array // attention array, pas une response car y'a pas de render
    {
        // $response = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222024%22&refine=location_city%3A%22Paris%22');
        $response = $this->client->request('GET', 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?select=uid%2C%20title_fr%2C%20description_fr%2C%20image%2C%20firstdate_begin%2C%20firstdate_end%2C%20lastdate_begin%2C%20lastdate_end%2C%20location_coordinates%2C%20location_name%2C%20location_address%2C%20daterange_fr%2C%20longdescription_fr&limit=-1&refine=updatedat%3A%222025%22&refine=location_city%3A%22Paris%22');

        $content = $response->getContent();

        // On définit comme contenu réponse qu'on a extraite et qu'on met dans un tableau.
        $content = $response->toArray(); // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        // Récupérer les résultats de la réponse. Si la réponse est vide ou null, renvoie un tableau vide
        $results = $content['results'] ?? [];

        // On initialise un tableau pour la data complète
        $completeData = [];

        // Pour chaque résulat retourné par l'API, on crée un tableau data.
        foreach ($results as $result) {
            $data = [
                'title' => $result['title_fr'], 
                'description' => $result['description_fr'], 
                'image_url' => $result['image'] ?? 'assets/img/nopicture.jpg', 
                'address' => $result['location_address'], 
                'eventId' => $result['uid'], 
                'orga' => $result['location_name'], 
                'date' => $result['daterange_fr'], 
                'location_coordinates' => [
                    'long' => $result['location_coordinates']['lon'],
                    'lat' => $result['location_coordinates']['lat']
                ], 
                // strip_tags supprime toutes les balises (style <p>) dans la string
                'longdescription' => strip_tags($result['longdescription_fr']), 

                'start' => $result['firstdate_begin'],
                'end' => $result['firstdate_end'],
                'last_start' => $result['lastdate_begin'],
                'last_end' => $result['lastdate_end'],
                'tags' => null, // Initialisation à null, au cas où le tag n'est pas détecté       
                'categories' => null, // Initialisation à null, au cas où la catégorie n'est pas détectée  
                'firstCategory' => null, // Initialisation à null, au cas où la catégorie n'est pas détectée          
            ];

            // Liste des tags pour toutes les catégories
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

            // On crée un tableau associatif categories où la clé est une catégorie et les valeurs sont des tags
            // Ressemblant un peu à ceci : 
            // $categories = [
            //     'Sport' => ['football', 'basketball', 'tennis'],
            //     'Musique' => ['rock', 'jazz', 'classique'],
            // ];
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

        // Retourne l'ensemble des datas traitées
        return $completeData;
    }

}