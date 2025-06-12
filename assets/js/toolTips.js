import { Tooltip } from 'bootstrap';

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
  new Tooltip(el);
});

