let avatarList = ["Group75.png", "Group77.png", "Group76.png", "Group73.png", "Frame74.png", "Frame89.png", "Frame88.png", "Frame91.png", "Frame90.png", "Frame92.png"];


let submitButton = document.querySelector('button[type="submit"]');
let fileInput = document.getElementById('photo_form_photo');
let divAvatar = document.querySelector('.divAvatar');
// let fileDiv = document.querySelector('inputsub');

// Création d'un champs caché pour stocker l'avatar choisi 
let avatarInput = document.createElement('input');
avatarInput.type = 'hidden';
avatarInput.name = 'selected_avatar';
fileInput.closest('form').appendChild(avatarInput);



let labelFile = document.querySelector('.required');
// let labelFile = document.querySelector('.subform');

// On injecte les avatars TimeOut Dans la page
divAvatar.innerHTML = avatarList.map(img => `<div class="avatarPicture col-sm-6">
    <img class="avatarImg" src="/assets/img/avatar/${img}" alt="Avatar" data-avatar="${img}"></div>`).join("");


// On gère la sélection d'un avatar
divAvatar.addEventListener('click', (event) => {
    let selectedAvatar = event.target.closest('.avatarImg');
    if (selectedAvatar) {
        avatarInput.value = selectedAvatar.dataset.avatar; // Stocke le nom de l'avatar sélectionné
        fileInput.value = ''; // Réinitialise l'upload de fichier
        console.log("Avatar choisi :", avatarInput.value);
    }
});

// Gérer l'upload manuel
fileInput.addEventListener('change', () => {
    avatarInput.value = ''; // Réinitialise l'avatar si un fichier est uploadé
    console.log("Fichier uploadé :", fileInput.files[0]?.name);
});



labelFile.innerHTML = '<div class="imgPicture"><i class="fa-solid fa-upload"></i></div>';



