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
$data = json_decode($json, true);
$varieties_per_page = 3;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start_from = ($current_page - 1) * $varieties_per_page;
$end_at = $start_from + $varieties_per_page;
$current_page_data = array_slice($data, $start_from, $varieties_per_page);
// Initialize an array to store the cultures and their paginated varieties
$cultures = [];
foreach ($data['Cultures'] as $i => $culture) {
  $cultures[$i] = [
    'id' => $culture['id'],
    'name' => $culture['Name'],
    'varieties' => [],
  ];
  foreach ($culture['Varieties'] as $j => $variety) {
    $cultures[$i]['varieties'][] = [
      'id' => $variety['id'],
      'name' => $variety['Name'],
      'code' => $variety['Code'],
      'query' => $variety['Query'],
      'patent' => $variety['Patent'],
      'closed' => $variety['Closed'],
      'reason' => $variety['Reason'],
    ];
  }
}
// Calculate the total number of pages based on the number of varieties per page and the total number of varieties (optional)
$total_pages = ceil($total_varieties / $varieties_per_page);

function debug_to_console($data) {
  $output = $data;
  if (is_array($output))
      $output = implode(',', $output);

  echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Патенты утратившие силу</title>
</head>
<body>
	<?php if ($total_varieties > 0): ?>
		<h1>Патенты утратившие силу</h1>
		<?php for ($i = 0; $i < count($current_page_data); $i++): ?>
      <?php debug_to_console($current_page_data["Cultures"][$i]["Name"]) ?>
      <div> 
        <h3><?php echo $current_page_data["Cultures"][$i]["Name"]; ?></h3> 
        <?php foreach ($current_page_data["Cultures"][$i]["Varieties"] as $j => $variety): ?> 
          <p><?php echo $variety["Name"]; ?></p> 
        <?php endforeach; ?> 
      </div> 
    <?php endfor; ?> 
    <?php if ($current_page > 1): ?>
			<a href="?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a> |
		<?php endif; ?> 
		<?php if ($current_page < $total_pages): ?> 
			<a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a> |
		<?php endif; ?> 
		Pagination: 
    <?php echo "Page " . $current_page . " of " . $total_pages; ?> | 
    Total Objects: <?php echo $total_varieties; ?> | 
    Objects per Page: <?php echo $varieties_per_page; ?> |  
    PHP Version: <?php echo PHP_VERSION; ?> | 
    Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?> | 
    Script Name: <?php echo $_SERVER['SCRIPT_NAME']; ?> | 
    Script Filename: <?php echo $_SERVER['PHP_SELF']; ?> | 
    Script Path: <?php echo $_SERVER['PHP_SELF']; ?> | 
    Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?> | 
    </body>
    </html>