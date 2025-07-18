document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('show-all-comments');
    if (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.extra-comment').forEach(function (el) {
          el.classList.remove('d-none');
        });
        btn.classList.add('d-none');
      });
    }
  });