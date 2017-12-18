<?php
error_reporting(E_ALL);
ini_set("display_errors", 1); 

require_once("../../../Factory/OrtTypFactory.php");

if (isset($_GET["Id"]))
{
    $ortTypFactory = new OrtTypFactory();
    $ortTyp = $ortTypFactory->loadById(intval($_GET["Id"]));
    return $ortTypFactory->delete($ortTyp);
}