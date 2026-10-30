<?php 
 
namespace App\Controllers; 
 
use App\Core\Controller; 
use App\Models\Producto; 
 
/** 
 * Controlador para la gestión de Productos (CRUD) 
 */ 
class ProductoController extends Controller 
{ 
    private Producto $productoModel; 
 
    public function __construct() 
    { 
        // 1. Proteger todas las rutas: solo usuarios autenticados 
        $this->requireAuth(); 
 
        // 2. Instanciar el modelo de Productos
        $this->productoModel = new Producto();
    }

    /**
     * Listado principal de productos
     * GET /productos
     */
    public function index(): void
    {
        $productos = $this->productoModel->listarTodos();

        $this->render('productos/index', [
            'productos' => $productos
        ], 'Gestión de Productos');
    }

    /**
     * Muestra el formulario para registrar un nuevo producto
     * GET /productos/crear
     */
    public function crear(): void
    {
        $this->render('productos/crear', [], 'Registrar Nuevo Producto');
    }

    /**
     * Procesa y guarda un nuevo producto en la base de datos
     * POST /productos/guardar
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('productos');
        }

        // Validación de campos obligatorios
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock  = $_POST['stock'] ?? '';

        if (empty($codigo) || empty($nombre) || $precio === '' || $stock === '') {
            $this->setFlash('danger', 'Los campos Código, Nombre, Precio y Stock son obligatorios.');
            redirect('productos/crear');
        }

        // Validar si el código ya está registrado
        if ($this->productoModel->existeCodigo($codigo)) {
            $this->setFlash('warning', 'El código "' . htmlspecialchars($codigo) . '" ya está en uso por otro producto.');
            redirect('productos/crear');
        }

        $exito = $this->productoModel->crear($_POST);

        if ($exito) {
            $this->setFlash('success', '¡Producto registrado exitosamente!');
            } else {
            $this->setFlash('danger', 'Ocurrió un error al registrar el producto en la base de datos.');
        }

        redirect('productos');
    }

    /**
     * Muestra el formulario de edición de un producto
     * GET /productos/editar/{id}
     */
    public function editar(?string $id = null): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->setFlash('warning', 'Identificador de producto no válido.');
            redirect('productos');
        }

        $producto = $this->productoModel->obtenerPorId($id);

        if (!$producto) {
            $this->setFlash('danger', 'El producto solicitado no existe.');
            redirect('productos');
        }

        $this->render('productos/editar', [
            'producto' => $producto
        ], 'Editar Producto #' . $producto['id']);
    }

    /**
     * Procesa la actualización de un producto
     * POST /productos/actualizar
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('productos');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('danger', 'Identificador de producto no válido.');
            redirect('productos');
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');

        if (empty($codigo) || empty($nombre)) {
            $this->setFlash('danger', 'El Código y el Nombre son obligatorios.');
            redirect('productos/editar/' . $id);
        }

        // Validar que el código no lo tenga otro producto distinto
        if ($this->productoModel->existeCodigo($codigo, $id)) {
            $this->setFlash('warning', 'El código "' . htmlspecialchars($codigo) . '" ya pertenece a otro producto registrado.');
            redirect('productos/editar/' . $id);
        }

        $exito = $this->productoModel->actualizar($id, $_POST);

        if ($exito) {
            $this->setFlash('success', '¡Producto actualizado exitosamente!');
        } else {
            $this->setFlash('danger', 'No se pudo actualizar el producto.');
        }

        redirect('productos');
    }

    /**
     * Elimina un producto por su ID
     * GET /productos/eliminar/{id}
     */
    public function eliminar(?string $id = null): void
    {
        $id = (int)$id;
        if ($id > 0) {
            $exito = $this->productoModel->eliminar($id);
            if ($exito) {
                $this->setFlash('success', '¡Producto eliminado correctamente!');
            } else {
                $this->setFlash('danger', 'No se pudo eliminar el producto.');
            }
        }

        redirect('productos');
    }
}


