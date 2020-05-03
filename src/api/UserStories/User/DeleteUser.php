<?php
require_once(__DIR__."/../../UserStories/UserStory.php");
require_once(__DIR__."/../../Factory/UserFactory.php");

class DeleteUser extends UserStory
{
    #region variables
    private $_user = null;
    #endregion

    #endregion properties
    #region input properties
    /**
     * Returns the given Userer, which is to delete.
     */
    private function getUser()
    {
        return $this->_user;
    }

    /**
     * Sets the User, which is to delete.
     * @param User $user User, which is to delete.
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }
    #endregion
    #endregion

    /**
     * Checks if a User is set.
     */
    protected function areParametersValid()
    {
        global $logger;
        $user = $this->getUser();
        
        if ($user == null)
        {
            $logger->warn("User ist nicht gesetzt!");
            $this->addMessage("User ist nicht gesetzt!");
            return false;
	}

        return true;
    }

    protected function execute()
    {
        $userFactory = new UserFactory();
        
        return $userFactory->delete($this->getUser());
    }
}
