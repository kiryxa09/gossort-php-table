<?php
// Set memory limit to 1024 MB
$memory_limit = '1024M';

// Include updated php.ini file (assuming it's in the same directory as this script)
ini_set('memory_limit', $memory_limit);
?>
<?php

$handle = fopen('patents-example.json', 'r');
$json = stream_get_contents($handle);
fclose($handle);

$dataArray = json_decode($json, true);

function debug_to_console($data)
{
  $output = $data;
  if (is_array($output))
    $output = implode(',', $output);

  echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

$varietiesPerPage = 4;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$startIndex = ($page - 1) * $varietiesPerPage;
$endIndex = $startIndex + $varietiesPerPage - 1;

$totalvarieties = count($dataArray['Varieties']);
$totalPages = ceil($totalvarieties / $varietiesPerPage);

$varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);

$filterKind = isset($_GET['kind']) ? $_GET['kind'] : '';

if (isset($_GET['kind'])) {
  $varieties = [];
  if ($filterKind) {
    $filterKind = ($filterKind);
    $filterKind = explode("\n", $filterKind);
    foreach ($dataArray['Varieties'] as $variety) {
      $kindName = strtolower($variety['Kind']);
      if (in_array($kindName, $filterKind)) {
        $varieties[] = $variety;
      }
    }
  } else {
    $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
  }
}


$filterVariety = isset($_GET['variety']) ? $_GET['variety'] : '';

if (isset($_GET['variety'])) {
  $varieties = [];
  if ($filterVariety) {
    $filterVariety = strtolower($filterVariety);
    $filterVariety = explode("\n", $filterVariety);
    foreach ($dataArray['Varieties'] as $variety) {
      $varietyName = strtolower($variety['Name']);
      if (in_array(strtolower($varietyName), $filterVariety)) {
        $varieties[] = $variety;
      }
    }
  } else {
    $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
    debug_to_console("3");
  }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>Патенты утратившие силу</title>
  <!-- <link rel="stylesheet" media="screen" href="style.php"> -->
  <style type="text/css">
    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table__row:nth-child(even) {
      background-color: #f2f2f2;
    }

    .table__header {
      font-weight: bold;
      padding: 8px;
      text-align: left;
    }

    .table__cell {
      padding: 8px;
      margin: 20px;
    }

    .pagination {
      margin-top: 16px;
    }

    .pagination__link {
      margin-right: 8px;
      text-decoration: none;
      color: blue;
    }

    .pagination__link_inactive {
      color: green;
    }

    .pagination__link:hover {
      text-decoration: underline;
    }

    .form {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form__button {
      appearance: none;
      background-color: #FAFBFC;
      border: 1px solid rgba(27, 31, 35, 0.15);
      border-radius: 6px;
      box-shadow: rgba(27, 31, 35, 0.04) 0 1px 0, rgba(255, 255, 255, 0.25) 0 1px 0 inset;
      box-sizing: border-box;
      color: #24292E;
      cursor: pointer;
      display: inline-block;
      font-family: -apple-system, system-ui, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
      font-size: 14px;
      font-weight: 500;
      line-height: 20px;
      list-style: none;
      padding: 6px 16px;
      position: relative;
      transition: background-color 0.2s cubic-bezier(0.3, 0, 0.5, 1);
      user-select: none;
      -webkit-user-select: none;
      touch-action: manipulation;
      vertical-align: middle;
      white-space: nowrap;
      word-wrap: break-word;
    }

    .form__button:hover {
      background-color: #F3F4F6;
      text-decoration: none;
      transition-duration: 0.1s;
    }

    .form__button:disabled {
      background-color: #FAFBFC;
      border-color: rgba(27, 31, 35, 0.15);
      color: #959DA5;
      cursor: default;
    }

    .form__button:active {
      background-color: #EDEFF2;
      box-shadow: rgba(225, 228, 232, 0.2) 0 1px 0 inset;
      transition: none 0s;
    }

    .form__button:focus {
      outline: 1px transparent;
    }

    .form__button:before {
      display: none;
    }

    .form__button:-webkit-details-marker {
      display: none;
    }

    @media (max-width: 768px) {
      .table__header {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }

      .table__cell {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }
    }

    /* Media query for screens smaller than 480px */
    @media (max-width: 480px) {
      .table__cell {
        margin: 5px;
      }

      .pagination {
        margin-top: 8px;
      }
    }
  </style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <form method="GET" action="" class="form">
    <label class="form__label" for="kind">Filter by kind:</label>
    <input class="form__textarea" id="kindInput" name="kind" placeholder="Сбросить фильтр" list="kindOptions">
    <datalist id="kindOptions">
      <?php
      $selectOptions = [];
      foreach ($dataArray['Varieties'] as $variety) {
        $kindName = $variety['Kind'];
        if (!in_array($kindName, $selectOptions)) {
          $selectOptions[] = $kindName;
          echo "<option value=\"$kindName\">$kindName</option>";
        }
      }
      ?>
    </datalist>
    </input>
    <button class="form__button" type="submit">Применить</button>
  </form>

  <form method="GET" action="" class="form">
    <label class="form__label" for="variety">Filter by Variety:</label>
    <input class="form__textarea" id="varietyInput" name="variety" placeholder="Сбросить фильтр" list="varietyOptions">
    <datalist id="varietyOptions">
      <?php
      $selectOptions = [];
      foreach ($dataArray['Varieties'] as $variety) {
        $varietyName = $variety['Name'];
        if (!in_array($varietyName, $selectOptions)) {
          $selectOptions[] = $varietyName;
          echo "<option value=\"$varietyName\">$varietyName</option>";
        }
      }
      ?>
    </datalist>
    </input>
    <button class="form__button" type="submit">Применить</button>
  </form>

  <?php
  if ($varieties !== array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage)) {
    echo '<h2 class="gossort__subheader">Отфильтрованный список</h2>';
    echo '<br />';
  }
  echo '<table class="table">';
  echo '<tr class="table__row">';
  echo '<th class="table__header">Род и вид</th>';
  echo '<th class="table__header">Код</th>';
  echo '<th class="table__header">Заявка</th>';
  echo '<th class="table__header">Сорт</th>';
  echo '<th class="table__header">Патент</th>';
  echo '<th class="table__header">Окончание действия</th>';
  echo '<th class="table__header">Причина</th>';
  echo '</tr>';
  foreach ($varieties as $variety) {
    echo '<tr class="table__row">';
    echo '<td class="table__cell">' . $variety['Kind'] . '</td>';
    echo '<td class="table__cell">' . $variety['Code'] . '</td>';
    echo '<td class="table__cell">' . $variety['Application'] . '</td>';
    echo '<td class="table__cell">' . $variety['Name'] . '</td>';
    echo '<td class="table__cell">' . $variety['Patent'] . '</td>';
    echo '<td class="table__cell">' . $variety['Closed'] . '</td>';
    echo '<td class="table__cell">' . $variety['Reason'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
  if ($varieties === array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage)) {
    echo '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
      if ($i == $page) {
        echo '<a class="pagination__link pagination__link_inactive"' . $i . '">' . $i . '</a> ';
      } else {
        echo '<a class="pagination__link" href="?page=' . $i . '">' . $i . '</a> ';
      }
    }
    echo '<br />';
    echo  'Current Page: ' . $page;
    echo '</div>';
  }
  ?>
</body>
</html>