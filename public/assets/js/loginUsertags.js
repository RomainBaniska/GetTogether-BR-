document.addEventListener('DOMContentLoaded', () => { 

  console.log("ça marche");

  // CONSTANTES
  const categories = document.getElementById('categories');
  const buttons = document.querySelectorAll('.tag-choice');
  const tagSearch = document.getElementById('tagsearch');
  let tagsChoices = []; // Initialiser le tableau des tags
  let saveTagsBtn = document.getElementById('btn-tags-save');

  buttons.forEach(button => {
     // Traitement du clic 
     button.addEventListener('click', (e) => {
      const clickedButton = e.target; // Récupérer le bouton sur lequel le clic s'est produit
        
      // Appeler la fonction pour basculer les classes
      clickedButton.classList.toggle('tag-choice-active');
      // ajoute l'item à la liste des tags ou l'en retire si besoin
      if (clickedButton.classList.contains("tag-choice-active")) {
        tagsChoices.push(clickedButton.textContent);
      } else {
        tagsChoices.splice(tagsChoices.indexOf(clickedButton.textContent), 1);
      }
    });

            });



if (categories) {
  // Ajouter un gestionnaire d'événements "change" à la balise select avec l'ID "categories"
  // filtrage des catégories via la searchbar.
  categories.addEventListener('change', () => {

    // Récupérer les options sélectionnées
    const selectedOptions = Array.from(categories.selectedOptions);
    // Créer un tableau pour stocker les valeurs sélectionnées 
    const selectedValues = selectedOptions.map(option => option.value);
    // Récupérer tous les articles dans la section mestags
    const articles = document.querySelectorAll('#mestags article');

    // Parcourir chaque article 
    articles.forEach(article => {
      // Récupérer chaque categorie de l'article (utiliser l'ID de l'article sans le préfixe "#")
      const category = article.id;

      // Vérifier si la categorie de l'article est incluse dans les valeurs sélectionnées 
      if (selectedValues.includes (category)) {
        article.style.display = 'block';
        } else {
          // Sinon, masquer l'article 
        article.style.display = 'none';
        }
    });
  });
  }

  if (tagSearch) {
    // Ajouter un gestionnaire d'événements "input" à la barre de recherche avec l'ID "tagsearch"
    tagSearch.addEventListener('input', () => {
      // Récupérer la valeur saisie dans la barre de recherche
      const searchString = tagSearch.value.toLowerCase();
      // Récupérer tous les titres d'article (éléments h2) dans la section mestags
      const articleTitles = document.querySelectorAll('#mestags article h2');
      // Parcourir chaque titre d'article
      articleTitles.forEach(title => {
        // Récupérer le texte du titre d'article 
        const titleText = title.textContent.toLowerCase();
        // Vérifier si le texte du titre d'article contient la chaîne de caractères saisie dans la recherche
        if (titleText.includes(searchString)) {
          // Si le titre d'article contient la chaîne de caractères saisie, afficher l'article parent
          title.parentElement.style.display = 'block';
        } else {
          // Sinon masquer l'article parent
          title.parentElement.style.display = "none";
        }
      });
    });
  }

  // Si les BTN tags existent :
  if (saveTagsBtn) {
    // Cliquer sur les boutons fetch la route du dashboardcontroller
    saveTagsBtn.addEventListener('click', () => {
      let jsonOutput = JSON.stringify(tagsChoices);
      // Fetch sur le DashboardController! (cf : jsonTags)
      fetch('/mestags/save/' + jsonOutput)
      .then(response => {  
        if (response.status == 200) {
          return response.text();
        }
        throw new Error('Echec de sauvegarde des tags');
      })
      .then(json => {
        console.log(JSON.parse(json));
      })
      .catch(error => {
        console.error('Erreur:', error);
      });
    });
  }

});