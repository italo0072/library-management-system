# Sistema de Gestión de Biblioteca

Mini-aplicación OOP en PHP para gestionar libros, usuarios y préstamos de una biblioteca.

## Estructura del proyecto

```
library-management-system/
├── biblioteca.sql
├── index.php
└── classes/
    ├── Database.php
    ├── Libro.php
    ├── Usuario.php
    ├── Prestamo.php
    └── Biblioteca.php
```

## Instalación y configuración

1. **Clona o copia este proyecto** dentro de la carpeta pública de tu servidor local
   (por ejemplo `htdocs` en XAMPP o `www` en Laragon).
   Comando para clonar
   ```
   git clone https://github.com/italo0072/library-management-system
   ```

3. **Levanta Apache y MySQL** desde el panel de XAMPP/Laragon.

4. **Crea la base de datos**:
   - Abre phpMyAdmin (`http://localhost/phpmyadmin`).
   - Ve a la pestaña **SQL** e importa/pega el contenido de `biblioteca.sql`, o usa
     "Importar" y selecciona el archivo directamente.
   - Esto creará la base de datos `biblioteca` con las tablas `libros`, `usuarios`
     y `prestamos`.

5. **Configura las credenciales de conexión** en `classes/Database.php` si tu
   configuración de MySQL no usa el usuario `root` sin contraseña:

   ```php
   private $host = 'localhost';
   private $db_name = 'biblioteca';
   private $username = 'root';
   private $password = '';
   ```

6. **Ejecuta el proyecto**:
   - Con XAMPP/Laragon: abre `http://localhost/library-management-system/index.php`
   

##  Funcionalidades implementadas

- **Libros**: crear, listar, editar y eliminar.
- **Usuarios**: crear, listar, editar y eliminar.
- **Préstamos**:
  - Registrar un préstamo (valida que haya stock disponible y disminuye la
    cantidad del libro).
  - Registrar una devolución (marca el préstamo como `devuelto`, guarda la
    fecha de devolución y repone el stock del libro).
  - Listado de préstamos activos.


