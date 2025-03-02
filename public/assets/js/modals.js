document.addEventListener('DOMContentLoaded', (e) => {

    //@todo : Ajouter une classe pour de cibler que les formulaire devant lancer des modals
    const elements = document.querySelectorAll('form button.registration[type="submit"]');
    const pw1 = document.getElementById('registration_form_password_first');
    const pw2 = document.getElementById('registration_form_password_second');

    elements.forEach(function(element){
        // if (pw1 === pw2) {
        element.addEventListener('click', (e) => {

            // annule le comportement normal du bouton
            e.preventDefault();

            console.log("yay");

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
                    btn.style.visibility = "visible";
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
                // if (!(pw1 === pw2)) {
                console.log(pw1.value);
                console.log(pw2.value);
                if (pw1.value !== pw2.value) {
                    let btn = document.querySelector('#modal .modal-footer button');
                    btn.style.visibility = "hidden";
                    document.querySelector('#modal .modal-body').innerHTML = "les passwords ne correspondent pas";
                } else {
                document.querySelector('#modal .modal-body').innerHTML = html;
                }
            


                const myModal = new bootstrap.Modal('#modal', {keyboard: false})
                myModal.show();

                // TODO => ici

            })
            .catch(error => {
                console.error('Une erreur s\'est produite lors de la validation :', error);
            });            
        });
    // }// Fin condition pw1 === pw2
    });

});