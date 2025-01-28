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

document.addEventListener('DOMContentLoaded', () => {
  const validateButton = document.getElementById('validate');

  if (validateButton) {
    validateButton.addEventListener('click', () => {
        Swal.fire({
              title: 'Merci',
              text: 'Souhaitez vous déposer un avis ? ',
              icon: 'question',
              confirmButtonText: 'Ok',
              showCancelButton: true,
              cancelButtonText: 'Non',
              }).then((result) => {
                          if (result.isConfirmed) {
                            window.location.href = validateButton.getAttribute('data-url')
                        }
          })
        }
    )}
  });