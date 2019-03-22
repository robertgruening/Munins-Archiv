<?php
require_once(__DIR__."/../../../UserStories/UserStory.php");
require_once(__DIR__."/../../../Factory/FundAttributTypeFactory.php");

class SaveFundAttributType extends UserStory
{
    #region variables
    private $_fundAttributType = null;
    #endregion

    #endregion properties
    #region input and output properties
    public function getFundAttributType()
    {
        return $this->_fundAttributType;
    }

    public function setFundAttributType($fundAttributType)
    {
        $this->_fundAttributType = $fundAttributType;
    }
    #endregion
    #endregion

    protected function areParametersValid()
    {
        $fundAttributType = $this->getFundAttributType();
        $fundAttributTypeFactory = new FundAttributTypeFactory();
        $fundAttributTypes = $fundAttributTypeFactory->loadAll();

        for ($i = 0; $i < count($fundAttributTypes); $i++)
        {
            if ($fundAttributTypes[$i]->getBezeichnung() == $fundAttributType->getBezeichnung() &&
                ($fundAttributType->getId() == -1 ||
                 $fundAttributType->getId() != $fundAttributTypes[$i]->getId()))
            {
                $this->addMessage("Es existiert bereits ein Fundattributtyp mit der Bezeichnung \"".$fundAttributType->getBezeichnung()."\"!");
                return false;
            }
        }

        return true;
    }

    protected function execute()
    {
        $fundAttributTypeFactory = new FundAttributTypeFactory();
        $this->setFundAttributType($fundAttributTypeFactory->save($this->getFundAttributType()));

        return true;
    }
}