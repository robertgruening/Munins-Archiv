<?php
include_once(__DIR__."/Factory.php");
include_once(__DIR__."/../Model/RatedFund.php");
include_once(__DIR__."/FundAttributFactory.php");
include_once(__DIR__."/AblageFactory.php");
include_once(__DIR__."/KontextFactory.php");
include_once(__DIR__."/UserFactory.php");

class FundFactory extends Factory
{
    #region variables
    private $_fundAttributFactory = null;
    private $_ablageFactory = null;
    private $_kontextFactory = null;
    private $_userFactory = null;
    #endregion

    #region properties
    protected function getFundAttributFactory()
    {
        return $this->_fundAttributFactory;
    }

    protected function getAblageFactory()
    {
        if ($this->_ablageFactory == null)
        {
            $this->_ablageFactory = new AblageFactory();
        }

        return $this->_ablageFactory;
    }

    protected function getKontextFactory()
    {
        if ($this->_kontextFactory == null)
        {
            $this->_kontextFactory = new KontextFactory();
        }

        return $this->_kontextFactory;
    }

    protected function getUserFactory()
    {
	    if ($this->_userFactory == null)
	    {
		    $this->_userFactory = new UserFactory();
	    }

	    return $this->_userFactory;
    }
    #endregion

    #region constructors
    function __construct()
    {
        $this->_fundAttributFactory = new FundAttributFactory();
    }
    #endregion

    #region methods
    /**
    * Returns the name of the database table.
    */
    public function getTableName()
    {
        return "Fund";
    }

    #region load
    protected function getSQLStatementToLoadById($id)
    {
        return "SELECT Id, Anzahl, Bezeichnung, Dimension1, Dimension2, Dimension3, Masse
        FROM Fund
        WHERE Id = ".$id.";";
    }

    protected function fill($dataSet)
    {
        if ($dataSet == null)
        {
            return null;
        }

        $fund = new Fund();
        $fund->setId(intval($dataSet["Id"]));
        $fund->setBezeichnung($dataSet["Bezeichnung"]);

        if (intval($dataSet["Anzahl"]) < 0)
        {
            $fund->setAnzahl(str_replace("-", ">", $dataSet["Anzahl"]));
        }
        else
        {
            $fund->setAnzahl($dataSet["Anzahl"]);
        }

        $fund->setDimension1($dataSet["Dimension1"]);
        $fund->setDimension2($dataSet["Dimension2"]);
        $fund->setDimension3($dataSet["Dimension3"]);
        $fund->setMasse($dataSet["Masse"]);
        $fund->setAblage($this->getAblageFactory()->loadByFund($fund));
        $fund->setKontext($this->getKontextFactory()->loadByFund($fund));
        $fund->setFundAttribute($this->getFundAttributFactory()->loadByFund($fund));

        return $fund;
    }
    #endregion

    #region save
    protected function getSQLStatementToInsert(iNode $element)
    {
        $anzahl = intval(str_replace(">", "-", $element->getAnzahl()));

        return "INSERT INTO ".$this->getTableName()." (Anzahl, Bezeichnung, Dimension1, Dimension2, Dimension3, Masse, Kontext_Id, Ablage_Id)
        VALUES (".$anzahl.", '".addslashes($element->getBezeichnung())."',
        ".($element->getDimension1() === null ? "NULL" : $element->getDimension1()).",
        ".($element->getDimension2() === null ? "NULL" : $element->getDimension2()).",
        ".($element->getDimension3() === null ? "NULL" : $element->getDimension3()).",
        ".($element->getMasse() === null ? "NULL" : $element->getMasse()).",
        ".($element->getKontext() === null ? "NULL" : $element->getKontext()->getId()).",
        ".($element->getAblage() === null ? "NULL" : $element->getAblage()->getId()).");";
    }

    protected function getSQLStatementToUpdate(iNode $element)
    {
        $anzahl = intval(str_replace(">", "-", $element->getAnzahl()));

        return "UPDATE ".$this->getTableName()."
        SET Anzahl = ".$anzahl.",
        Bezeichnung = '".addslashes($element->getBezeichnung())."',
        Dimension1 = ".($element->getDimension1() === null ? "NULL" : $element->getDimension1()).",
        Dimension2 = ".($element->getDimension2() === null ? "NULL" : $element->getDimension2()).",
        Dimension3 = ".($element->getDimension3() === null ? "NULL" : $element->getDimension3()).",
        Masse = ".($element->getMasse() === null ? "NULL" : $element->getMasse()).",
        Kontext_Id = ".($element->getKontext() === null ? "NULL" : $element->getKontext()->getId()).",
        Ablage_Id = ".($element->getAblage() === null ? "NULL" : $element->getAblage()->getId())."
        WHERE Id = ".$element->getId().";";
    }
    #endregion

    #region convert
    public function convertToInstance($object)
    {
        global $logger;
        $logger->debug("Konvertiere Daten zu Fund");

        if ($object == null)
        {
            $logger->error("Fund ist nicht gesetzt!");
            return null;
        }

        $fund = new Fund();

        if (isset($object["Id"]))
        {
            $fund->setId(intval($object["Id"]));
        }

        if (isset($object["Anzahl"]))
        {
            $fund->setAnzahl(intval($object["Anzahl"]));
        }
        else
        {
            $logger->debug("Anzahl ist nicht gesetzt!");
        }

        if (isset($object["Bezeichnung"]))
        {
            $fund->setBezeichnung($object["Bezeichnung"]);
        }
        else
        {
            $logger->debug("Bezeichnung ist nicht gesetzt!");
        }

        if (isset($object["FundAttribute"]))
        {
            for ($i = 0; $i < count($object["FundAttribute"]); $i++)
            {
                $fund->addFundAttribut($this->getFundAttributFactory()->convertToInstance($object["FundAttribute"][$i]));
            }
        }
        else
        {
            $logger->debug("FundAttribute ist nicht gesetzt!");
        }

        if (isset($object["Dimension1"]) &&
        !empty($object["Dimension1"]))
        {
            $fund->setDimension1(intval($object["Dimension1"]));
        }
        else
        {
            $logger->debug("Dimension1 ist nicht gesetzt!");
        }

        if (isset($object["Dimension2"]) &&
        !empty($object["Dimension2"]))
        {
            $fund->setDimension2(intval($object["Dimension2"]));
        }
        else
        {
            $logger->debug("Dimension2 ist nicht gesetzt!");
        }

        if (isset($object["Dimension3"]) &&
        !empty($object["Dimension3"]))
        {
            $fund->setDimension3(intval($object["Dimension3"]));
        }
        else
        {
            $logger->debug("Dimension3 ist nicht gesetzt!");
        }

        if (isset($object["Masse"]) &&
        !empty($object["Masse"]))
        {
            $fund->setMasse(intval($object["Masse"]));
        }
        else
        {
            $logger->debug("Masse ist nicht gesetzt!");
        }

        if (isset($object["Ablage"]))
        {
            $fund->setAblage($this->getAblageFactory()->convertToInstance($object["Ablage"]));
        }
        else
        {
            $logger->warn("Ablage ist nicht gesetzt!");
        }

        if (isset($object["Kontext"]))
        {
            $fund->setKontext($this->getKontextFactory()->convertToInstance($object["Kontext"]));
        }
        else
        {
            $logger->warn("Kontext ist nicht gesetzt!");
        }

        return $fund;
    }
    #endregion

    #region Ablage
    public function loadByAblage($ablage)
    {
        $funde = array();
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByAblage($ablage));

            if (!$mysqli->errno)
            {
                while ($datensatz = $ergebnis->fetch_assoc())
                {
                    array_push($funde, $this->loadById(intval($datensatz["Id"])));
                }
            }
        }

        $mysqli->close();

        return $funde;
    }

    protected function getSQLStatementToLoadIdsByAblage($ablage)
    {
        return "SELECT Id
        FROM Fund
        WHERE Ablage_Id = ".$ablage->getId().";";
    }

    public function linkAblage($element, $ablage)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLinkAblage($element, $ablage));

            if (!$mysqli->errno)
            {
                $element->setAblage($ablage);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToLinkAblage($element, $ablage)
    {
        return "UPDATE ".$this->getTableName()."
        SET Ablage_Id = ".$ablage->getId()."
        WHERE Id = ".$element->getId().";";
    }

    public function unlinkAblage($element, $ablage)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToUnlinkAblage($element, $ablage));

            if (!$mysqli->errno)
            {
                $element->setAblage(null);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToUnlinkAblage($element, $ablage)
    {
        return "UPDATE ".$this->getTableName()."
        SET Ablage_Id = NULL
        WHERE Id = ".$element->getId().";";
    }
    #endregion

    #region Kontext
    public function loadByKontext($kontext)
    {
        $funde = array();
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByKontext($kontext));

            if (!$mysqli->errno)
            {
                while ($datensatz = $ergebnis->fetch_assoc())
                {
                    array_push($funde, $this->loadById(intval($datensatz["Id"])));
                }
            }
        }

        $mysqli->close();

        return $funde;
    }

    protected function getSQLStatementToLoadIdsByKontext($kontext)
    {
        return "SELECT Id
        FROM Fund
        WHERE Kontext_Id = ".$kontext->getId().";";
    }

    public function linkKontext($element, $kontext)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLinkKontext($element, $kontext));

            if (!$mysqli->errno)
            {
                $element->setKontext($kontext);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToLinkKontext($element, $kontext)
    {
        return "UPDATE ".$this->getTableName()."
        SET Kontext_Id = ".$kontext->getId()."
        WHERE Id = ".$element->getId().";";
    }

    public function unlinkKontext($element, $kontext)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToUnlinkKontext($element, $kontext));

            if (!$mysqli->errno)
            {
                $element->setKontext(null);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToUnlinkKontext($element, $kontext)
    {
        return "UPDATE ".$this->getTableName()."
        SET Kontext_Id = NULL
        WHERE Id = ".$element->getId().";";
    }
    #endregion

    #region FundAttribute
    public function loadByFundAttribut($fundAttribut)
    {
        $funde = array();
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByFundAttribut($fundAttribut));

            if (!$mysqli->errno)
            {
                while ($datensatz = $ergebnis->fetch_assoc())
                {
                    array_push($funde, $this->loadById(intval($datensatz["Id"])));
                }
            }
        }

        $mysqli->close();

        return $funde;
    }

    protected function getSQLStatementToLoadIdsByFundAttribut($fundAttribut)
    {
        return "SELECT Fund_Id AS Id
        FROM ".$this->getTableName()."_FundAttribut
        WHERE FundAttribut_Id = ".$fundAttribut->getId().";";
    }

    public function linkFundAttribut($element, $fundAttribut)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToLinkFundAttribut($element, $fundAttribut));

            if (!$mysqli->errno)
            {
                $element->AddFundAttribut($fundAttribut);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToLinkFundAttribut($element, $fundAttribut)
    {
        return "INSERT INTO ".$this->getTableName()."_FundAttribut (Fund_Id, FundAttribut_Id)
        VALUES (".$element->getId().",".$fundAttribut->getId().");";
    }

    public function unlinkFundAttribut($element, $fundAttribut)
    {
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

        if (!$mysqli->connect_errno)
        {
            $mysqli->set_charset("utf8");
            $ergebnis = $mysqli->query($this->getSQLStatementToUnlinkFundAttribut($element, $fundAttribut));

            if (!$mysqli->errno)
            {
                $element->removeFundAttribut($fundAttribut);
            }
        }

        $mysqli->close();

        return $element;
    }

    public function getSQLStatementToUnlinkFundAttribut($element, $fundAttribut)
    {
        return "DELETE
        FROM ".$this->getTableName()."_FundAttribut
        WHERE Fund_Id = ".$element->getId()." AND
        FundAttribut_Id = ".$fundAttribut->getId().";";
    }

    /**
    * Synchronises the Fund's FundAttribute with the given
    * FundAttributen.
    * Returns the updated Fund.
    *
    * @param $fund Fund to synchronise.
    * @param array $fundAttribute FundAttribute to be used as new FundAttribute.
    */
    public function synchroniseFundAttribute($fund, array $fundAttribute)
    {
        $fund = $this->linkNewFundAttribute($fund, $fundAttribute);
        $fund = $this->unlinkObsoleteFundAttribute($fund, $fundAttribute);

        return $fund;
    }

    /**
    * Links FundAttribute that are not in the Fund's
    * FundAttribut list.
    * Returns the updated Fund.
    *
    * @param $fund Fund to be updated with new FundAttributen.
    * @param array $fundAttribute FundAttribute to be used as new FundAttribute.
    */
    protected function linkNewFundAttribute($fund, array $fundAttribute)
    {
        for ($i = 0; $i < count($fundAttribute); $i++)
        {
            if (!$fund->containsFundAttribut($fundAttribute[$i]))
            {
                $fund = $this->linkFundAttribut($fund, $fundAttribute[$i]);
            }
        }

        return $fund;
    }

    /**
    * Unlinks FundAttribute that are in the Fund's
    * FundAttribut list, but not in the given FundAttribute.
    * Returns the updated Fund.
    *
    * @param $fund Fund to be cleaned up from obsolete FundAttributen.
    * @param array $fundAttribute FundAttribute to be used as new FundAttribute.
    */
    protected function unlinkObsoleteFundAttribute($fund, array $fundAttribute)
    {
        for ($i = 0; $i < count($fund->getFundAttribute());)
        {
            $contains = false;

            for ($j = 0; $j < count($fundAttribute); $j++)
            {
                if ($fundAttribute[$j]->getId() == $fund->getFundAttribute()[$i]->getId())
                {
                    $contains = true;
                    break;
                }
            }

            if ($contains)
            {
                $i++;
            }
            else
            {
                $fund = $this->unlinkFundAttribut($fund, $fund->getFundAttribute()[$i]);
            }
        }

        return $fund;
    }

    public function unlinkAllFundAttribute($fund)
    {
        return $this->unlinkObsoleteFundAttribute($fund, array());
    }
    #endregion

        #region User (Funde are bookmarked)
        public function loadByUserAsBookmarked($user)
        {
            $funde = array();
            $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

            if (!$mysqli->connect_errno)
            {
                $mysqli->set_charset("utf8");
                $ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByUserAsBookmarked($user));

                if (!$mysqli->errno)
                {
                    while ($datensatz = $ergebnis->fetch_assoc())
                    {
						if ($datensatz["Id"] != null)
						{
                        	array_push($funde, $this->loadById(intval($datensatz["Id"])));
						}
                    }
                }
            }

            $mysqli->close();

            return $funde;
        }

        protected function getSQLStatementToLoadIdsByUserAsBookmarked($user)
        {
            return "SELECT ".$this->getTableName()."_Id AS Id
            FROM ".$this->getUserFactory()->getTableName()."_Bookmarked".$this->getTableName()."
            WHERE ".$this->getUserFactory()->getTableName()."_Id = ".$user->getId().";";
        }
	#endregion

        #region User (Funde are rated)
        public function loadByUserAsRated($user)
        {
            $funde = array();
            $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);

            if (!$mysqli->connect_errno)
            {
                $mysqli->set_charset("utf8");
                $ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByUserAsRated($user));

                if (!$mysqli->errno)
                {
                    while ($datensatz = $ergebnis->fetch_assoc())
                    {
						if ($datensatz["Id"] != null)
						{
                        	array_push($funde, $this->loadById(intval($datensatz["Id"])));
						}
                    }
                }
            }

            $mysqli->close();

            return $funde;
        }

        protected function getSQLStatementToLoadIdsByUserAsRated($user)
        {
            return "SELECT ".$this->getTableName()."_Id AS Id
	    FROM ".$this->getUserFactory->getTableName()."_".$this->getTabeName()."IsRated
            WHERE ".$this->getUserFactory()->getTableName()."_Id = ".$user->getId().";";
        }
        #endregion

    #endregion
}
