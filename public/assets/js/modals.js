document.addEventListener('DOMContentLoaded', (e) => {

    //@todo : Ajouter une classe pour de cibler que les formulaire devant lancer des modals
    const elements = document.querySelectorAll('form button.registration[type="submit"]');

    elements.forEach(function(element){
        element.addEventListener('click', (e) => {

            // annule le comportement normal du bouton
            e.preventDefault();

            // On récupère toutes les données du formulaire
            const formData = new FormData(e.target.form);

            // On construit dynamiquement un nom d'un élément caché (input hidden) qui prend le nom du formulaire et lui rajoute '_destination'
            const hiddenElementName = e.target.form.name + '_destination';
            // En l'occurence pour la route registration, le nom sera "registration_form_destination"
            console.log(hiddenElementName);
            const url = document.getElementById(hiddenElementName).value;
            console.log(url); // en l'occurence "/registration"
;
            // Envoyer les données du formulaire au serveur -> En gros renvoyer à "registration"
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Si le statut est 200 (positif)
                if (response.status == 200) {
                    // On sélectionne le bouton de la modal et on change son style, son comportement et ce qu'il y est écrit
                    let btn = document.querySelector('#modal .modal-footer button');
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-primary');
                    btn.innerHTML = 'Me connecter';
                    btn.addEventListener('click', (e) => {
                        document.location.replace('/login');
                    });
                }
                
                // Si la réponse est OK ou UNAUTHORISED
                if (response.status == 200 || response.status == 401 ) { 
                    return response.text();
                }
            })
            .then(html => {
                document.querySelector('#modal .modal-body').innerHTML = html;

                // document.querySelector('#modal .modal-body').innerHTML = 
                // new DOMParser().parseFromString(html, 'text/html').querySelector('#error-message').innerHTML;


                const myModal = new bootstrap.Modal('#modal', {keyboard: false})
                myModal.show();

                // TODO => ici

                // // Création d'un DOM temporaire pour analyser la réponse HTML
                // const tempDiv = document.createElement('div');
                // tempDiv.innerHTML = html;
 
                // // Recherche des erreurs dans la réponse
                // const errors = tempDiv.querySelector('.form-error-message, .invalid-feedback');
 
                // if (errors) {
                //      // Affiche seulement les erreurs dans la modal
                //      document.querySelector('#modal .modal-body').innerHTML = errors.outerHTML;
                // } else {
                //      // Sinon, affiche tout le contenu dans la modal
                //      document.querySelector('#modal .modal-body').innerHTML = html;
                // }
 
                // // Affichage de la modal
                // const myModal = new bootstrap.Modal('#modal', {keyboard: false});
                // myModal.show();
            })
            .catch(error => {
                console.error('Une erreur s\'est produite lors de la validation :', error);
            });            
        });
    });

});