<?php
class Team_Kwc_Team_Member_Data_Vcard_Trl_Component extends Kwc_Chained_Trl_Component
{
    public static function getSettings($masterComponentClass = null)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['contentSender'] = 'Team_Kwc_Team_Member_Data_Vcard_Trl_ContentSender';
        return $ret;
    }
}
