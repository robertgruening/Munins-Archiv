<?php
include_once(__DIR__."/INode.php");

class Fund implements iNode
{    
    public $Id;
    public $Bezeichnung;
    public $OrderNumber;
    public $Anzahl;
    public $Dimension1;
    public $Dimension2;
    public $Dimension3;
    public $Masse;
    public $FundAttribute;
    public $Ablage;
    public $Kontext;
    public $Ratings;
    public $Rating; 
    
    public function getId()
    {
        return $this->Id;
    }
    
    public function setId($id)
    {
        $this->Id = $id;
    }
    
    public function getBezeichnung()
    {
        return $this->Bezeichnung;
    }
    
    public function setBezeichnung($bezeichnung)
    {
        $this->Bezeichnung = $bezeichnung;
    }
    
    public function getOrderNumber()
    {
        $this->OrderNumber;
    }
    
    public function setOrderNumber($orderNumber)
    {
        $this->OrderNumber = $orderNumber;
    }
    
    public function getAnzahl()
    {
        return $this->Anzahl;
    }
    
    public function setAnzahl($anzahl)
    {
        $this->Anzahl = $anzahl;
    }
    
    public function getDimension1()
    {
        return $this->Dimension1;
    }
    
    public function setDimension1($dimension1)
    {
        $this->Dimension1 = $dimension1;
    }
    
    public function getDimension2()
    {
        return $this->Dimension2;
    }
    
    public function setDimension2($dimension2)
    {
        $this->Dimension2 = $dimension2;
    }
    
    public function getDimension3()
    {
        return $this->Dimension3;
    }
    
    public function setDimension3($dimension3)
    {
        $this->Dimension3 = $dimension3;
    }
    
    public function getMasse()
    {
        return $this->Masse;
    }
    
    public function setMasse($masse)
    {
        $this->Masse = $masse;
    }
    
    public function getFundAttribute()
    {
        return $this->FundAttribute;
    }
    
    public function setFundAttribute($fundAttribute)
    {
        $this->FundAttribute = $fundAttribute;
    }
    
    public function addFundAttribut($fundAttribut)
    {
        array_push($this->FundAttribute, $fundAttribut);
    }
    
    public function removeFundAttribut($fundAttribut)
    {
        for ($i = 0; $i < count($this->FundAttribute); $i++)
        {
            if ($this->FundAttribute[$i]->getId() == $fundAttribut->getId())
            {
                array_splice($this->FundAttribute, $i, 1);
                break;
            }
        }
    }
    
    public function containsFundAttribut($fundAttribut)
    {
        for ($i = 0; $i < count($this->FundAttribute); $i++)
        {
            if ($this->FundAttribute[$i]->getId() == $fundAttribut->getId())
            {
                return true;
            }
        }

        return false;
    }
    
	public function getRatings()
    {
        return $this->Ratings;
    }
    
    public function setRatings($ratings)
    {
	    $this->Ratings = $ratings;
	    $this->Rating = $this->calculateAverage($this->Ratings);
    }

    public function getRating()
    {
	    return $this->Rating;
    }
    
    public function getAblage()
    {
        return $this->Ablage;
    }
    
    public function setAblage($ablage)
    {
        $this->Ablage = $ablage;
    }
    
    public function getKontext()
    {
        return $this->Kontext;
    }
    
    public function setKontext($kontext)
    {
        $this->Kontext = $kontext;
    }

    private function calculateAverage($values)
    {
	    if ($values == null ||
	    	count($values) == 0)
	    {
		    return 0;
	    }

	    return array_sum($values) / count($values);
    }
    
    function __construct()
    {
        $this->Id = -1;
        $this->Bezeichnung = null;
        $this->OrderNumber = null;
        $this->Anzahl = null;
        $this->Dimension1 = null;
        $this->Dimension2 = null;
        $this->Dimension3 = null;
        $this->Masse = null;
        $this->FundAttribute = array();
        $this->Ablage = null;
        $this->Kontext = null;
	$this->Ratings = array();
	$this->Rating = 0;
    }
}
