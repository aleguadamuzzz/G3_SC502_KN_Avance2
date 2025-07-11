function saludar() {
    alert('¡Bienvenido a la Red de Intercambio de Alimentos!');
}

document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const username = this.username.value.trim();
            const email = this.email.value.trim();
            const pwd = this.password.value;
            if (username.length < 3) {
                alert('El nombre de usuario debe tener al menos 3 letras.');
                e.preventDefault();
                return;
            }
            if (pwd.length < 6) {
                alert('La contraseña debe tener al menos 6 letras.');
                e.preventDefault();
            }
        });
    }

    const publishForm = document.getElementById('publishForm');
    if (publishForm) {
        publishForm.addEventListener('submit', function (e) {
            const nombre = this.nombre.value.trim();
            const descripcion = this.descripcion.value.trim();
            const caducidad = this.caducidad.value;
            const ubicacion = this.ubicacion.value.trim();
            if (!nombre) {
                alert('El nombre del alimento es obligatorio.');
                e.preventDefault();
                return;
            }
            if (descripcion.length < 5) {
                alert('La descripcion debe tener al menos 5 letras.');
                e.preventDefault();
                return;
            }
            if (!caducidad) {
                alert('Selecciona la fecha de caducidad.');
                e.preventDefault();
                return;
            }
        });
    }
});