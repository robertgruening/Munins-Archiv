<?php
include_once(__DIR__."/Factory.php");
include_once(__DIR__."/AblageTypFactory.php");
include_once(__DIR__."/ITreeFactory.php");
include_once(__DIR__."/TreeFactory.php");
include_once(__DIR__."/FundFactory.php");
include_once(__DIR__."/../Model/Ablage.php");

class AblageFactory extends Factory implements iTreeFactory
{
    protected function getSQLStatementToLoadById($id)
    {
        return "SELECT Id, Bezeichnung, Typ_Id
                FROM ".$this->getTableName()."
                WHERE Id = ".$id;
    }
    
    protected function fill($dataSet)
    {
        $ablage = new Ablage();
        $ablage->setId(intval($dataSet["Id"]));
        $ablage->setBezeichnung($dataSet["Bezeichnung"]);
        $ablage->setPath($this->getPath($ablage));
        
        $ablageTypFactory = new AblageTypFactory();
        $ablageTyp = $ablageTypFactory->loadById(intval($dataSet["Typ_Id"]));
        
        $ablage->setType($ablageTyp);
        
        return $ablage;
    }
    
    public function loadByKontext($kontext)
    {
        $elemente = array();
        $mysqli = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);
		
		if (!$mysqli->connect_errno)
		{
			$mysqli->set_charset("utf8");
			$ergebnis = $mysqli->query($this->getSQLStatementToLoadIdsByKontext($kontext));	
			
			if (!$mysqli->errno)
			{
				while ($datensatz = $ergebnis->fetch_assoc())
				{
					array_push($elemente, $this->loadById(intval($datensatz["Id"])));
				}
			}
		}
		
		$mysqli->close();
		
		return $elemente;
    }
    
    protected function getSQLStatementToLoadIdsByKontext($kontext)
    {
        return "SELECT Ablage_Id AS Id
                FROM ".$this->getTableName()."_Kontext
                WHERE Kontext_Id = ".$kontext->getId();
    }
    
    public function loadParent($element)
    {
        $treeFactory = new TreeFactory($this);
        $parent = $treeFactory->loadParent($element);
        $element->setParent($parent);
        
        return $element;
    }

    public function loadChildren($element)
    {
        $treeFactory = new TreeFactory($this);
        $children = $treeFactory->loadChildren($element);
        $element->setChildren($children);
        
        return $element;
    }

    public function getPath($element)
    {
        $treeFactory = new TreeFactory($this);
        
        return $treeFactory->getPath($element);
    }

    public function loadFunde($element)
    {
        $fundFactory = new FundFactory();
        $funde = $fundFactory->loadByAblage($element);
        $element->setFunde($funde);
        
        return $element;
    }
    
    public function loadRoots()
    {
        $treeFactory = new TreeFactory($this);
        
        return $treeFactory->loadRoots();
    }
    
    public function getTableName()
    {
        return "Ablage";
    }
}