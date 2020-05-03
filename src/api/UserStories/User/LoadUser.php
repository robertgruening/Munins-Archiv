<?php
require_once(__DIR__."/../../UserStories/UserStory.php");
require_once(__DIR__."/../../Factory/UserFactory.php");

class LoadUser extends UserStory
{
    #region variables
    private $_id = null;
	private $_guid = null;
    private $_user = null;
    #endregion

    #endregion properties
    #region input properties
    public function setId($id)
    {
        $this->_id = $id;
    }

    private function getId()
    {
        return $this->_id;
    }

    public function setGuid($guid)
    {
        $this->_guid = $guid;
    }

    private function getGuid()
    {
        return $this->_guid;
    }
    #endregion

    #region output properties
    public function getUser()
    {
        return $this->_user;
    }

    private function setUser($user)
    {
        $this->_user = $user;
    }
    #endregion
    #endregion

    protected function areParametersValid()
    {
		global $logger;
		$areParametersValid = true;

        if (($this->getId() === null ||
             $this->getId() === "") &&
			($this->getGuid() === null ||
	         $this->getGuid() === ""))
        {
            $logger->warn("ID und GUID sind nicht gesetzt!");
            $this->addMessage("ID und GUID sind nicht gesetzt!");
            $areParametersValid = false;
        }

		if ($this->getId() != null &&
			$this->getId() != "")
		{
			if (!is_numeric($this->getId()) &&
				!is_int($this->getId()))
			{
            	$logger->warn("ID muss eine Zahl sein!");
            	$this->addMessage("ID muss eine Zahl sein!");
				$areParametersValid = false;
			}
		}

		if ($this->getGuid() != null &&
			$this->getGuid() != "")
		{
			if (!preg_match("/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/i", $this->getGuid()))
			{
            	$logger->warn("GUID muss eine Hexadezimalzahl im Format XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX sein!");
            	$this->addMessage("GUID muss eine Hexadezimalzahl im Format XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX sein!");
				$areParametersValid = false;
			}
		}

        return $areParametersValid;
    }

    protected function execute()
    {
        $userFactory = new UserFactory();

		if ($this->getId() != null &&
			$this->getId() != "")
		{
        	$user = $userFactory->loadById($this->getId());

	        if ($user == null)
	        {
	            global $logger;
	            $logger->warn("Es gibt keine User mit der ID ".$this->getId()."!");
	            $this->addMessage("Es gibt keine User mit der ID ".$this->getId()."!");
	            return false;
	        }
		}
		else if ($this->getGuid() != null &&
				 $this->getGuid() != "")
		{
        	$user = $userFactory->loadByGuid($this->getGuid());

	        if ($user == null)
	        {
	            global $logger;
	            $logger->warn("Es gibt keine User mit der GUID ".$this->getGuid()."!");
	            $this->addMessage("Es gibt keine User mit der GUID ".$this->getGuid()."!");
	            return false;
	        }
		}

        $this->setUser($user);

        return true;
    }
}
