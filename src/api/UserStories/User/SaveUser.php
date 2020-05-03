<?php
require_once(__DIR__."/../../UserStories/UserStory.php");
require_once(__DIR__."/../../Factory/UserFactory.php");

class SaveUser extends UserStory
{
    #region variables
    private $_user = null;
    #endregion

    #endregion properties
    #region input and output properties
    public function getUser()
    {
        return $this->_user;
    }

    public function setUser($user)
    {
        $this->_user = $user;
    }
    #endregion
    #endregion

    protected function areParametersValid()
    {
        global $logger;
        $user = $this->getUser();

        if ($user->getFirstName() == null ||
            trim($user->getFirstName()) == "")
        {
            $logger->warn("Vorname ist nicht gesetzt!");
            $this->addMessage("Vorname ist nicht gesetzt!");
            return false;
        }
        else if (strlen($user->getFirstName()) > 20)
        {
            $logger->warn("Vorname darf nicht länger als 20 Zeichen sein!");
            $this->addMessage("Vorname darf nicht länger als 20 Zeichen sein!");
            return false;
	}

        if ($user->getLastName() == null ||
            trim($user->getLastName()) == "")
        {
            $logger->warn("Nachname ist nicht gesetzt!");
            $this->addMessage("Nachname ist nicht gesetzt!");
            return false;
        }
        else if (strlen($user->getLastName()) > 30)
        {
            $logger->warn("Nachname darf nicht länger als 30 Zeichen sein!");
            $this->addMessage("Nachname darf nicht länger als 30 Zeichen sein!");
            return false;
        }

        return true;
    }

    protected function execute()
    {
        $userFactory = new UserFactory();
        $this->setUser($userFactory->save($this->getUser()));

        return true;
    }
}
