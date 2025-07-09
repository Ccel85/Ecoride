import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['validButton', 'trashButton', 'trashButtons', 'validateButton', 'avisButton','avisValidateSignalement', 'avisForm', 'removeAvisButton', 'goButton', 'stopButton']
    
    connect() {
        console.log('validButton_controller connecté');
    }

//Manipulation covoiturage

//Réserver un covoiturage
    validButton(event) {
        const button = event.currentTarget;
        Swal.fire({
            title: 'Confirmation!',
            text: 'Confirmez vous la réservation de ce covoiturage ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Réserver',
            cancelButtonText: "Annuler",
            iconColor: '#39B54E',
            color:'#324D4D',
            cancelButtonColor:'#324D4D',
            confirmButtonColor: '#39B54E',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ 
                title:"Merci!",
                text:"", 
                icon:"success",
                confirmButtonColor: '#39B54E',
                timer: 1500, }); // Durée de l'affichage

                // Redirection vers l'URL
                setTimeout(()=>
                {window.location.href = button.getAttribute('data-url')
                },1500);// Pour attendre que le message disparaisse
                }
            })
        /*  } else {
          //redirection si pas connecté
            window.location.href = 'http://127.0.0.1:8000/login';;
        } */
        }
//Annuler réservation d'un covoiturage
    trashButton(event) {
        const button = event.currentTarget;
            Swal.fire({
                title: 'Confirmation !',
                text: 'Confirmez vous l\'annulation du covoiturage ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Annuler',
                cancelButtonText: "Retour",
                iconColor: '#39B54E',
                color:'#324D4D',
                cancelButtonColor:'#324D4D',
                confirmButtonColor: '#39B54E',
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
                window.location.href = button.getAttribute('data-url');
            });
            }
        });
    }
    
//Supprimer un Covoiturage  
    trashCovoiturage(event) {
    const button = event.currentTarget;
        Swal.fire({
        title: 'Confirmation !',
        text: 'Confirmez-vous la suppression du covoiturage ?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Supprimer',
        cancelButtonText: "Retour",
        iconColor: '#39B54E',
        color:'#324D4D',
        cancelButtonColor:'#324D4D',
        confirmButtonColor: '#39B54E',
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
            window.location.href = button.getAttribute('data-url');
            })
        }
        })
    }

// Validation passager: Voyage terminé 
    validateButton(event) {
        const button = event.currentTarget;

            Swal.fire({
            title: 'Merci',
            text: 'Souhaitez-vous valider votre voyage ?',
            icon: 'question',
            confirmButtonText: 'Oui',
            showCancelButton: true,
            cancelButtonText: 'Non',
            iconColor: '#39B54E',
            color:'#324D4D',
            cancelButtonColor:'#324D4D',
            confirmButtonColor: '#39B54E',
            }).then((result) => {
            if (result.isConfirmed) {
                
                // Récupération de l'URL de validation (route Symfony)
                const validationUrl = button.getAttribute('data-url');

                // Redirection vers le contrôleur Symfony pour validation
                window.location.href = validationUrl;

                // Affichage d'une alerte après redirection (optionnel, dépend de ton contrôleur)
                setTimeout(() => {
                Swal.fire({
                    title: 'Validation réussie !',
                    text: 'Votre voyage a bien été validé.',
                    icon: 'success',
                    confirmButtonColor: '#39B54E',
                })
                })
            }
            })
        }

//démarrer un voyage
    goButton(event) {
        const button = event.currentTarget;
            Swal.fire({
                title: 'Souhaitez vous démarrer le voyage maintenant ?',
                text: '',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Go!!',
                cancelButtonText: "Annuler",
                iconColor: '#39B54E',
                color:'#324D4D',
                cancelButtonColor:'#324D4D',
                confirmButtonColor: '#39B54E',
            }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({ 
                            title:"Bonne route !",
                            text:"", 
                            icon:"success",
                            confirmButtonColor: '#39B54E',})
                        window.location.href = button.getAttribute('data-url');
                        }
                    });
        }
                
//arrêter un voyage
    stopButton(event) {
        const button = event.currentTarget;
            Swal.fire({
                title: 'Souhaitez vous arrêter le voyage ?',
                text: '',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Arrivé!',
                cancelButtonText: "Annuler",
                iconColor: '#39B54E',
                color:'#324D4D',
                cancelButtonColor:'#324D4D',
                confirmButtonColor: '#39B54E',
            }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({ 
                            title:"Merci pour ce covoiturage.",
                            text:"", 
                            icon:"success",
                            confirmButtonColor: '#39B54E',})
                        window.location.href = button.getAttribute('data-url');
                        }
                    });
                }

//Manipulation avis

//Ajouter avis
    avisButton(event) {
        const button = event.currentTarget;
        
            // Affichage de l'alerte pour laisser un avis
            Swal.fire({
                text: 'Souhaitez-vous laisser un avis ?',
                icon: 'question',
                confirmButtonText: 'Oui',
                showCancelButton: true,
                cancelButtonText: 'Non',
                iconColor: '#39B54E',
                color:'#324D4D',
                cancelButtonColor:'#324D4D',
                confirmButtonColor: '#39B54E',
                }).then((reviewResult) => {
                if (reviewResult.isConfirmed) {
                    // Redirection vers la page d'avis
                    window.location.href = button.getAttribute('data-url'); 
                }
                })
            }

//Ajouter un signalement
    signalButton(event) {
        const button = event.currentTarget;
        
            // Affichage de l'alerte pour laisser un avis
            Swal.fire({
                text: 'Souhaitez-vous laisser un signalement ?',
                icon: 'question',
                confirmButtonText: 'Oui',
                showCancelButton: true,
                cancelButtonText: 'Non',
                iconColor: '#39B54E',
                color:'#324D4D',
                cancelButtonColor:'#324D4D',
                confirmButtonColor: '#39B54E',
                }).then((reviewResult) => {
                if (reviewResult.isConfirmed) {
                    // Redirection vers la page d'avis
                    window.location.href = button.getAttribute('data-url'); 
                }
                })
            }
            
//Supprimer Avis
    removeAvisButton(event) {
        const button = event.currentTarget;
                // Affichage de l'alerte SweetAlert pour confirmer
                Swal.fire({
                    text: 'Voulez-vous supprimer l\'avis ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui',
                    cancelButtonText: 'Non',
                    iconColor: '#39B54E',
                    color:'#324D4D',
                    cancelButtonColor:'#324D4D',
                    confirmButtonColor: '#39B54E',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = button.getAttribute('data-url'); 
                    }
                });
            }
/* //A revoir:
//Valider Avis page employé
    setupValidation(event) {
            const validateButton = event.buttonIdTarget;
            const avisForm = event.formIdTarget;

            if (validateButton && avisForm) {
        /*  validateButton.addEventListener('click', (event) => {
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
                    iconColor: '#39B54E',
                    color:'#324D4D',
                    cancelButtonColor:'#324D4D',
                    confirmButtonColor: '#39B54E',
                }).then((result) => {
                    if (result.isConfirmed) {
                        avisForm.submit(); // Soumet le formulaire en POST
                        }
                    })
                }
                // Applique le script aux deux formulaires
                setupValidation('avisValidateAvis', 'avisFormAvis');
                setupValidation('avisValidateSignalement', 'avisFormSignalement');
            } */


}
