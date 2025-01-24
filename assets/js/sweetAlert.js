import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
  const validButton = document.getElementById('participer');

  if (validButton) {
    validButton.addEventListener('click', () => {
        Swal.fire({
              title: 'Confirmation !',
              text: 'Confirmez vous la réservation de ce covoiturage ?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Réserver',
              cancelButtonText: "Annuler",
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              Swal.fire({ 
                title:"Votre covoiturage est confirmé",
                text:"", 
                icon:"success",
                timer: 1500, }); // Durée de l'affichage
              window.location.href = validButton.getAttribute('data-url');
            }
          })
      });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const trashButton = document.getElementById('trash');

  if (trashButton) {
    trashButton.addEventListener('click', (e) => {

      e.preventDefault(); // Empêche l'action par défaut du bouton ou lien

        Swal.fire({
              title: 'Confirmation !',
              text: 'Confirmez vous l\'annulation de la réservation de ce covoiturage ?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Annuler',
              cancelButtonText: "Retour",
          }).then((result) => {
        if (result.isConfirmed) {
          // Afficher un message de succès avant la redirection
          Swal.fire({
            title: 'Succès',
            text: 'Votre annulation a été validée.',
            icon: 'success',
            timer: 1500, // Durée de l'affichage
            showConfirmButton: false, // Pas de bouton
          }).then(() => {
            // Redirection après le succès
            window.location.href = trashButton.getAttribute('data-url');
          });
        }
      });
    });
  }
});
