<?php
require_once(__DIR__."/../../UserStories/UserStory.php");
require_once(__DIR__."/../../Factory/UserFactory.php");

class LoadUsers extends UserStory
{
    #region variables
    private $_users = array();
    #endregion

    #endregion properties
    #region output properties
    public function getUsers()
    {
        return $this->_users;
    }

    private function setUsers($users)
    {
        $this->_users = $users;
    }
    #endregion
    #endregion

    protected function areParametersValid()
    {
        return true;
    }

    protected function execute()
    {
        $userFactory = new UserFactory();
        $users = $userFactory->loadAll();
        $this->setUsers($users);

        return true;
    }
}
