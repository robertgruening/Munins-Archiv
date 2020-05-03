<?php
include_once(__DIR__."/INode.php");
include_once(__DIR__."/IListNode.php");

class User implements iNode, iListNode
{    
	public $Id;
	public $FirstName;
	public $LastName;
    	public $Guid;	
	public $OrderNumber;
    
    
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
        return $this->getFirstName()." ".$this->getLastName();
    }

    public function setBezeichnung($bezeichnung)
    {
    }

    public function getFirstName()
    {
	    return $this->FirstName;
    }

    public function setFirstName($firstName)
    {
	    $this->FirstName = $firstName;
    }

    public function getLastName()
    {
	    return $this->LastName;
    }

    public function setLastName($lastName)
    {
	    $this->LastName = $lastName;
    }
    
    public function getOrderNumber()
    {
        $this->OrderNumber;
    }
    
    public function setOrderNumber($orderNumber)
    {
        $this->OrderNumber = $orderNumber;
    }

    public function getGuid()
    {
	    return $this->Geuid;
    }

    public function setGuid($guid)
    {
	    $this->Guid = $guid;
    }
    
    function __construct()
    {
        $this->Id = -1;
	$this->FirstName = null;
	$this->LastName = null;
	$this->Guid = null;
	$this->OrderNumber = null;
    }
}
