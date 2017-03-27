<?php
class Team_Kwc_Team_Component extends Kwc_Abstract_List_Component
{
    public static function getSettings($param = null)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Team');
        $ret['componentIcon'] = 'image';
        $ret['generators']['child']['component'] = 'Team_Kwc_Team_Member_Component';
        $ret['generators']['child']['class'] = 'Team_Kwc_Team_MemberGenerator';
        $ret['extConfig'] = 'Kwc_Abstract_List_ExtConfigList';

        // möglich zu überschreiben für vcards
        // $ret['defaultVcardValues'] = array();
        return $ret;
    }
}
