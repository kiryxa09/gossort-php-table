<?php
$handle = fopen('Patents.json', 'r');
$json = stream_get_contents($handle);
fclose($handle);
$dataArray = json_decode($json, true);

$varietiesPerPage = 25;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$startIndex = ($page - 1) * $varietiesPerPage;
$endIndex = $startIndex + $varietiesPerPage - 1;

$executed = false;
$filtered = false;

if (!$executed) {
  $varieties = $dataArray['Varieties'];

  $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
  $totalvarieties = count($dataArray['Varieties']);
  $totalPages = ceil($totalvarieties / $varietiesPerPage);
  $executed = true;
};

$filterVariety = '';
$filterKind = '';

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
if ($searchQuery) {
  $varieties = [];
  $counter = 0;
  $loweredQuery = strtolower($searchQuery); //mb_strtolower in server
  foreach ($dataArray['Varieties'] as $variety) {
    $kindName = strtolower($variety['Kind']); //mb_strtolower in server
    $varietyName = strtolower($variety['Name']); //mb_strtolower in server
    if (strpos($kindName, $loweredQuery) !== false || strpos($varietyName, $loweredQuery) !== false) {
      $varieties[] = $variety;
      $counter++;

      if (strpos($varietyName, $loweredQuery) !== false) {
        $filterVariety = $searchQuery;
      }
      if (strpos($kindName, $loweredQuery) !== false) {
        $filterKind = $searchQuery;
      }
    }
  }
  $varieties = array_slice($varieties, $startIndex, $varietiesPerPage);
  $totalPages = ceil($counter / $varietiesPerPage);
  $filtered = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
  $totalvarieties = count($dataArray['Varieties']);
  $totalPages = ceil($totalvarieties / $varietiesPerPage);
  $filtered = false;
}

function debug_to_console($data)
{
  $output = $data;
  if (is_array($output))
    $output = implode(',', $output);

  echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
};
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>Патенты утратившие силу</title>
  <style type="text/css">
    /* style-kit in server => style only for local dev*/
    .patents__table {
      margin: auto;
      width: 80%;
      border-collapse: collapse;
    }

    .patents__table__row:nth-child(even) {
      background-color: #f2f2f2;
    }

    .patents__table__header {
      font-weight: bold;
      padding: 8px;
      text-align: left;
    }

    .patents__table__cell {
      padding: 8px;
      margin: 20px;
    }

    .patents__pagination {
      margin-top: 16px;
    }

    .patents__pagination__link {
      margin-right: 8px;
      text-decoration: none;
      color: blue;
    }

    .patents__pagination__link_inactive {
      color: green;
    }

    .patents__pagination__link:hover {
      text-decoration: underline;
    }

    .patents__forms {
      display: flex;
      justify-content: center;
    }

    .patents__form {
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 5px;
      height: 50px;
      margin: 15px 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .patents__form__refresh {
      border: none;
      justify-content: center;
    }

    .patents__form__input {
      border: none;
      outline: none;
      padding: 10px;
      border-radius: 0;
      height: 45px;
      margin: 0 0 0 10px;
      padding: 0;
      width: 100%;
      font-family: Inter;
    }

    .patents__form__label {
      width: max-content;
      white-space: nowrap;
      font-size: 12px;
    }

    .patents__filtered-text {
      color: #4B75B4;
      padding: 10px 0;
    }

    .patents__form__button {
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

    .patents__form__button:hover {
      background-color: #F3F4F6;
      text-decoration: none;
      transition-duration: 0.1s;
    }

    .patents__form__button:disabled {
      background-color: #FAFBFC;
      border-color: rgba(27, 31, 35, 0.15);
      color: #959DA5;
      cursor: default;
    }

    .patents__form__button:active {
      background-color: #EDEFF2;
      box-shadow: rgba(225, 228, 232, 0.2) 0 1px 0 inset;
      transition: none 0s;
    }

    .patents__form__button:focus {
      outline: 1px transparent;
    }

    .patents__form__button:before {
      display: none;
    }

    .patents__form__button:-webkit-details-marker {
      display: none;
    }

    .patents__form__button-refresh {
      align-self: center;
      height: 50px;
    }

    .patents__header {
      text-align: center;
      margin: 20px;
    }

    @media (max-width: 768px) {
      .patents__table__header {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }

      .patents__table__cell {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }

      .patents__form__label {
        display: none;
      }

      .patents__forms {
        flex-direction: column;
    }
    }

    @media (max-width: 480px) {
      .patents__table__cell {
        margin: 5px;
      }

      .patents__pagination {
        margin-top: 8px;
      }
    }
  </style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <div class="content">
    <div class="patents__forms">
      <form method="GET" action="" class="patents__form patents__form__search">
        <label class="patents__form__label" for="patents__searchInput">Поиск по виду, роду или сорту:</label>
        <input class="patents__form__input" id="patents__searchInput" name="search" placeholder="Введите запрос" list="patents__searchOptions" value="<?php echo is_array($searchQuery) ? implode("\n", $searchQuery) : $searchQuery; ?>">
        <datalist class="patents__form__datalist" id="patents__searchOptions">
          <?php
          $selectOptions = [];
          foreach ($dataArray['Varieties'] as $variety) {
            $kindName = $variety['Kind'];
            if (!in_array($kindName, $selectOptions)) {
              $selectOptions[] = $kindName;
              echo "<option value=\"$kindName\">$kindName</option>";
            }
          }
          foreach ($dataArray['Varieties'] as $variety) {
            $varietyName = $variety['Name'];
            if (!in_array($varietyName, $selectOptions)) {
              $selectOptions[] = $varietyName;
              echo "<option value=\"$varietyName\">$varietyName</option>";
            }
          }
          ?>
        </datalist>
        <button class="patents__form__button" type="submit">&#128270;</button>
      </form>

      <form class="patents__form patents__form__refresh" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <button class="patents__form__button patents__form__button-refresh" type="submit">К списку &#127968;</button>
      </form>

    </div>

    <?php
    if ($filtered) {
      echo '<span class="patents__filtered-text">Отфильтрованный список</span>';
      echo '<br />';
    }
    echo '<table class="patents__table">';
    echo '<tr class="patents__table__row">';
    echo '<th class="patents__table__header"><b>Род и вид</b></th>';
    echo '<th class="patents__table__header"><b>Код</b></th>';
    echo '<th class="patents__table__header"><b>Заявка</b></th>';
    echo '<th class="patents__table__header"><b>Сорт</b></th>';
    echo '<th class="patents__table__header"><b>Патент</b></th>';
    echo '<th class="patents__table__header"><b>Окончание действия</b></th>';
    echo '<th class="patents__table__header"><b>Причина</b></th>';
    echo '</tr>';
    foreach ($varieties as $variety) {
      echo '<tr class="patents__table__row">';
      echo '<td class="patents__table__cell">' . $variety['Kind'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Code'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Application'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Name'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Patent'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Closed'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Reason'] . '</td>';
      echo '</tr>';
    }
    echo '</table>';
    if ($filtered) {
      echo '<ul class="pagination patents__pagination">';
      for ($i = $page - 5; $i <= $page + 5 && $i <= $totalPages; $i++) {
        if ($i > 0) {
          $isActivePage = ($i == $page);
          $queryParam = '';

          if ($filterVariety) {
            $varietyCode = (is_array($filterVariety) ? implode(",", $filterVariety) : $filterVariety);
            $queryParam = '&search=' . $varietyCode;
          } elseif ($filterKind) {
            $kindCode = (is_array($filterKind) ? implode(",", $filterKind) : $filterKind);
            $queryParam = '&search=' . $kindCode;
          }

          echo ($isActivePage ?
            '<li><a class="patents__pagination__link pagination__link_inactive">' . $i . '</a></li>'
            :
            '<li><a class="patents__pagination__link" href="?page=' . $i . $queryParam . '">' . $i . '</a></li>');

          if ($i == $page + 5 && $i < $totalPages) {
            echo "...";
            echo '<li><a class="patents__pagination__link" href="?page=' . $totalPages . $queryParam . '">' . $totalPages . '</a></li>';
          }
        }
      }
      echo '</ul>';
    } else {
      echo '<ul class="pagination patents__pagination">';
      for ($i = $page - 5; $i <= $page + 5 && $i <= $totalPages; $i++) {
        if ($i > 0) {
          if ($i == $page) {
            echo '<li><a class="patents__pagination__link pagination__link_inactive">' . $i . '</a></li>';
          } else {
            echo '<li><a class="patents__pagination__link" href="?page=' . $i . '">' . $i . '</a></li>';
          }
        }
        if ($i == $page + 5 && $i < $totalPages) {
          echo "...";
          echo '<li><a class="patents__pagination__link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }
      }
    }
    ?>
  </div>
</body>

</html>