<?php
header("Content-type: text/css");

function generate_css() {
  $color = "#FF0000";
  $background_color = "#000000";
  $font_size = "16px";

  $css = "
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
    
    .pagination__link:hover {
      text-decoration: underline;
    }
  ";

  return $css;
}

echo generate_css();
?>
