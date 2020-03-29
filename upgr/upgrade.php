<?php
error_reporting(E_ALL);
ini_set("display_errors", 1); 
    
Main();

function Main()
{
    $config = array();
    $config["MYSQL_HOST"] = "localhost";
    $config["MYSQL_BENUTZER"] = "";
    $config["MYSQL_KENNWORT"] = "";
    $config["MYSQL_DATENBANK"] = "Munins_Archiv";
    $config["DATABASE_BACKUP_FILE_PATH"] = "";
    $config["DATABASE_BACKUP_FILE_NAME"] = "Munins_Archiv_v1.1.sql";

    echo "MUNINS ARCHIV\r\n";
    echo "\r\n";
    echo "Hinweise:\r\n";
    echo "\r\n";
    echo "Dieses Upgrade-Skript ist für den Einsatz auf einem Linux-Betriebssystem konzipiert. Wenn Sie ein Windows-Betriebssystem verwenden, können Sie dieses Skript entsprechend anpassen oder Sie wenden sich an den Hersteller (http://github.com/robertgruening/Munins-Archiv/).\r\n";
    echo "\r\n";
    echo "Falls Sie das Upgrade abbrechen wollen, verwenden Sie die Tastenkombination Steuerung und C (STRG+C).\r\n";
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    echo "Starte Upgrade von Version 1.1 auf Version 1.2.\r\n";
    echo "\r\n";
    echo "\r\n";
    
    echo "1. Konfiguriere Parameter\r\n";
    echo "Im Folgenden müssen Sie Zugangsdaten und Einstellungen vornehmen, damit das Skript das Upgrade durchführen kann.\r\n";
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    $config = FillConfig($config); 
    echo "\r\n"; 
    ListConfig($config);    
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    
    echo "2. Datenbanksicherung\r\n";
    echo "Im Folgenden wird eine Sicherungsdatei mit dem Inhalt der Datenbank \"Munins_Archiv\" erzeugt.\r\n";
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    CreateDatabaseBackup($config);
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";

    echo "3. Datenbankaktualisierung\r\n";
    echo "Im Folgenden werden alle Fundstellen um geografische Positionen erweitert. \r\n";
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    UpgradeFundstellen($config);
    readline("Weiter mit [EINGABE] ...");
    echo "\r\n";
    echo "Beende Upgrade von Version 1.1 auf Version 1.2.\r\n";
}

#region configuration
function FillConfig($config)
{
    echo "Geben Sie nachfolgend die Zugangsdaten eine MySQL-Datenbankbenutzers an, der Administratorrechte für die Datenbank \"".$config["MYSQL_DATENBANK"]."\" hat.\r\n";
    $config["MYSQL_BENUTZER"] = readline("Benutzername: ");
    $config["MYSQL_KENNWORT"] = readline("Passwort: ");
    echo "\r\n";
    echo "Geben Sie nachfolgend den Verzeichnispfad an, in dem die Sicherungsdateien für das Upgrade abgelegt werden sollen.\r\n";
    $config["DATABASE_BACKUP_FILE_PATH"] = readline("Pfad: ");
    
    if (!endsWith($config["DATABASE_BACKUP_FILE_PATH"], "/"))
    {
        $config["DATABASE_BACKUP_FILE_PATH"] .= "/";
    }
    
    return $config;
}

function ListConfig($config)
{
    echo "MYSQL_HOST: .............. ".$config["MYSQL_HOST"]."\r\n";
    echo "MYSQL_BENUTZER: .......... ".$config["MYSQL_BENUTZER"]."\r\n";
    echo "MYSQL_KENNWORT: .......... ".($config["MYSQL_KENNWORT"] == "" ? "" : "*****")."\r\n";
    echo "MYSQL_DATENBANK: ......... ".$config["MYSQL_DATENBANK"]."\r\n";
    echo "DATABASE_BACKUP_FILE_PATH: ".$config["DATABASE_BACKUP_FILE_PATH"]."\r\n";
    echo "DATABASE_BACKUP_FILE_NAME: ".$config["DATABASE_BACKUP_FILE_NAME"]."\r\n";
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     
     if ($length == 0)
     {
        return true;
     }
          
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    
    if ($length == 0) 
    {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
#endregion

#region backup
function CreateDatabaseBackup($config)
{
    $result = exec("mysqldump ".$config["MYSQL_DATENBANK"].
                   " --password=".$config["MYSQL_KENNWORT"].
                   " --user=".$config["MYSQL_BENUTZER"].
                   " --single-transaction > '".$config["DATABASE_BACKUP_FILE_PATH"].$config["DATABASE_BACKUP_FILE_NAME"]."'",$output);
}
#endregion

#region SQL operations
#region table operations
function DoesTableExist($config, $tableName)
{
    $doesTableExist = false;
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        SELECT COUNT(*) AS Anzahl 
	        FROM information_schema.TABLES
	        WHERE TABLE_SCHEMA = '".$config["MYSQL_DATENBANK"]."' AND 
	        TABLE_NAME = '".$tableName."';");
	    $datensatz = $ergebnis->fetch_assoc();
	    $doesTableExist = intval($datensatz["Anzahl"]) == 0 ? false : true;
    }
    $mysqli->close();
    
    return $doesTableExist;
}

function CreateTableFundstelle($config)
{
    if (DoesTableExist($config, "Fundstelle"))
    {
        echo "Die Tabelle \"Fundstelle\" existiert bereits.\r\n";
    }
    else
    {
    	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    	if (!$mysqli->connect_errno)
    	{
	    	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
				CREATE TABLE IF NOT EXISTS `Fundstelle` (
  					`Id` int(11) NOT NULL,
  					`GeoPoint` point DEFAULT NULL,
  					PRIMARY KEY (`Id`)
				);	
	    	");
	    	
	    	if ($mysqli->errno)
	    	{
	    		echo "Beim Anlegen der Tabelle \"Fundstelle\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error;
	    	}
	    	else
	    	{
	    		echo "Tabelle \"Fundstelle\" ist angelegt.\r\n";
	    	}
    	}
    
    	$mysqli->close();
    }
}
#endregion
#endregion

#region data operations
#region Kontext
function GetKontexteByType($config, $kontextType)
{
    $kontexte = array();
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        SELECT Kontext.Id AS Id, Kontext.Bezeichnung AS Bezeichnung
	        FROM Kontext LEFT JOIN KontextTyp ON Kontext.Typ_Id = KontextTyp.Id	        
	        WHERE KontextTyp.Bezeichnung = '".$kontextType."';");
	        
	    if (!$mysqli->errno)
		{
			while ($datensatz = $ergebnis->fetch_assoc())
			{
				$kontext["Id"] = intval($datensatz["Id"]);
				$kontext["Bezeichnung"] = $datensatz["Bezeichnung"];
				array_push($kontexte, $kontext);
			}
		}
    }
    $mysqli->close();
    
    return $kontexte;
}

function InsertFundstelleToTableFundstelle($config, $fundstelle)
{
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        INSERT INTO Fundstelle(Id)
	        VALUES(".$fundstelle["Id"].");");
	        
	   
	    if ($mysqli->errno)
	    {
	    	echo "Beim Anlegen einer Fundstelle in der Tabelle \"Fundstelle\" ist ein Fehler aufgetreten!\r\n";
	    	echo $mysqli->errno.": ".$mysqli->error;
	    }
	    else
	    {
	    	echo "Fundstelle (".$fundstelle["Id"].") ist erfolgreich in der Tabelle \"Fundstelle\" angelgt.\r\n";
	    }
    }
    $mysqli->close();
}
#endregion
#endregion

#region tasks
#region extend "Fundstelle" by "GeoPoint"
function UpgradeFundstellen($config)
{
	CreateTableFundstelle($config);
	$fundstellen = GetKontexteByType($config, "Fundstelle");
	
	for ($i = 0; $i < count($fundstellen); $i++)
	{
		InsertFundstelleToTableFundstelle($config, $fundstellen[$i]);
	}  
}
#endregion
#endregion