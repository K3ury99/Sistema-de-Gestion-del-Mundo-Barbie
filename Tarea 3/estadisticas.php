<?php
// Definir la ruta de la carpeta donde se almacenan los datos.
$datos_dir = __DIR__ . '/datos';

// ==========================
// CARGAR DATOS DE PERSONAJES
// ==========================
$characters = [];
foreach (glob($datos_dir . '/record_*.json') as $char_file) {
    $record = json_decode(file_get_contents($char_file), true);
    if ($record) {
        $characters[] = $record;
    }
}

// ==========================
// CARGAR DATOS DE PROFESIONES
// ==========================
$professions = [];
foreach (glob($datos_dir . '/profession_record_*.json') as $prof_file) {
    $record = json_decode(file_get_contents($prof_file), true);
    if ($record) {
        $professions[] = $record;
    }
}

// -------------------------
// 1. Cantidad total de personajes registrados
// -------------------------
$total_characters = count($characters);

// -------------------------
// 2. Cantidad total de profesiones registradas
// -------------------------
$total_professions = count($professions);

// -------------------------
// 3. Promedio de profesiones por personaje
// -------------------------
$avg_professions_per_character = $total_characters > 0 ? round($total_professions / $total_characters, 2) : 0;

// -------------------------
// 4. Edad promedio de los personajes
// -------------------------
$sum_ages = 0;
$count_ages = 0;
foreach ($characters as $char) {
    if (!empty($char['fechaNacimiento'])) {
        $birthDate = new DateTime($char['fechaNacimiento']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        $sum_ages += $age;
        $count_ages++;
    }
}
$avg_age = $count_ages > 0 ? round($sum_ages / $count_ages, 2) : 0;

// -------------------------
// 5. Distribución de personajes según su categoría de profesión
// -------------------------
$category_distribution = [];
foreach ($professions as $record) {
    $cat = $record['categoria'];
    if (!isset($category_distribution[$cat])) {
        $category_distribution[$cat] = 0;
    }
    $category_distribution[$cat]++;
}

// -------------------------
// 6. Nivel de experiencia más común y recuento para graficar
// -------------------------
$nivel_counts = [];
foreach ($professions as $record) {
    $nivel = $record['nivel'];
    if (!isset($nivel_counts[$nivel])) {
        $nivel_counts[$nivel] = 0;
    }
    $nivel_counts[$nivel]++;
}
$common_nivel = '';
$max_count = 0;
foreach ($nivel_counts as $nivel => $count) {
    if ($count > $max_count) {
        $max_count = $count;
        $common_nivel = $nivel;
    }
}

// -------------------------
// 7. Profesión con salario más alto y con menor salario
// -------------------------
$max_salary = null;
$min_salary = null;
$profession_max = '';
$profession_min = '';
foreach ($professions as $record) {
    $sal = floatval($record['salario']);
    if ($max_salary === null || $sal > $max_salary) {
        $max_salary = $sal;
        $profession_max = $record['profesion'];
    }
    if ($min_salary === null || $sal < $min_salary) {
        $min_salary = $sal;
        $profession_min = $record['profesion'];
    }
}

// -------------------------
// 8. Salario promedio global
// -------------------------
$total_salary = 0;
$count_salary = 0;
foreach ($professions as $record) {
    $total_salary += floatval($record['salario']);
    $count_salary++;
}
$avg_salary = $count_salary > 0 ? round($total_salary / $count_salary, 2) : 0;

// -------------------------
// 9. Personaje con el salario más alto
// -------------------------
$highest_salary_record = null;
foreach ($professions as $record) {
    if ($highest_salary_record === null || floatval($record['salario']) > floatval($highest_salary_record['salario'])) {
        $highest_salary_record = $record;
    }
}
$character_highest_salary = '';
if ($highest_salary_record) {
    $character_id = $highest_salary_record['character'];
    foreach ($characters as $char) {
        if ($char['identificacion'] == $character_id) {
            $character_highest_salary = $char['nombre'] . " " . $char['apellido'];
            break;
        }
    }
}

// -------------------------
// 10. Salario promedio por categoría
// -------------------------
$salary_by_category = [];
$count_by_category = [];
foreach ($professions as $record) {
    $cat = $record['categoria'];
    $sal = floatval($record['salario']);
    if (!isset($salary_by_category[$cat])) {
        $salary_by_category[$cat] = 0;
        $count_by_category[$cat] = 0;
    }
    $salary_by_category[$cat] += $sal;
    $count_by_category[$cat]++;
}
$avg_salary_by_category = [];
foreach ($salary_by_category as $cat => $total) {
    $avg_salary_by_category[$cat] = round($total / $count_by_category[$cat], 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Estadísticas del Mundo Barbie</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Font Awesome -->
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
    h1, h2 {
      color: var(--barbie-dark);
    }
    /* Estilos para las tarjetas (botones) de estadísticas: se revierten al estilo anterior */
    .stat {
      font-size: 0.9em;
      margin: 0;
    }
    .card {
      border: none;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-card {
      padding: 10px;
      margin-bottom: 15px;
      text-align: center;
    }
    /* Contenedor de gráficos con tamaño uniforme (se mantienen los estilos recientes) */
    .chart-container {
      margin: 10px auto;  /* Margen superior reducido para subir los gráficos */
      padding: 10px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      height: 300px; /* Altura fija para todos los gráficos */
      position: relative;
    }
    .chart-container h2 {
      font-size: 1em;
      margin-bottom: 10px;
    }
    /* El canvas se posiciona para ocupar el espacio restante; se reduce el offset superior */
    .chart-container canvas {
      position: absolute;
      top: 30px;  /* Offset superior reducido */
      left: 0;
      right: 0;
      bottom: 0;
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
  
  <div class="container my-4">
    <h1 class="text-center mb-4">Panel de Estadísticas del Mundo Barbie</h1>
    
    <!-- Tarjetas de Estadísticas Básicas (se usan las cards anteriores para estadísticas) -->
    <div class="row mb-2">
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Total de Personajes: <strong><?php echo $total_characters; ?></strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Total de Profesiones: <strong><?php echo $total_professions; ?></strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Promedio de Profesiones por Personaje: <strong><?php echo $avg_professions_per_character; ?></strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Edad Promedio: <strong><?php echo $avg_age; ?> años</strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Nivel de Experiencia Más Común: <strong><?php echo $common_nivel; ?></strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Salario Promedio: <strong>$<?php echo $avg_salary; ?></strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Profesión con Salario Más Alto: <strong><?php echo $profession_max; ?> ($<?php echo $max_salary; ?>)</strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Profesión con Salario Más Bajo: <strong><?php echo $profession_min; ?> ($<?php echo $min_salary; ?>)</strong></p>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="card stat-card">
          <p class="stat">Personaje con el Salario Más Alto: <strong><?php echo $character_highest_salary; ?></strong></p>
        </div>
      </div>
    </div>
    
    <!-- Sección de Gráficos con contenedores uniformes -->
    <div class="row">
      <div class="col-md-4">
        <div class="chart-container">
          <h2 class="text-center">Salario Promedio por Categoría</h2>
          <canvas id="salaryChart"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-container">
          <h2 class="text-center">Distribución de Profesiones</h2>
          <canvas id="categoryPieChart"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-container">
          <h2 class="text-center">Niveles de Experiencia</h2>
          <canvas id="experienceDoughnutChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts para los Gráficos -->
  <script>
    // Gráfico 1: Bar Chart para Salario Promedio por Categoría
    const ctx1 = document.getElementById('salaryChart').getContext('2d');
    const salaryChartData = {
      labels: <?php echo json_encode(array_keys($avg_salary_by_category)); ?>,
      datasets: [{
        label: 'Salario Promedio ($USD)',
        data: <?php echo json_encode(array_values($avg_salary_by_category)); ?>,
        backgroundColor: [
          'rgba(255, 99, 132, 0.5)',
          'rgba(54, 162, 235, 0.5)',
          'rgba(255, 206, 86, 0.5)',
          'rgba(75, 192, 192, 0.5)',
          'rgba(153, 102, 255, 0.5)',
          'rgba(255, 159, 64, 0.5)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1
      }]
    };
    const salaryChartConfig = {
      type: 'bar',
      data: salaryChartData,
      options: {
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    };
    const salaryChart = new Chart(ctx1, salaryChartConfig);
    
    // Gráfico 2: Pie Chart para Distribución de Profesiones
    const ctx2 = document.getElementById('categoryPieChart').getContext('2d');
    const categoryPieChartData = {
      labels: <?php echo json_encode(array_keys($category_distribution)); ?>,
      datasets: [{
        data: <?php echo json_encode(array_values($category_distribution)); ?>,
        backgroundColor: [
          'rgba(255, 99, 132, 0.6)',
          'rgba(54, 162, 235, 0.6)',
          'rgba(255, 206, 86, 0.6)',
          'rgba(75, 192, 192, 0.6)',
          'rgba(153, 102, 255, 0.6)',
          'rgba(255, 159, 64, 0.6)'
        ],
        borderWidth: 1,
        borderColor: '#fff'
      }]
    };
    const categoryPieChartConfig = {
      type: 'pie',
      data: categoryPieChartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    };
    const categoryPieChart = new Chart(ctx2, categoryPieChartConfig);
    
    // Gráfico 3: Doughnut Chart para Niveles de Experiencia
    const ctx3 = document.getElementById('experienceDoughnutChart').getContext('2d');
    const experienceDoughnutChartData = {
      labels: <?php echo json_encode(array_keys($nivel_counts)); ?>,
      datasets: [{
        data: <?php echo json_encode(array_values($nivel_counts)); ?>,
        backgroundColor: [
          'rgba(255, 205, 86, 0.6)',
          'rgba(75, 192, 192, 0.6)',
          'rgba(201, 203, 207, 0.6)'
        ],
        borderColor: '#fff',
        borderWidth: 1
      }]
    };
    const experienceDoughnutChartConfig = {
      type: 'doughnut',
      data: experienceDoughnutChartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    };
    const experienceDoughnutChart = new Chart(ctx3, experienceDoughnutChartConfig);
  </script>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
