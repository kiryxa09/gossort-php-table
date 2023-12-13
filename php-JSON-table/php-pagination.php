<?php
// Set memory limit to 1024 MB
$memory_limit = '1024M';
// Include updated php.ini file (assuming it's in the same directory as this script)
$ini_path = dirname(__FILE__) . './php.ini';
ini_set('memory_limit', $memory_limit);
ini_set('include_path', '.:' . $ini_path); // Add current directory and php.ini path to include_path for easier loading of php files with require or include statements (optional)
?>
<?php

$json = file_get_contents('patents-example.json');
$dataArray = json_decode($json, true);

$culturesPerPage = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$startIndex = ($page - 1) * $culturesPerPage;
$endIndex = $startIndex + $culturesPerPage - 1;

$cultures = array_slice($dataArray['Cultures'], $startIndex, $culturesPerPage);



$totalCultures = count($dataArray['Cultures']);
$totalPages = ceil($totalCultures / $culturesPerPage);


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Патенты утратившие силу</title>
    <link rel="stylesheet" media="screen" href="style.php">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php 
    echo '<table class="table">';
    echo '<tr class="table__row"><th class="table__header">Код</th><th class="table__header">Заявка</th><th class="table__header">Название</th><th class="table__header">Патент</th><th class="table__header">Окончание действия</th><th class="table__header">Причина</th></tr>';
    foreach ($cultures as $culture) {
        echo '<tr class="table__row">';
        echo '<td class="table__cell">' . $culture['Name'] . '</td>';
        foreach ($culture['Varieties'] as $variety) {
          echo '<tr class="table__row">';
            echo '<td class="table__cell">' . $variety['Code'] . '</td>';
            echo '<td class="table__cell">' . $variety['Query'] . '</td>';
            echo '<td class="table__cell">' . $variety['Name'] . '</td>';
            echo '<td class="table__cell">' . $variety['Patent'] . '</td>';
            echo '<td class="table__cell">' . $variety['Closed'] . '</td>';
            echo '<td class="table__cell">' . $variety['Reason'] . '</td>';
          echo '</tr>';
      }
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        echo '<a class="pagination__link" href="?page=' . $i . '">' . $i . '</a> ';
    }
    echo '</div>';
    ?>
</body>
</html>