<?php
include_once(__DIR__."/INode.php");

class UserRating implements iNode
{
	public $Id;
    public $User;
    public $Rating;
    
    
    public function getId()
    {
        return $this->Id;
    }
    
    public function setId($id)
    {
        $this->Id = $id;
    }
    
    public function getUser()
    {
        return $this->User;
    }
    
    public function setUser($user)
    {
        $this->User = $user;
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
        $this->User = null;
        $this->Rating = null;
    }
}
