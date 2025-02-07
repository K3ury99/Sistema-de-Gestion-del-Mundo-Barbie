<?php
// Ruta de la carpeta donde se almacenan los registros
$datos_dir = __DIR__ . '/datos';

// Si la carpeta no existe, se crea.
if (!is_dir($datos_dir)) {
    mkdir($datos_dir, 0777, true);
}

// Variables para los campos del formulario.
$old_id    = "";
$record_id = "";
$character = "";
$profesion = "";
$categoria = "";
$nivel     = "";
$salario   = "";
$error     = "";
$message   = "";

// ---------------------------
// Eliminar un registro de profesión (Delete)
// ---------------------------
if (isset($_GET['accion']) && $_GET['accion'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $json_file = $datos_dir . '/profession_record_' . $id . '.json';
    if (file_exists($json_file)) {
        unlink($json_file);
        header("Location: profesiones.php");
        exit;
    } else {
        $error = "Registro de profesión no encontrado para eliminar.";
    }
}

// ---------------------------
// Editar un registro de profesión (Read para edición)
// ---------------------------
if (isset($_GET['accion']) && $_GET['accion'] === 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $json_file = $datos_dir . '/profession_record_' . $id . '.json';
    if (file_exists($json_file)) {
        $record = json_decode(file_get_contents($json_file), true);
        $old_id    = $record['id'];
        $record_id = $record['id'];
        $character = $record['character'];
        $profesion = $record['profesion'];
        $categoria = $record['categoria'];
        $nivel     = $record['nivel'];
        $salario   = $record['salario'];
    } else {
        $error = "Registro de profesión no encontrado para editar.";
    }
}

// ---------------------------
// Procesar formulario (Create / Update)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos enviados
    $old_id    = isset($_POST['old_id']) ? trim($_POST['old_id']) : "";
    $character = isset($_POST['character']) ? trim($_POST['character']) : "";
    $profesion = isset($_POST['profesion']) ? trim($_POST['profesion']) : "";
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : "";
    $nivel     = isset($_POST['nivel']) ? trim($_POST['nivel']) : "";
    $salario   = isset($_POST['salario']) ? trim($_POST['salario']) : "";
    
    // Validar que todos los campos estén completos.
    if ($character == "" || $profesion == "" || $categoria == "" || $nivel == "" || $salario == "") {
        $error = "Todos los campos son obligatorios.";
    }
    
    // Si no hubo errores, se crea o actualiza el registro.
    if ($error == "") {
        // Si es un nuevo registro, se genera un id único; si es edición se conserva el mismo.
        if ($old_id == "") {
            $record_id = uniqid();
        } else {
            $record_id = $old_id;
        }
        
        // Preparar el registro.
        $record = [
            'id'        => $record_id,
            'character' => $character,
            'profesion' => $profesion,
            'categoria' => $categoria,
            'nivel'     => $nivel,
            'salario'   => $salario
        ];
        
        // Guardar el registro en un archivo JSON.
        $json_file = $datos_dir . '/profession_record_' . $record_id . '.json';
        file_put_contents($json_file, json_encode($record));
        
        header("Location: profesiones.php");
        exit;
    }
}

// ---------------------------
// Generar listado de Personajes para el select
// Se leen todos los archivos de personajes y se crea un array.
$characters_list = [];
foreach (glob($datos_dir . '/record_*.json') as $char_file) {
    $char_record = json_decode(file_get_contents($char_file), true);
    // Se utiliza el campo 'identificacion' como clave y se muestra nombre y apellido.
    $characters_list[$char_record['identificacion']] = $char_record['nombre'] . " " . $char_record['apellido'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Profesiones y Salarios - Mundo Barbie</title>
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
        /* Separador vertical entre columnas con márgenes reducidos */
        .vertical-divider {
            border-left: 2px solid var(--barbie-pink);
            height: 100%;
            margin-left: 0;
            margin-right: 0;
        }
    </style>
</head>
<body>
    <!-- Navbar con enlaces -->
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
        <h1 class="mt-4 mb-4 text-center">Registro de Profesiones y Salarios</h1>
        
        <?php if ($error != ""): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Columna izquierda: Formulario de Registro (NO MODIFICAR) -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h5 class="card-title mb-0"><i class="fa-solid fa-plus"></i> Registrar Profesión</h5>
                    </div>
                    <div class="card-body">
                        <form action="profesiones.php" method="post">
                            <!-- Campo oculto para identificar si se está editando -->
                            <input type="hidden" name="old_id" value="<?= htmlspecialchars($old_id) ?>">
                            
                            <div class="mb-3">
                                <label for="character" class="form-label">Personaje:</label>
                                <select name="character" id="character" class="form-select" required>
                                    <option value="">Seleccione un personaje</option>
                                    <?php foreach ($characters_list as $id => $nombreCompleto): ?>
                                        <option value="<?= htmlspecialchars($id) ?>" <?= ($character == $id ? "selected" : "") ?>>
                                            <?= htmlspecialchars($nombreCompleto) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profesion" class="form-label">Nombre de la Profesión:</label>
                                <input type="text" id="profesion" name="profesion" class="form-control" value="<?= htmlspecialchars($profesion) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría:</label>
                                <select name="categoria" id="categoria" class="form-select" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php 
                                    $categorias = ["Ciencia", "Arte", "Deporte", "Entretenimiento", "Otro"];
                                    foreach ($categorias as $cat):
                                    ?>
                                        <option value="<?= $cat ?>" <?= ($categoria == $cat ? "selected" : "") ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nivel" class="form-label">Nivel de Experiencia:</label>
                                <select name="nivel" id="nivel" class="form-select" required>
                                    <option value="">Seleccione un nivel</option>
                                    <?php 
                                    $niveles = ["Principiante", "Intermedio", "Avanzado"];
                                    foreach ($niveles as $niv):
                                    ?>
                                        <option value="<?= $niv ?>" <?= ($nivel == $niv ? "selected" : "") ?>><?= $niv ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="salario" class="form-label">Salario Mensual Estimado ($USD):</label>
                                <input type="number" step="0.01" id="salario" name="salario" class="form-control" value="<?= htmlspecialchars($salario) ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Separador vertical (se mantiene sin modificaciones) -->
            <div class="col-md-1 d-none d-md-flex justify-content-center" style="padding-left: 0; padding-right: 0;">
                <div class="vertical-divider" style="margin-left: 0; margin-right: 0;"></div>
            </div>
            
            <!-- Columna derecha: Listado de Profesiones (se muestra completo sin scroll) -->
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h5 class="card-title mb-0"><i class="fa-solid fa-list"></i> Listado de Profesiones</h5>
                    </div>
                    <!-- Se elimina el estilo que hacía scrollable el listado y se agrega padding-right para más espacio -->
                    <div class="card-body p-0" style="padding-right: 20px;">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Personaje</th>
                                        <th>Profesión</th>
                                        <th>Categoría</th>
                                        <th>Nivel</th>
                                        <th>Salario ($USD)</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Se leen todos los archivos JSON de profesiones (prefijo "profession_record_")
                                    foreach (glob($datos_dir . '/profession_record_*.json') as $file) {
                                        $record = json_decode(file_get_contents($file), true);
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($record['id']) . "</td>";
                                        // Se muestra el nombre completo del personaje (si existe en el listado)
                                        $charName = $record['character'];
                                        if (isset($characters_list[$record['character']])) {
                                            $charName = $characters_list[$record['character']];
                                        }
                                        echo "<td>" . htmlspecialchars($charName) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['profesion']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['categoria']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['nivel']) . "</td>";
                                        echo "<td>" . htmlspecialchars($record['salario']) . "</td>";
                                        echo "<td>";
                                        echo '<a href="profesiones.php?accion=edit&id=' . urlencode($record['id']) . '" class="btn btn-sm btn-warning me-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                                        echo '<a href="profesiones.php?accion=delete&id=' . urlencode($record['id']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de eliminar este registro?\')"><i class="fa-solid fa-trash"></i></a>';
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
    
    <!-- Bootstrap JS (opcional, para funcionalidades interactivas) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
