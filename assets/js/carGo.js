import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
  const goButton = document.getElementById('carGo');

  if (goButton) {
    goButton.addEventListener('click', () => {
        Swal.fire({
              title: 'Souhaitez vous démarrer le voyage mainteant ?',
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
              goButton.classList.add('btn-danger'); */
              });
          }
              });