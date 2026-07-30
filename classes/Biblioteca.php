<?php
require_once 'Database.php';
require_once 'Libro.php';
require_once 'Usuario.php';
require_once 'Prestamo.php';

class Biblioteca {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function agregarLibro(Libro $libro) {
        if ($libro->getIsbn()) {
            $sqlBuscar = "SELECT id, activo FROM libros WHERE isbn = :isbn";
            $stmtBuscar = $this->conn->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':isbn', $libro->getIsbn());
            $stmtBuscar->execute();
            $existente = $stmtBuscar->fetch();

            if ($existente) {
                if ($existente['activo'] == 1) {
                    return false;
                }
                $sqlReactivar = "UPDATE libros
                                  SET titulo = :titulo, autor = :autor, cantidad = :cantidad, activo = 1
                                  WHERE id = :id";
                $stmtReactivar = $this->conn->prepare($sqlReactivar);
                $stmtReactivar->bindValue(':titulo', $libro->getTitulo());
                $stmtReactivar->bindValue(':autor', $libro->getAutor());
                $stmtReactivar->bindValue(':cantidad', $libro->getCantidad());
                $stmtReactivar->bindValue(':id', $existente['id'], PDO::PARAM_INT);
                return $stmtReactivar->execute();
            }
        }

        $sql = "INSERT INTO libros (titulo, autor, isbn, cantidad) VALUES (:titulo, :autor, :isbn, :cantidad)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':titulo', $libro->getTitulo());
        $stmt->bindValue(':autor', $libro->getAutor());
        $stmt->bindValue(':isbn', $libro->getIsbn());
        $stmt->bindValue(':cantidad', $libro->getCantidad());
        return $stmt->execute();
    }

    public function editarLibro($id, $nuevosDatos) {
        $sql = "UPDATE libros SET titulo = :titulo, autor = :autor, isbn = :isbn, cantidad = :cantidad WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':titulo', $nuevosDatos['titulo']);
        $stmt->bindValue(':autor', $nuevosDatos['autor']);
        $stmt->bindValue(':isbn', $nuevosDatos['isbn']);
        $stmt->bindValue(':cantidad', $nuevosDatos['cantidad']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarLibro($id) {
        $sql = "UPDATE libros SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerLibros() {
        $sql = "SELECT * FROM libros WHERE activo = 1 ORDER BY titulo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarLibro($id) {
        $sql = "SELECT * FROM libros WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function agregarUsuario(Usuario $usuario) {
        $sqlBuscar = "SELECT id, activo FROM usuarios WHERE email = :email";
        $stmtBuscar = $this->conn->prepare($sqlBuscar);
        $stmtBuscar->bindValue(':email', $usuario->getEmail());
        $stmtBuscar->execute();
        $existente = $stmtBuscar->fetch();

        if ($existente) {
            if ($existente['activo'] == 1) {
                return false;
            }
            $sqlReactivar = "UPDATE usuarios
                              SET nombre = :nombre, telefono = :telefono, activo = 1
                              WHERE id = :id";
            $stmtReactivar = $this->conn->prepare($sqlReactivar);
            $stmtReactivar->bindValue(':nombre', $usuario->getNombre());
            $stmtReactivar->bindValue(':telefono', $usuario->getTelefono());
            $stmtReactivar->bindValue(':id', $existente['id'], PDO::PARAM_INT);
            return $stmtReactivar->execute();
        }

        $sql = "INSERT INTO usuarios (nombre, email, telefono) VALUES (:nombre, :email, :telefono)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nombre', $usuario->getNombre());
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':telefono', $usuario->getTelefono());
        return $stmt->execute();
    }

    public function editarUsuario($id, $nuevosDatos) {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, telefono = :telefono WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nombre', $nuevosDatos['nombre']);
        $stmt->bindValue(':email', $nuevosDatos['email']);
        $stmt->bindValue(':telefono', $nuevosDatos['telefono']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarUsuario($id) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerUsuarios() {
        $sql = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarUsuario($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function prestarLibro($libro_id, $usuario_id) {
        $libro = $this->buscarLibro($libro_id);
        if (!$libro || $libro['cantidad'] <= 0) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $sqlInsert = "INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, estado)
                          VALUES (:libro_id, :usuario_id, :fecha_prestamo, 'activo')";
            $stmt = $this->conn->prepare($sqlInsert);
            $stmt->bindValue(':libro_id', $libro_id, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_prestamo', date('Y-m-d'));
            $stmt->execute();

            $sqlUpdate = "UPDATE libros SET cantidad = cantidad - 1 WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':id', $libro_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function devolverLibro($prestamo_id) {
        $sqlPrestamo = "SELECT * FROM prestamos WHERE id = :id";
        $stmt = $this->conn->prepare($sqlPrestamo);
        $stmt->bindValue(':id', $prestamo_id, PDO::PARAM_INT);
        $stmt->execute();
        $prestamo = $stmt->fetch();

        if (!$prestamo || $prestamo['estado'] === 'devuelto') {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $sqlUpdate = "UPDATE prestamos SET fecha_devolucion = :fecha, estado = 'devuelto' WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':fecha', date('Y-m-d'));
            $stmtUpdate->bindValue(':id', $prestamo_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $sqlLibro = "UPDATE libros SET cantidad = cantidad + 1 WHERE id = :id";
            $stmtLibro = $this->conn->prepare($sqlLibro);
            $stmtLibro->bindValue(':id', $prestamo['libro_id'], PDO::PARAM_INT);
            $stmtLibro->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function obtenerPrestamosActivos() {
        $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_devolucion, p.estado,
                       l.titulo AS libro_titulo, u.nombre AS usuario_nombre
                FROM prestamos p
                JOIN libros l ON p.libro_id = l.id
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.estado = 'activo'
                ORDER BY p.fecha_prestamo DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTodosPrestamos() {
        $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_devolucion, p.estado,
                       l.titulo AS libro_titulo, u.nombre AS usuario_nombre
                FROM prestamos p
                JOIN libros l ON p.libro_id = l.id
                JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.fecha_prestamo DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
