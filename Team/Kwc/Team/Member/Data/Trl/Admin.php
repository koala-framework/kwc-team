<?php
class Team_Kwc_Team_Member_Data_Trl_Admin extends Kwc_Admin
{
    public function componentToString(Kwf_Component_Data $data)
    {
        return $data->chained
            ->getComponent()
            ->getRow()
            ->__toString();
    }
}
