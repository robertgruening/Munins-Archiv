<?php
include_once(__DIR__."/Factory.php");
include_once(__DIR__."/IListFactory.php");
include_once(__DIR__."/ListFactory.php");
include_once(__DIR__."/../Model/User.php");

class UserFactory extends Factory implements iListFactory
{
    #region variables
    private $_listFactory = null;
    #endregion

    #region properties
    protected function getListFactory()
    {
        return $this->_listFactory;
    }
    #endregion

    #region constructors
    function __construct()
    {
        $this->_listFactory = new ListFactory($this);
    }
    #endregion

    #region methods
    /**
     * Returns the name of the database table.
     */
    public function getTableName()
    {
        return "User";
    }

    #region load
	public function loadByGuid($guid)
	{
		global $logger;
		$logger->debug("Lade Element (".$guid.")");

		$element = null;
		$mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

		if (!$mysqli->connect_errno)
		{
			$mysqli->set_charset("utf8");
			$ergebnis = $mysqli->query($this->getSQLStatementToLoadByGuid($guid));

			if ($mysqli->errno)
			{
				$logger->error("Datenbankfehler: ".$mysqli->errno." ".$mysqli->error);
			}
			else
			{
				$element = $this->fill($ergebnis->fetch_assoc());
			}
		}

		$mysqli->close();

		return $element;
	}

    /**
    * Returns the SQL statement to load ID, FirstName, LastName and GUID
    * by User GUID.
    *
    * @param $guid GUID of the User to load.
    */
    protected function getSQLStatementToLoadByGuid($guid)
    {
        return "SELECT Id, FirstName, LastName, Guid
        FROM ".$this->getTableName()."
        WHERE Guid = '".$guid."';";
    }

    #region load
    protected function getSQLStatementToLoadById($id)
    {
        return "SELECT Id, FirstName, LastName, Guid
		FROM ".$this->getTableName()."
		WHERE Id = ".$id.";";
    }

    public function loadAll()
    {
        return $this->getListFactory()->loadAll();
    }

    protected function fill($dataSet)
    {
        if ($dataSet == null)
        {
            return null;
        }

        $user = new User();
        $user->setId(intval($dataSet["Id"]));
        $user->setFirstName($dataSet["FirstName"]);
        $user->setLastName($dataSet["LastName"]);
        $user->setGuid($dataSet["Guid"]);

        return $user;
    }
    #endregion

    #region save
    protected function getSQLStatementToInsert(iNode $element)
    {
        return "INSERT INTO ".$thss->getTableme." (FirstName, LastName, GuidName)
                VALUES ('".addslashes($element->getFirstName())."', '".addslashes($element->getLastName())."', UUID());";
    }

    protected function getSQLStatementToUpdate(iNode $element)
    {
        return "UPDATE ".$this->getTableName()."
		SET FirstName = '".addslashes($element->getFirstName())."',
		LastName = '".addslashes($element->getLastName())."'
                WHERE Id = ".$element->getId().";";
    }
    #endregion

    #region convert
    public function convertToInstance($object)
    {
        global $logger;
        $logger->debug("Konvertiere Daten zu User");

        if ($object == null)
        {
            $logger->error("User ist nicht gesetzt!");
            return null;
        }

        $user = new User();

        if (isset($object["Id"]))
        {
            $user->setId(intval($object["Id"]));
        }
        else
        {
            $logger->debug("ID ist nicht gesetzt!");
        }

        if (isset($object["FirstName"]))
        {
            $user->setFirstName($object["FirstName"]);
        }
        else
        {
            $logger->debug("Vorname ist nicht gesetzt!");
	}

        if (isset($object["LastName"]))
        {
            $user->setLastName($object["LastName"]);
        }
        else
        {
            $logger->debug("Nachname ist nicht gesetzt!");
	}

        if (isset($object["Guid"]))
        {
            $user->setGuid($object["Guid"]);
        }
        else
        {
            $logger->debug("GUID ist nicht gesetzt!");
        }


        return $user;
    }
    #endregion
    #endregion
}
