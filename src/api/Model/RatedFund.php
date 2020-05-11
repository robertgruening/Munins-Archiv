<?php
include_once(__DIR__."/INode.php");

class RatedFund implements iNode
{    
    public $Id;
    public $Fund;
    public $Rating;
    
    public function getId()
    {
        return $this->Id;
    }
    
    public function setId($id)
    {
        $this->Id = $id;
    }
    
    public function getFund()
    {
        return $this->Fund;
    }
    
    public function setFund($fund)
    {
        $this->Fund = $fund;
    }
    
    public function getRating()
    {
        return $this->Rating;
    }
    
    public function setRating($rating)
    {
        $this->Rating = $rating;
    }
    
    function __construct()
    {
	$this->Id = -1;
        $this->Fund = null;
        $this->Rating = 0;
    }
}
