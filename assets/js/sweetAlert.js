/* import Swal from 'sweetalert2';

//valider participation au covoiturage
document.addEventListener('DOMContentLoaded', () => {
  const validButton = document.getElementById('participer');
  if (validButton) {

    /* const token = localStorage.getItem('authToken'); 

    validButton.addEventListener('click', () => {
      /* if (token) { 
        Swal.fire({
              title: 'Confirmation !',
              text: 'Confirmez vous la réservation de ce covoiturage ?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Réserver',
              cancelButtonText: "Annuler",
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.fire({ 
                title:"Votre covoiturage est confirmé",
                text:"", 
                icon:"success",
                timer: 1500, }); // Durée de l'affichage

                // Redirection vers l'URL
                setTimeout(()=>
                {window.location.href = validButton.getAttribute('data-url')
                },1500);// Pour attendre que le message disparaisse
              }
          })
       /*  } else {
          //redirection si pas connecté
          window.location.href = 'http://127.0.0.1:8000/login';;
        } 
      });
  }
});

//annuler réservation 
document.addEventListener('DOMContentLoaded', () => {
  const trashButton = document.getElementById('trash');

  if (trashButton) {
    trashButton.addEventListener('click', (e) => {

      e.preventDefault(); // Empêche l'action par défaut du bouton ou lien

        Swal.fire({
              title: 'Confirmation !',
              text: 'Confirmez vous l\'annulation du covoiturage ?',
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
})
  
  //annuler Covoiturage  
  document.addEventListener('DOMContentLoaded', () => {
    // Sélectionne tous les boutons avec l'ID "trash"
    const trashButtons = document.querySelectorAll('[id^="trash_"]');
  
    // Ajouter un écouteur d'événement sur chaque bouton
    trashButtons.forEach((trashButton) => {
      trashButton.addEventListener('click', (e) => {
        e.preventDefault(); // Empêche l'action par défaut du bouton ou lien
  
        Swal.fire({
          title: 'Confirmation !',
          text: 'Confirmez-vous la suppression du covoiturage ?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Supprimer',
          cancelButtonText: "Retour",
        }).then((result) => {
          if (result.isConfirmed) {
            // Afficher un message de succès avant la redirection
            Swal.fire({
              title: 'Succès',
              text: 'Votre voyage a été annulé. Un message sera envoyé aux passagers.',
              icon: 'success',
              timer: 2500, // Durée de l'affichage
              showConfirmButton: false, // Pas de bouton
            }).then(() => {
              // Redirection après le succès
              window.location.href = trashButton.getAttribute('data-url');
            });
          }
        });
      });
    });
  });

// Validation passager Voyage terminé 
  document.addEventListener('DOMContentLoaded', () => {
    const validateButton = document.getElementById('validate');

    if (validateButton) {
      validateButton.addEventListener('click', (event) => {

        event.preventDefault(); // Empêche la redirection immédiate

        Swal.fire({
          title: 'Merci',
          text: 'Souhaitez-vous valider votre voyage ?',
          icon: 'question',
          confirmButtonText: 'Oui',
          showCancelButton: true,
          cancelButtonText: 'Non',
        }).then((result) => {
          if (result.isConfirmed) {
            // Récupération de l'URL de validation (route Symfony)
            const validationUrl = validateButton.getAttribute('data-url');

            // Redirection vers le contrôleur Symfony pour validation
            window.location.href = validationUrl;

            // Affichage d'une alerte après redirection (optionnel, dépend de ton contrôleur)
            setTimeout(() => {
              Swal.fire({
                title: 'Validation réussie !',
                text: 'Votre voyage a bien été validé.',
                icon: 'success'
              })
            })
          }
        })
      })
    }
  })

    //Ajouter avis
    document.addEventListener('DOMContentLoaded', () => {
      const avisButton = document.getElementById('checkAvis');

      if (avisButton) {
        avisButton.addEventListener('click', (event) => {

          event.preventDefault(); // Empêche la redirection immédiate
        
            // Affichage de l'alerte pour laisser un avis
            Swal.fire({
              text: 'Souhaitez-vous laisser un avis ?',
              icon: 'question',
              confirmButtonText: 'Oui',
              showCancelButton: true,
              cancelButtonText: 'Non',
            }).then((reviewResult) => {
              if (reviewResult.isConfirmed) {
                // Redirection vers la page d'avis
                window.location.href = avisButton.getAttribute('data-url'); 
              }
            })
          })
        }
      });

    //Valider Avis
      document.addEventListener('DOMContentLoaded', () => {
        //fonction validation
        function setupValidation(buttonId, formId) {
          const validateButton = document.getElementById(buttonId);
          const avisForm = document.getElementById(formId);

        if (validateButton && avisForm) {
          validateButton.addEventListener('click', (event) => {
                event.preventDefault(); // Empêche la soumission immédiate du formulaire
    
                // Vérifie si au moins une case est cochée
                const checkboxes = avisForm.querySelectorAll('input[name="isValid[]"]:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({
                        text: 'Veuillez sélectionner au moins un avis à valider.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                // Affichage de l'alerte SweetAlert pour confirmer
                Swal.fire({
                    text: 'Voulez-vous valider les avis sélectionnés ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui',
                    cancelButtonText: 'Non',
                }).then((result) => {
                    if (result.isConfirmed) {
                        avisForm.submit(); // Soumet le formulaire en POST
                    }
                });
            });
        }
      }
      // Applique le script aux deux formulaires
      setupValidation('avisValidateAvis', 'avisFormAvis');
      setupValidation('avisValidateSignalement', 'avisFormSignalement');
    });
    
    //Supprimer Avis
    document.addEventListener('DOMContentLoaded', () => {
      const removeAvisButton = document.getElementById('removeAvis');
  
      if (removeAvisButton ) {
        removeAvisButton.addEventListener('click', (event) => {
              event.preventDefault(); // Empêche la soumission immédiate du formulaire
  
              // Affichage de l'alerte SweetAlert pour confirmer
              Swal.fire({
                  text: 'Voulez-vous supprimer l\'avis ?',
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonText: 'Oui',
                  cancelButtonText: 'Non',
              }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href = removeAvisButton.getAttribute('data-url'); 
                  }
              });
          });
      }
  });
  
  //démarrer un voyage
  document.addEventListener('DOMContentLoaded', () => {
    const goButton = document.getElementById('carGo');
  
    if (goButton) {
      goButton.addEventListener('click', () => {
          Swal.fire({
                title: 'Souhaitez vous démarrer le voyage maintenant ?',
                text: '',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Go!!',
                cancelButtonText: "Annuler",
            }).then((result) => {
                        if (result.isConfirmed) {
                          Swal.fire({ 
                            title:"Bonne route !",
                            text:"", 
                            icon:"success",})
                      window.location.href = goButton.getAttribute('data-url');
                      }
                    });
                /* goButton.textContent = "Arrivé!";
                goButton.classList.remove('btn-succes');
                goButton.classList.add('btn-danger'); 
                });
            }
                });
  
  //arreter un voyage
  document.addEventListener('DOMContentLoaded', () => {
    const stopButton = document.getElementById('carStop');
  
    if (stopButton) {
      stopButton.addEventListener('click', () => {
          Swal.fire({
                title: 'Souhaitez vous arrêter le voyage ?',
                text: '',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Arrivé!',
                cancelButtonText: "Annuler",
            }).then((result) => {
                        if (result.isConfirmed) {
                          Swal.fire({ 
                            title:"Merci pour ce covoiturage.",
                            text:"", 
                            icon:"success",})
                      window.location.href = stopButton.getAttribute('data-url');
                      }
                    });
                /* goButton.textContent = "Arrivé!";
                goButton.classList.remove('btn-succes');
                goButton.classList.add('btn-danger'); 
                });
            }
                });

                
 */