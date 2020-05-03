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
	if (readline("Weiter mit \"ja\": ") == "ja")
	{
		echo "\r\n";
		UpgradeFundstellen($config);
		readline("Weiter mit [EINGABE] ...");
	}
	echo "\r\n";

    echo "Im Folgenden werden alle Ablagen um eine GUID (UUID) erweitert.\r\n";
	if (readline("Weiter mit \"ja\": ") == "ja")
	{
		echo "\r\n";
		UpgradeAblagen($config);
		readline("Weiter mit [EINGABE] ...");
	}
	echo "\r\n";

    echo "Im Folgenden werden alle Orte gelöscht. Orte werden um eine Kategorie erweitert.\r\n";
	if (readline("Weiter mit \"ja\": ") == "ja")
	{
		echo "\r\n";
		UpgradeOrte($config);
		readline("Weiter mit [EINGABE] ...");
	}
	echo "\r\n";

    echo "Im Folgenden wird die Benutzerverwaltung angelgt. Hierzu gehört das Merken (Bookmarken) und Bewerten (5-Sterne-Bewertung) von Funden.\r\n";
	if (readline("Weiter mit \"ja\": ") == "ja")
	{
		echo "\r\n";
		CreateUserManagement($config);
		readline("Weiter mit [EINGABE] ...");
	}
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

function DoesColumnExist($config, $tableName, $columnName)
{
    $doesColumnExist = false;
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        SELECT COUNT(*) AS Anzahl
	        FROM information_schema.COLUMNS
	        WHERE TABLE_SCHEMA = '".$config["MYSQL_DATENBANK"]."' AND
	        TABLE_NAME = '".$tableName."' AND
	        COLUMN_NAME = '".$columnName."';");
	    $datensatz = $ergebnis->fetch_assoc();
	    $doesColumnExist = intval($datensatz["Anzahl"]) == 0 ? false : true;
    }
    $mysqli->close();

    return $doesColumnExist;
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
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Tabelle \"Fundstelle\" ist angelegt.\r\n";
	    	}
    	}

    	$mysqli->close();
    }
}

function CreateTableUser($config)
{
    if (DoesTableExist($config, "User"))
    {
        echo "Die Tabelle \"User\" existiert bereits.\r\n";
    }
    else
    {
    	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    	if (!$mysqli->connect_errno)
    	{
	    	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
				CREATE TABLE IF NOT EXISTS `User` (
  					`Id` int(11) NOT NULL AUTO_INCREMENT,
					`FirstName` varchar(20) NOT NULL,
					`LastName` varchar(30) NOT NULL,
					`Guid` varchar(36) DEFAULT NULL,
					PRIMARY KEY (`Id`),
					UNIQUE KEY `IndexUserGuid` (`Guid`)
				);
	    	");

	    	if ($mysqli->errno)
	    	{
	    		echo "Beim Anlegen der Tabelle \"User\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Tabelle \"User\" ist angelegt.\r\n";
	    	}
    	}

    	$mysqli->close();
    }
}

function CreateTableBookmark($config)
{
    if (DoesTableExist($config, "Bookmark"))
    {
        echo "Die Tabelle \"Bookmark\" existiert bereits.\r\n";
    }
    else
    {
    	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    	if (!$mysqli->connect_errno)
    	{
	    	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
				CREATE TABLE IF NOT EXISTS `Bookmark` (
  					`User_Id` int(11) NOT NULL,
					`Fund_Id` int(11) NOT NULL,
					PRIMARY KEY (`User_Id`, `Fund_Id`),
					FOREIGN KEY (`User_Id`) REFERENCES `User`(`Id`),
					FOREIGN KEY (`Fund_Id`) REFERENCES `Fund`(`Id`)
				);
	    	");

	    	if ($mysqli->errno)
	    	{
	    		echo "Beim Anlegen der Tabelle \"Bookmark\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Tabelle \"Bookmark\" ist angelegt.\r\n";
	    	}
    	}

    	$mysqli->close();
    }
}

function CreateTableRating($config)
{
    if (DoesTableExist($config, "Rating"))
    {
        echo "Die Tabelle \"Rating\" existiert bereits.\r\n";
    }
    else
    {
    	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    	if (!$mysqli->connect_errno)
    	{
	    	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
				CREATE TABLE IF NOT EXISTS `Rating` (
  					`User_Id` int(11) NOT NULL,
					`Fund_Id` int(11) NOT NULL,
					`Rating` tinyint(1) NOT NULL,
					PRIMARY KEY (`User_Id`, `Fund_Id`),
					FOREIGN KEY (`User_Id`) REFERENCES `User`(`Id`),
					FOREIGN KEY (`Fund_Id`) REFERENCES `Fund`(`Id`)
				);
	    	");

	    	if ($mysqli->errno)
	    	{
	    		echo "Beim Anlegen der Tabelle \"Rating\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Tabelle \"Rating\" ist angelegt.\r\n";
	    	}
    	}

    	$mysqli->close();
    }
}

function CreateTableOrtCategory($config)
{
    if (DoesTableExist($config, "OrtCategory"))
    {
        echo "Die Tabelle \"OrtCategory\" existiert bereits.\r\n";
    }
    else
    {
    	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    	if (!$mysqli->connect_errno)
    	{
	    	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
				CREATE TABLE IF NOT EXISTS `OrtCategory` (
  					`Id` int(11) NOT NULL AUTO_INCREMENT,
  					`Bezeichnung` varchar(30) NOT NULL UNIQUE,
                    PRIMARY KEY (`Id`)
				);
	    	");

	    	if ($mysqli->errno)
	    	{
	    		echo "Beim Anlegen der Tabelle \"OrtCategory\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Tabelle \"OrtCategory\" ist angelegt.\r\n";
	    	}
    	}

    	$mysqli->close();
    }
}

function CreateUniqueIndex($config, $tableName, $indexName, $columnName)
{
    $ergebnis = false;
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        CREATE UNIQUE INDEX ".$indexName."
            ON ".$tableName." (".$columnName.");");
    }
    $mysqli->close();

    return $ergebnis;
}
#endregion
#endregion

#region data operations
#region Ablage
function GetAblage($config)
{
    $ablagen = array();
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
        SELECT Id, Bezeichnung
        FROM Ablage;");

	    if (!$mysqli->errno)
		{
			while ($datensatz = $ergebnis->fetch_assoc())
			{
				$ablage["Id"] = intval($datensatz["Id"]);
				$ablage["Bezeichnung"] = $datensatz["Bezeichnung"];
				array_push($ablagen, $ablage);
			}
		}
    }
    $mysqli->close();

    return $ablagen;
}

function InsertGuidToAblage($config, $ablage)
{
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        UPDATE Ablage
	        SET Guid = UUID()
	        WHERE Id = ".$ablage["Id"].";");

	    if ($mysqli->errno)
	    {
	    	echo "Beim Hinzufügen der GUID für die Ablage (".$ablage["Id"].") ist ein Fehler aufgetreten!\r\n";
	    	echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    }
	    else
	    {
	    	echo "GUID wurde erfolgreich zu Ablage (".$ablage["Id"].") hinzugefügt.\r\n";
	    }
    }
    $mysqli->close();
}
#endregion

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
	    	echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    }
	    else
	    {
	    	echo "Fundstelle (".$fundstelle["Id"].") ist erfolgreich in der Tabelle \"Fundstelle\" angelgt.\r\n";
	    }
    }
    $mysqli->close();
}

function InsertColumnGuidInTableAblage($config)
{
	if (DoesColumnExist($config, "Ablage", "Guid"))
	{
		echo "Die Spalte \"Guid\" existiert bereits in der Tabelle \"Ablage\".";
	}
	else
	{
   	$mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

   	if (!$mysqli->connect_errno)
   	{
	   	$mysqli->set_charset("utf8");
	    	$ergebnis = $mysqli->query("
	        ALTER TABLE `Ablage` ADD COLUMN `Guid` varchar(36) DEFAULT NULL;");

	   	if ($mysqli->errno)
	   	{
	    		echo "Beim Anlegen der Spalte \"Guid\" in der Tabelle \"Ablage\" ist ein Fehler aufgetreten!\r\n";
	    		echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    	}
	    	else
	    	{
	    		echo "Spalte \"Guid\" wurde erfolgreich in Tabelle \"Ablage\" angelgt.\r\n";
	    		CreateUniqueIndex($config, "Ablage", "IndexAblageGuid", "Guid");
	    	}
    	}

   	$mysqli->close();
	}
}
#endregion

#region Ort
function DeleteLinkedDataToTableOrt($config)
{
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
            DELETE
            FROM Kontext_Ort;");


	    if ($mysqli->errno)
	    {
	    	echo "Beim Löschen aller Verknüpfungen zwischen Orten und Kontexten ist ein Fehler aufgetreten!\r\n";
	    	echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    }
	    else
	    {
	    	echo "Alle Verknüpfungen zwischen Orten und Kontexten sind erfolgreich gelöscht.\r\n";
	    }
    }
    $mysqli->close();
}

function DeleteDataInTableOrt($config)
{
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
        $mysqli->autocommit(false);
        $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $passed = true;
        $passed = $mysqli->query("SET FOREIGN_KEY_CHECKS=0;") && $passed;

        if ($passed)
        {
            $passed = $mysqli->query("DELETE FROM Ort;") && $passed;
        }

        if ($passed)
        {
            $passed = $mysqli->query("SET FOREIGN_KEY_CHECKS=1;") && $passed;
        }

        if ($passed)
        {
            $mysqli->commit();
        }
        else
        {
            $mysqli->rollback();
        }

	    if ($mysqli->errno)
	    {
	    	echo "Beim Löschen Orte ist ein Fehler aufgetreten!\r\n";
	    	echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    }
	    else
	    {
	    	echo "Alle Orte sind erfolgreich gelöscht.\r\n";
	    }
    }
    $mysqli->close();
}

function InsertOrtCategoriesToTableOrtCategory($config)
{
    $ortCategoryBezeichnung = array("Kartasterdatum", "Gebietskörperschaft", "Flurname");

    for ($i = 0; $i < count($ortCategoryBezeichnung); $i++)
    {
        InsertOrtCategoryToTableOrtCategory($config, $ortCategoryBezeichnung[$i]);
    }
}

function InsertOrtCategoryToTableOrtCategory($config, $ortCategoryBezeichnung)
{
    $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

    if (!$mysqli->connect_errno)
    {
	    $mysqli->set_charset("utf8");
	    $ergebnis = $mysqli->query("
	        INSERT INTO OrtCategory(Bezeichnung)
	        VALUES('".$ortCategoryBezeichnung."');");

	    if ($mysqli->errno)
	    {
	    	echo "Beim Anlegen einer Ortskategorie in der Tabelle \"OrtCategory\" ist ein Fehler aufgetreten!\r\n";
	    	echo $mysqli->errno.": ".$mysqli->error."\r\n";
	    }
	    else
	    {
	    	echo "Ortskategorie (".$ortCategoryBezeichnung.") ist erfolgreich in der Tabelle \"OrtCategory\" angelgt.\r\n";
	    }
    }
    $mysqli->close();
}

function InsertColumnCategoryIdInTableOrt($config)
{
	if (DoesColumnExist($config, "Ort", "Category_Id"))
	{
		echo "Die Spalte \"Category_Id\" existiert bereits in der Tabelle \"Ort\".";
	}
	else
	{
        $mysqli = new mysqli($config["MYSQL_HOST"], $config["MYSQL_BENUTZER"], $config["MYSQL_KENNWORT"], $config["MYSQL_DATENBANK"]);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
    
            $ergebnis = $mysqli->query("ALTER TABLE `Ort` ADD COLUMN `Category_Id` int NOT NULL, ADD FOREIGN KEY (`Category_Id`) REFERENCES `OrtCategory` (`Id`);");
        
            if ($mysqli->errno)
            {
                    echo "Beim Anlegen der Spalte \"Category_Id\" in der Tabelle \"Ort\" ist ein Fehler aufgetreten!\r\n";
                    echo $mysqli->errno.": ".$mysqli->error."\r\n";
            }
            else
            {
                echo "Spalte \"Category_Id\" wurde erfolgreich in Tabelle \"Ort\" angelgt.\r\n";
            }
        }

        $mysqli->close();
	}
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

#region extend "Ablagen" by "GUID"
function UpgradeAblagen($config)
{
	InsertColumnGuidInTableAblage($config);
	$ablagen = GetAblage($config);

	for ($i = 0; $i < count($ablagen); $i++)
	{
		InsertGuidToAblage($config, $ablagen[$i]);
	}
}
#endregion

#region extent "Orte" by "Category"
function UpgradeOrte($config)
{
    DeleteLinkedDataToTableOrt($config);
    DeleteDataInTableOrt($config);

    CreateTableOrtCategory($config);
    CreateUniqueIndex($config, "OrtCategory", "IndexOrtCategoryBezeichnung", "Bezeichnung");
    InsertOrtCategoriesToTableOrtCategory($config);

    InsertColumnCategoryIdInTableOrt($config);    
}
#endregion

#region create user management
function CreateUserManagement($config)
{
	CreateTableUser($config);
	CreateTableBookmark($config);
	CreateTableRating($config);
}
#endregion
#endregion
