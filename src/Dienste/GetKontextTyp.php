<?php
error_reporting(E_ALL);
ini_set("display_errors", 1); 

require_once("Klassen/Kontext/class.KontextTyp.php");

if (isset($_POST["Id"]))
{
	$kontextTyp = new KontextTyp();
	$kontextTyp->LoadById(intval($_POST["Id"]));
	echo json_encode($kontextTyp->ConvertToAssocArray());
}
else
{
	$assocArrayKontextTypen = array();
	$kontextTyp = new KontextTyp();
	$kontextTypen = $kontextTyp->LoadAll();
	for ($i = 0; $i < count($kontextTypen); $i++)
	{
		array_push($assocArrayKontextTypen, $kontextTypen[$i]->ConvertToAssocArray());
	}
	echo json_encode($assocArrayKontextTypen);
}
