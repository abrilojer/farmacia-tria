console.log('🔵 ========== APP.JS CARGADO ==========');
console.log('🔵 Fecha:', new Date().toLocaleString());
console.log('🔵 URL actual:', window.location.href);

window.usuarioActual = window.usuarioActual || null;

console.log('🔵 Usuario al cargar:', window.usuarioActual);
console.log('🔵 ========================================');

const API_URL = 'http://localhost/farmacia_tria/api';

// Funciones auxiliares
function mostrarError(inputId, mensaje) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const feedbackDiv = input.nextElementSibling;
    input.classList.add('is-invalid');

    if (feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
        feedbackDiv.textContent = mensaje;
        feedbackDiv.style.display = 'block';
    }
}

function limpiarErrores() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function mostrarAlerta(mensaje, tipo = 'danger') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        setTimeout(() => alertDiv.remove(), 5000);
    }
}

// REGISTRO
function registrarUsuario(event) {
    event.preventDefault();
    console.log('🔵 Iniciando registro...');
    limpiarErrores();

    const correo = document.getElementById('correo').value;
    const password = document.getElementById('password').value;

    if (!correo || !password) {
        if (!correo) mostrarError('correo', 'El correo es obligatorio');
        if (!password) mostrarError('password', 'La contraseña es obligatoria');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) {
        mostrarError('correo', 'Por favor ingresa un correo válido');
        return;
    }

    if (password.length < 6) {
        mostrarError('password', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }

    const formData = new FormData();
    formData.append('correo', correo);
    formData.append('contrasena', password);

    fetch(`${API_URL}/registro.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(text => {
            console.log('Respuesta registro:', text);
            const data = JSON.parse(text);

            if (data.message === "Usuario registrado exitosamente.") {
                mostrarAlerta('¡Registro exitoso! Redirigiendo...', 'success');
                setTimeout(() => window.location.href = 'login.html', 2000);
            } else if (data.message === "El correo ya está registrado.") {
                mostrarError('correo', 'Este correo ya está registrado.');
            } else {
                mostrarAlerta(data.message, 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al conectar con el servidor.', 'danger');
        });
}

// LOGIN
function iniciarSesion(event) {
    event.preventDefault();
    console.log('🔵 Iniciando login...');
    limpiarErrores();

    const correo = document.getElementById('correo').value;
    const password = document.getElementById('password').value;

    if (!correo || !password) {
        if (!correo) mostrarError('correo', 'El correo es obligatorio');
        if (!password) mostrarError('password', 'La contraseña es obligatoria');
        return;
    }

    const formData = new FormData();
    formData.append('correo', correo);
    formData.append('contrasena', password);

    fetch(`${API_URL}/login.php`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(text => {
            console.log('Respuesta login:', text);
            const data = JSON.parse(text);

            if (data.usuario_id) {
                window.usuarioActual = {
                    id: data.usuario_id,
                    correo: data.correo
                };
                console.log('✅ Usuario guardado:', window.usuarioActual);

                mostrarAlerta('¡Inicio de sesión exitoso!', 'success');
                setTimeout(() => window.location.href = 'catalogo.html', 1500);
            } else if (data.message === "Usuario no encontrado.") {
                mostrarError('correo', 'No existe una cuenta con este correo.');
            } else if (data.message === "Contraseña incorrecta.") {
                mostrarError('password', 'La contraseña es incorrecta.');
            } else {
                mostrarAlerta(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al conectar con el servidor.', 'danger');
        });
}

// PRODUCTOS
function cargarProductosDesdeDB() {
    console.log('🔵 Cargando productos...');

    fetch(`${API_URL}/productos.php`)
        .then(response => response.json())
        .then(productos => {
            console.log('✅ Productos cargados:', productos.length);
            renderizarProductos(productos);
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
}

function renderizarProductos(productos) {
    const medicamentos = productos.filter(p => p.nombre_categoria === 'Medicamentos');
    const productosPersonales = productos.filter(p => p.nombre_categoria === 'Productos');
    const cosmeticos = productos.filter(p => p.nombre_categoria === 'Cosméticos');

    renderizarCategoria('medicamentos', medicamentos);
    renderizarCategoria('productos', productosPersonales);
    renderizarCategoria('cosmeticos', cosmeticos);
}

function renderizarCategoria(seccionId, productos) {
    const seccion = document.getElementById(seccionId);
    if (!seccion) return;

    const contenedor = seccion.querySelector('.row.g-4');
    if (!contenedor) return;

    contenedor.innerHTML = '';

    productos.forEach(producto => {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6 col-lg-4';

        const sinStock = producto.stock <= 0;
        const stockBajo = producto.stock > 0 && producto.stock <= 5;

        col.innerHTML = `
            <div class="card h-100">
                <img src="${producto.imagen_url}" class="card-img-top" alt="${producto.nombre_producto}">
                <div class="card-body">
                    <h5 class="card-title">${producto.nombre_producto}</h5>
                    <p class="card-text">${producto.descripcion}</p>
                    <p class="fw-bold">$${producto.precio}</p>
                    ${sinStock ?
                '<button class="btn btn-secondary w-100" disabled>Agotado</button>' :
                `<button class="btn btn-primary add-to-cart w-100" 
                            data-id="${producto.id_producto}" 
                            data-nombre="${producto.nombre_producto}" 
                            data-precio="${producto.precio}"
                            data-img="${producto.imagen_url}"
                            data-stock="${producto.stock}">
                            Agregar al carrito
                        </button>`
            }
                </div>
            </div>
        `;

        contenedor.appendChild(col);
    });

    asignarEventosCarrito();
}

// CARRITO
let carritoActual = [];

function asignarEventosCarrito() {
    document.querySelectorAll(".add-to-cart").forEach(btn => {
        btn.addEventListener("click", () => {
            const id_producto = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const img = btn.getAttribute('data-img');
            const precio = Number(btn.getAttribute('data-precio'));
            const stock = parseInt(btn.getAttribute('data-stock'));

            const item = carritoActual.find(i => i.id_producto === id_producto);

            if (item) {
                if (item.cantidad >= stock) {
                    alert(`Stock máximo: ${stock}`);
                    return;
                }
                item.cantidad += 1;
            } else {
                carritoActual.push({
                    id: crypto.randomUUID(),
                    id_producto,
                    nombre,
                    precio,
                    cantidad: 1,
                    img,
                    stock
                });
            }

            renderizarCarrito();
            new bootstrap.Offcanvas(document.getElementById('carrito')).show();
        });
    });
}

function renderizarCarrito() {
    const cartItemsEl = document.getElementById("cart-items");
    const cartTotalEl = document.getElementById("cart-total");

    if (!cartItemsEl || !cartTotalEl) return;

    cartItemsEl.innerHTML = "";
    let total = 0;

    carritoActual.forEach((item, index) => {
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";

        li.innerHTML = `
            <div class="d-flex align-items-center">
                <img src="${item.img}" style="width:50px;height:50px" class="me-2">
                <div>
                    <div>${item.nombre}</div>
                    <div class="fw-bold">$${item.precio * item.cantidad}</div>
                </div>
            </div>
            <div class="d-flex flex-column align-items-end">
                <button class="btn btn-sm btn-secondary mb-1 btn-plus">+</button>
                <span class="mb-1">x${item.cantidad}</span>
                <button class="btn btn-sm btn-secondary mb-1 btn-minus">-</button>
                <button class="btn btn-sm btn-danger btn-remove">X</button>
            </div>
        `;

        li.querySelector('.btn-plus').addEventListener('click', () => {
            if (item.cantidad >= item.stock) {
                alert(`Stock máximo: ${item.stock}`);
                return;
            }
            item.cantidad++;
            renderizarCarrito();
        });

        li.querySelector('.btn-minus').addEventListener('click', () => {
            if (item.cantidad > 1) {
                item.cantidad--;
            } else {
                carritoActual.splice(index, 1);
            }
            renderizarCarrito();
        });

        li.querySelector('.btn-remove').addEventListener('click', () => {
            carritoActual.splice(index, 1);
            renderizarCarrito();
        });

        cartItemsEl.appendChild(li);
        total += item.precio * item.cantidad;
    });

    cartTotalEl.textContent = total;
}

// FINALIZAR COMPRA
function finalizarCompra() {
    console.log('🔵 Intentando finalizar compra...');
    console.log('Usuario actual:', window.usuarioActual);

    if (carritoActual.length === 0) {
        alert("Tu carrito está vacío.");
        return;
    }

    // VERIFICAR LOGIN
    if (!window.usuarioActual || !window.usuarioActual.id) {
        console.log('❌ No hay usuario logueado - BLOQUEADO');
        alert('⛔ Debes iniciar sesión para realizar una compra.\n\n¿Deseas ir a la página de inicio de sesión?');

        if (confirm('Haz clic en Aceptar para ir al login')) {
            window.location.href = 'login.html';
        }

        return;
    }

    console.log('✅ Usuario logueado, continuando...');

    document.getElementById("dni").value = "";
    document.getElementById("codigo").value = "";
    document.getElementById("formularioPago").style.display = "none";
    document.querySelectorAll('input[name="metodoPago"]').forEach(el => el.checked = false);

    new bootstrap.Modal(document.getElementById("metodosPagoModal")).show();
}

// CONFIRMAR PAGO
function confirmarPago() {
    console.log('🔵 Confirmando pago...');

    const metodo = document.querySelector('input[name="metodoPago"]:checked');
    const dni = document.getElementById("dni").value.trim();