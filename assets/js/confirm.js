/* const participer = document.getElementById("particip");

participer.addEventListener('click',function(){
alert('Confirmer vous la réservation de ce covoiturage')
}); */

document.addEventListener('DOMContentLoaded', () => {
  const alertButton = document.getElementById('participer');

  if (alertButton) {
      alertButton.addEventListener('click', () => {
        Swal.fire({
              title: 'Bienvenue !',
              text: 'Ceci est une alerte SweetAlert2.',
              icon: 'success',
              confirmButtonText: 'Ok'
          });
      });
  }
});