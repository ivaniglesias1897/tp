Pasos para crear un proyecto Laravel 10:

1. Verifica que tienes instalado Composer:
   ```bash
   composer --version
   ```
   Si no lo tienes, descárgalo desde [getcomposer.org](https://getcomposer.org/).

2. Instala Laravel 10 de forma global (opcional):
   ```bash
   composer global require laravel/installer
   ```

3. Crea un nuevo proyecto Laravel 10:
   ```bash
   laravel new nombre-del-proyecto
   # o usando Composer directamente:
   composer create-project --prefer-dist laravel/laravel="^10.0" nombre-del-proyecto
   ```

4. Accede a la carpeta del proyecto:
   ```bash
   cd nombre-del-proyecto
   ```

5. Copia el archivo de entorno y genera la clave de la aplicación:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

6. Configura la conexión a la base de datos en el archivo `.env`.

7. Ejecuta las migraciones (opcional):
   ```bash
   php artisan migrate
   ```

8. Inicia el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

9. Instala InfyOm Laravel Generator:
   ```bash
   composer require infyomlabs/laravel-generator --dev
   ```
   9.1. Instala Doctrine DBAL (requerido para algunas migraciones avanzadas):
   ```bash
      composer require doctrine/dbal
   ```

10. Publica los archivos de configuración y assets:
    ```bash
    php artisan vendor:publish --provider="InfyOm\Generator\InfyOmGeneratorServiceProvider"
    php artisan vendor:publish --provider="InfyOm\AdminLTETemplates\AdminLTETemplatesServiceProvider"
    ```

11. Instala AdminLTE 3 para InfyOm:
    ```bash
    composer require infyomlabs/adminlte-templates --dev
    ```

12. Instala dependencias front-end:
    ```bash
    npm install
    npm run dev
    ```

13. Configura InfyOm en el archivo

14. Instala Laravel UI:
    ```bash
    composer require laravel/ui
    ```

15. Genera el scaffolding de autenticación con AdminLTE:
    ```bash
    php artisan ui adminlte --auth
    ```

16. Compila los assets nuevamente:
    ```bash
    npm install
    npm run dev
    ```
17. Crear controladores con artisan
    Para crear un controlador en Laravel usando Artisan, ejecuta el siguiente comando:
    ```bash
    php artisan make:controller CargoController
    ```

    **Explicación:**
    - `php artisan make:controller`: Es el comando de Artisan para generar un nuevo controlador en la carpeta `app/Http/Controllers`.
    - `CargoController`: Es el nombre del controlador que se va a crear. Por convención, el nombre debe terminar en `Controller` y estar en singular o plural según el contexto del recurso.

    Este comando generará un archivo llamado `CargoController.php` en `app/Http/Controllers/`, donde podrás definir la lógica para manejar las solicitudes HTTP relacionadas con el recurso `Cargo` (por ejemplo: listar, crear, editar, eliminar cargos).
    
18. Generar Vista HTML con blade
    Para generar automáticamente las vistas Blade (HTML) de un modelo usando InfyOm, ejecuta el siguiente comando:
    ```bash
    php artisan infyom.scaffold:views Cargos --fromTable --table=cargos
    ```

    **Explicación de cada parte del comando:**
    - `php artisan infyom.scaffold:views`: Llama al generador de vistas de InfyOm para crear archivos Blade listos para usar en Laravel.
    - `Cargos`: Es el nombre del modelo para el cual se generarán las vistas. Debe coincidir con el modelo y la tabla que tienes en tu base de datos.
    - `--fromTable`: Indica que las vistas deben generarse a partir de la estructura real de la tabla en la base de datos (no solo del modelo), lo que permite que los campos y formularios se creen automáticamente según las columnas de la tabla.
    - `--table=cargos`: Especifica el nombre exacto de la tabla en la base de datos de la cual se tomarán los campos para las vistas.

    **¿Qué genera este comando?**
    - Archivos Blade para las operaciones CRUD (crear, listar, editar, ver) del modelo especificado.
    - Formularios y tablas adaptados a la estructura de la tabla `cargos`.
    - Las vistas se ubicarán en `resources/views/cargos/`.

    > **Nota:** Asegúrate de que la tabla `cargos` exista en la base de datos antes de ejecutar este comando.
