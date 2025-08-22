function saludar() {
    alert('Bienvenido a la Red de Intercambio de Alimentos!');
}

document.addEventListener('DOMContentLoaded', function () {
    // Aqui hacemos una validacion de los formularios como tal.
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
                return;
            }
            if (!email.includes('@')) {
                alert('Por favor ingresa un email valido.');
                e.preventDefault();
                return;
            }
        });
    }

    // Aqui hacemos una validacion del formulario de publicar alimentos
    const publishForm = document.getElementById('publicarForm');
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
            if (!ubicacion) {
                alert('La ubicacion es obligatoria.');
                e.preventDefault();
                return;
            }

            const fechaCaducidad = new Date(caducidad);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaCaducidad < hoy) {
                alert('La fecha de caducidad no puede ser anterior a hoy.');
                e.preventDefault();
                return;
            }
        });
    }

    // Aqui hacemos una validacion del formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const email = this.email.value.trim();
            const password = this.password.value.trim();

            if (!email || !password) {
                alert('Todos los campos son obligatorios.');
                e.preventDefault();
                return;
            }
            if (!email.includes('@')) {
                alert('Por favor ingresa un email válido.');
                e.preventDefault();
                return;
            }
        });
    }
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
});