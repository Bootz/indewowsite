<?php
if(INCLUDED!==true)exit;
// ==================== //
$pathway_info[] = array('title'=>'Renommer un personnage', 'link'=>'index.php?n=admin&sub=chartools');
// ==================== //
?>
<?php
include "chartools/charconfig.php";
include "chartools/add.php";
include "chartools/functionstransfer.php";
include "chartools/functionsrename.php";
include "chartools/tabs.php";
?>