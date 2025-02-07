<?php
// Ruta de la carpeta donde se guardarán las imágenes.
$datos_dir = __DIR__ . '/datos';

// Si la carpeta no existe, se crea.
if (!is_dir($datos_dir)) {
    mkdir($datos_dir, 0777, true);
}

// Variables para los campos del formulario.
$old_identificacion = "";
$identificacion   = "";
$nombre           = "";
$apellido         = "";
$fechaNacimiento  = "";
$profesion        = "";
$foto_file        = ""; // Nombre del archivo de foto actual (si existe)
$error            = "";
$message          = "";

// ---------------------------
// Eliminar un registro (Delete)
// ---------------------------
if (isset($_GET['accion']) && $_GET['accion'] === 'delete' && isset($_GET['identificacion'])) {
    $id = $_GET['identificacion'];
    $json_file = $datos_dir . '/record_' . $id . '.json';
    if (file_exists($json_file)) {
        // Leer el registro para saber si tiene foto asociada.
        $record = json_decode(file_get_contents($json_file), true);
        if (!empty($record['foto']) && file_exists($datos_dir . '/' . $record['foto'])) {
            unlink($datos_dir . '/' . $record['foto']); // Eliminar archivo de imagen.
        }
        unlink($json_file); // Eliminar el archivo JSON.
        header("Location: index.php");
        exit;
    } else {
        $error = "Registro no encontrado para eliminar.";
    }
}

// ---------------------------
// Editar un registro (Read para edición)
// ---------------------------
if (isset($_GET['accion']) && $_GET['accion'] === 'edit' && isset($_GET['identificacion'])) {
    $id = $_GET['identificacion'];
    $json_file = $datos_dir . '/record_' . $id . '.json';
    if (file_exists($json_file)) {
        $record = json_decode(file_get_contents($json_file), true);
        $old_identificacion = $record['identificacion'];
        $identificacion   = $record['identificacion'];
        $nombre           = $record['nombre'];
        $apellido         = $record['apellido'];
        $fechaNacimiento  = $record['fechaNacimiento'];
        $profesion        = $record['profesion'];
        $foto_file        = $record['foto']; // Puede estar vacío si no se subió imagen.
    } else {
        $error = "Registro no encontrado para editar.";
    }
}

// ---------------------------
// Procesar formulario (Create / Update)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $old_identificacion = isset($_POST['old_identificacion']) ? trim($_POST['old_identificacion']) : '';
    $identificacion   = isset($_POST['identificacion']) ? trim($_POST['identificacion']) : '';
    $nombre           = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido         = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $fechaNacimiento  = isset($_POST['fechaNacimiento']) ? trim($_POST['fechaNacimiento']) : '';
    $profesion        = isset($_POST['profesion']) ? trim($_POST['profesion']) : '';
    
    // Validar que los campos obligatorios no estén vacíos.
    if ($identificacion == "" || $nombre == "" || $apellido == "" || $fechaNacimiento == "" || $profesion == "") {
        $error = "Todos los campos (excepto la foto) son obligatorios.";
    }
    
    // Procesar la imagen si se ha seleccionado un archivo.
    $new_photo_filename = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['foto']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            // El nombre de la foto se crea en función de la identificación.
            $new_photo_filename = 'img_' . $identificacion . '.' . $ext;
            $destination = $datos_dir . '/' . $new_photo_filename;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                $error = "Error al subir la foto.";
            }
        } else {
            $error = "Tipo de archivo no permitido. Solo se aceptan JPEG, PNG y GIF.";
        }
    } else {
        // Si no se carga una nueva foto y se está editando, se conserva la foto anterior.
        if ($old_identificacion != "") {
            $json_file = $datos_dir . '/record_' . $old_identificacion . '.json';
            if (file_exists($json_file)) {
                $record = json_decode(file_get_contents($json_file), true);
                $new_photo_filename = $record['foto'];
            }
        }
    }
    
    // Si no hubo errores, se guarda (crea o actualiza) el registro.
    if ($error == "") {
        // Si se está actualizando y se ha subido una nueva foto, se elimina la foto antigua (si es distinta).
        if ($old_identificacion != "" && $old_identificacion === $identificacion && isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $old_json_file = $datos_dir . '/record_' . $old_identificacion . '.json';
            if (file_exists($old_json_file)) {
                $old_record = json_decode(file_get_contents($old_json_file), true);
                if (!empty($old_record['foto']) && $old_record['foto'] != $new_photo_filename) {
                    $old_photo_path = $datos_dir . '/' . $old_record['foto'];
                    if (file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                }
            }
        }
        
        // Si se está actualizando y se modificó la identificación, se elimina el archivo anterior.
        if ($old_identificacion != "" && $old_identificacion !== $identificacion) {
            $old_json_file = $datos_dir . '/record_' . $old_identificacion . '.json';
            if (file_exists($old_json_file)) {
                unlink($old_json_file);
            }
        }
        
        // Preparar el registro.
        $record = [
            'identificacion'  => $identificacion,
            'nombre'          => $nombre,
            'apellido'        => $apellido,
            'fechaNacimiento' => $fechaNacimiento,
            'foto'            => $new_photo_filename, // Puede estar vacío.
            'profesion'       => $profesion
        ];
        
        // Guardar el registro en un archivo JSON. El nombre del archivo se forma con la identificación.
        $json_file = $datos_dir . '/record_' . $identificacion . '.json';
        file_put_contents($json_file, json_encode($record));
        
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Personajes - Mundo Barbie</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome (para íconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --barbie-pink: #FF69B4;
            --barbie-light: #FFF0F5;
            --barbie-dark: #c71585;
        }
        body {
            background-color: var(--barbie-light);
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: var(--barbie-pink);
        }
        .navbar-brand, .nav-link {
            color: #fff !important;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: var(--barbie-pink);
            border-color: var(--barbie-pink);
        }
        .btn-primary:hover {
            background-color: var(--barbie-dark);
            border-color: var(--barbie-dark);
        }
        .table thead {
            background-color: var(--barbie-pink);
            color: #fff;
        }
        .form-label {
            font-weight: 600;
        }
        .custom-container {
            max-width: 1200px;
            margin: 20px auto;
        }
        /* Separador vertical entre columnas */
        .vertical-divider {
            border-left: 2px solid var(--barbie-pink);
            height: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar con enlaces actualizados -->
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fa-solid fa-star"></i> Mundo Barbie</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
             <li class="nav-item">
                <a class="nav-link" href="index.php">Inicio</a>
             </li>
             <li class="nav-item">
                <a class="nav-link" href="profesiones.php">Profesiones y Salarios</a>
             </li>
             <li class="nav-item">
                <a class="nav-link" href="estadisticas.php">Estadistica</a>
             </li>
          </ul>
        </div>
      </div>
    </nav>
    
    <div class="custom-container">
        <h1 class="mt-4 mb-4 text-center">Registro de Personajes</h1>
        
        <?php if ($error != ""): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($message != ""): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Columna izquierda: Formulario de Registro -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h5 class="card-title mb-0"><i class="fa-solid fa-user-plus"></i> Registrar Persona</h5>
                    </div>
                    <div class="card-body">
                        <form action="index.php" method="post" enctype="multipart/form-data">
                            <!-- Campo oculto para distinguir si es edición -->
                            <input type="hidden" name="old_identificacion" value="<?= htmlspecialchars($old_identificacion) ?>">
                            
                            <div class="mb-3">
                                <label for="identificacion" class="form-label">Identificación:</label>
                                <input type="text" id="identificacion" name="identificacion" class="form-control" value="<?= htmlspecialchars($identificacion) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($nombre) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido:</label>
                                <input type="text" id="apellido" name="apellido" class="form-control" value="<?= htmlspecialchars($apellido) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento:</label>
                                <input type="date" id="fechaNacimiento" name="fechaNacimiento" class="form-control" value="<?= htmlspecialchars($fechaNacimiento) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto del personaje:</label>
                                <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
                                <?php if ($foto_file != ""): ?>
                                    <div class="mt-2">
                                        <p class="mb-1">Foto actual:</p>
                                        <img src="datos/<?= htmlspecialchars($foto_file) ?>" alt="Foto de <?= htmlspecialchars($nombre) ?>" class="img-thumbnail" style="width: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profesion" class="form-label">Profesión o Rol en el Mundo Barbie:</label>
                                <input type="text" id="profesion" name="profesion" class="form-control" value="<?= htmlspecialchars($profesion) ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Separador vertical -->
            <div class="col-md-1 d-none d-md-flex justify-content-center">
                <div class="vertical-divider"></div>
            </div>
            
            <!-- Columna derecha: Listado de Personas -->
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h5 class="card-title mb-0"><i class="fa-solid fa-list"></i> Listado de Personas</h5>
                    </div>
                    <!-- Se establece un contenedor scrollable para el listado -->
                    <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Fecha Nac.</th>
                                        <th>Foto</th>
                                        <th>Profesión</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Se leen todos los archivos JSON que cumplen el patrón de registro.
                                    foreach (glob($datos_dir . '/record_*.json') as $file) {
                                        $record = json_decode(file_get_contents($file), true);
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($record['identificacion']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['nombre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['apellido']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['fechaNacimiento']) . "</td>";
                                        echo "<td>";
                                        if (!empty($record['foto']) && file_exists($datos_dir . '/' . $record['foto'])) {
                                            echo '<img src="datos/' . htmlspecialchars($record['foto']) . '" alt="Foto de ' . htmlspecialchars($record['nombre']) . '" class="img-thumbnail" style="width: 60px;">';
                                        }
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($record['profesion']) . "</td>";
                                        echo "<td>";
                                        echo '<a href="index.php?accion=edit&identificacion=' . urlencode($record['identificacion']) . '" class="btn btn-sm btn-warning me-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                                        echo '<a href="index.php?accion=delete&identificacion=' . urlencode($record['identificacion']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de eliminar este personaje?\')"><i class="fa-solid fa-trash"></i></a>';
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>  <!-- Fin row -->
    </div>
    
    <!-- Bootstrap JS  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
