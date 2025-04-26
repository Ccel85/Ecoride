console.log("🔥 Mon JS chart est bien chargé !");
document.body.addEventListener('DOMContentLoaded', () => {
  // Boutons
  const chartButton = document.getElementById('chartButton');
  const chartCreditButton = document.getElementById('chartCreditButton');

  // Div chart
  const chart = document.getElementById('chart');
  const chartCredit = document.getElementById('chartCredit');

  console.log("chartButton:", chartButton);
  console.log("chartCreditButton:", chartCreditButton);

  if (chartButton && chartCreditButton) {
    console.log("Boutons trouvés, on attache les événements");

    chartButton.addEventListener('click', (e) => {
      e.preventDefault(); // 🔥 essentiel
      console.log('Clic sur Nombre de covoiturage');
      chart.classList.remove('d-none');
      //chartCredit.classList.add('d-none');
    });

    chartCreditButton.addEventListener('click', (e) => {
      e.preventDefault(); // 🔥 essentiel
      console.log('Clic sur Nombre de crédits');
      chartCredit.classList.remove('d-none');
      //chart.classList.add('d-none');
    });
  }else {
    console.warn("⚠️ Boutons non trouvés");
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const chartButton = document.getElementById('chartButton');

  if (chartButton) {
    chartButton.addEventListener('click', (e) => {
      e.preventDefault();
      console.log("✔ Clic détecté sur 'Nombre de covoiturage'");
    });
  }
});
  // Vérification si les éléments existent
  /* if (chartButton && chartCreditButton && chart && chartCredit) {  
    // Écoute des événements click
    chartButton.addEventListener('click', () => {
      chart.classList.remove('d-none');
     /*  chartCredit.classList.add('d-none'); 
    });

    chartCreditButton.addEventListener('click', () => {
      chartCredit.classList.remove('d-none');
      /* chart.classList.add('d-none'); 
    }); */

    