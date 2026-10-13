# Plantilla Base Educativa: Sistema de Reserva (PHP + POO + MySQL + AdminLTE v4)

Esta plantilla está diseñada como material educativo para estudiantes, facilitando la comprensión y aplicación práctica de la **Programación Orientada a Objetos (POO)** y la arquitectura de software **MVC (Modelo - Vista - Controlador)** en **PHP 8+** con base de datos **MySQL** y la plantilla administrativa **AdminLTE v4** (basada en Bootstrap 5).

---

## 📁 Estructura del Proyecto

```text
SistemaReserva/
│
├── app/                              # Núcleo de la aplicación
│   ├── Config/                       # Configuraciones globales
│   │   ├── App.php                   # Constantes de app y detección dinámica de URL
│   │   └── Database.php              # Conexión Singleton a MySQL con PDO
│   │
│   ├── Core/                         # Clases base del Framework educativo
│   │   ├── Autoloader.php            # Carga automática PSR-4 de clases
│   │   ├── Controller.php            # Controlador base (renderizado con AdminLTE, flash alerts)
│   │   ├── Helpers.php               # Funciones globales (base_url, asset, url, redirect)
│   │   ├── Model.php                 # Modelo base con instancia PDO segura
│   │   └── Router.php                # Enrutador Front-Controller
│   │
│   ├── Controllers/                  # Controladores de la aplicación
│   │   ├── HomeController.php        # Controlador del Dashboard
│   │   └── ClienteController.php     # Controlador CRUD para Clientes
│   │
│   ├── Models/                       # Modelos de base de datos (POO + PDO)
│   │   └── Cliente.php               # Métodos CRUD seguros con sentencias preparadas
│   │
│   └── Views/                        # Vistas HTML / PHP organizadas
│       ├── layouts/                  # Partes modulares de AdminLTE
│       │   ├── header.php            # Etiquetas meta, fuentes y CSS
│       │   ├── navbar.php            # Barra superior con cambio de tema y perfil
│       │   ├── sidebar.php           # Menú lateral de navegación con enlaces
│       │   └── footer.php            # Pie de página y librerías JS
│       ├── home/                     # Vistas del Dashboard
│       │   └── index.php             # Métricas y tarjetas de bienvenida
│       └── clientes/                 # Vistas del módulo de clientes
│           ├── index.php             # Tabla listado con acciones
│           ├── crear.php             # Formulario de registro
│           └── editar.php            # Formulario de modificación
│
├── database/                         # Scripts de base de datos
│   └── database.sql                  # Creación de tablas y datos iniciales de prueba
│
├── public/                           # Directorio público accesible desde el navegador
│   ├── assets/                       # Archivos estáticos
│   │   ├── adminlte/                 # Distribución de AdminLTE v4 (css, js, imágenes)
│   │   ├── css/custom.css            # Estilos personalizados para los alumnos
│   │   └── js/custom.js              # Scripts personalizados para los alumnos
│   ├── uploads/                      # Carpeta para archivos subidos
│   ├── .htaccess                     # Enrutamiento de URLs amigables
│   └── index.php                     # Front Controller (Punto de entrada)
│
├── routes/                           # Documentación y catálogo de rutas
│   └── web.php                       # Mapa didáctico de rutas
│
├── .htaccess                         # Redirección automática a public/
└── README.md                         # Documentación del proyecto
```

---

## 🚀 Requisitos e Instalación

### 1. Requisitos
- **Laragon** (recomendado) o XAMPP con **PHP 8.1 o superior** y **MySQL**.

### 2. Configurar la Base de Datos
1. Inicia los servicios de **Laragon** (botón *Start All*).
2. Abre tu gestor de base de datos preferido (*HeidiSQL*, *phpMyAdmin* o línea de comandos).
3. Importa el archivo `database/database.sql`.
   - Se creará la base de datos `sistema_reserva` con la tabla `clientes` y registros de prueba.

### 3. Ejecutar el Proyecto
- Si usas el Virtual Host automático de Laragon:
  ```text
  http://sistemareserva.test
  ```
- Si accedes por la ruta tradicional de Apache:
  ```text
  http://localhost/SistemaReserva/public/
  ```
  *(El archivo `.htaccess` en la raíz también redirige automáticamente si ingresas a `http://localhost/SistemaReserva`)*.

---

## 🧠 Conceptos de POO Aplicados

1. **Encapsulamiento**: Propiedades privadas/protegidas en las clases con métodos públicos para interactuar con ellas.
2. **Herencia**: 
   - Todos los modelos heredan de `App\Core\Model` para compartir la conexión PDO.
   - Todos los controladores heredan de `App\Core\Controller` para reutilizar el renderizado del layout de AdminLTE y las funciones de redirección y alertas.
3. **Patrón Singleton**: En `App\Config\Database`, para garantizar una única conexión PDO abierta por petición HTTP.
4. **Sentencias Preparadas (PDO)**: Consultas parametrizadas (`$stmt->prepare(...)` y `$stmt->execute(...)`) para proteger la aplicación contra inyección SQL.

---

## 🛠️ ¿Cómo agregar un nuevo módulo? (Paso a Paso para Alumnos)

Supongamos que deseas crear el módulo **Habitaciones**:

### Paso 1: Crear la tabla en MySQL
Añade la tabla en `database/database.sql` y ejecútala en MySQL:
```sql
CREATE TABLE habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    estado ENUM('disponible', 'ocupada', 'mantenimiento') DEFAULT 'disponible'
);
```

### Paso 2: Crear el Modelo (`app/Models/Habitacion.php`)
```php
<?php
namespace App\Models;

use App\Core\Model;

class Habitacion extends Model
{
    public function listar(): array
    {
        $stmt = $this->db->query("SELECT * FROM habitaciones ORDER BY numero ASC");
        return $stmt->fetchAll();
    }
}
```

### Paso 3: Crear el Controlador (`app/Controllers/HabitacionController.php`)
```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Habitacion;

class HabitacionController extends Controller
{
    public function index(): void
    {
        $modelo = new Habitacion();
        $habitaciones = $modelo->listar();

        $this->render('habitaciones/index', [
            'habitaciones' => $habitaciones
        ], 'Gestión de Habitaciones');
    }
}
```

### Paso 4: Crear la Vista (`app/Views/habitaciones/index.php`)
Crea la carpeta `app/Views/habitaciones` y el archivo `index.php` con la tabla y componentes de AdminLTE.

### Paso 5: Agregar el enlace en el Menú Lateral (`app/Views/layouts/sidebar.php`)
Agrega el elemento de menú:
```html
<li class="nav-item">
    <a href="<?= url('habitaciones') ?>" class="nav-link">
        <i class="nav-icon bi bi-door-closed-fill"></i>
        <p>Habitaciones</p>
    </a>
</li>
```

---

## 🎨 Plantilla AdminLTE v4
- Los assets de AdminLTE v4 (CSS, JS e imágenes) se encuentran organizados y listos en `public/assets/adminlte/`.
- La plantilla cuenta con soporte nativo para **Modo Claro / Modo Oscuro**.
- Utiliza **Bootstrap 5** y **Bootstrap Icons**.
# Sistema de Ventas
