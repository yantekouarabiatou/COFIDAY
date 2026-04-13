import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
import Swal from 'sweetalert2';

// Rendre Swal disponible globalement
window.Swal = Swal;

// Configuration par défaut
Swal.defaults = {
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    buttonsStyling: true,
    reverseButtons: true
};
