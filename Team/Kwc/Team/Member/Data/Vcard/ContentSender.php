<?php
class Team_Kwc_Team_Member_Data_Vcard_ContentSender extends Kwf_Component_Abstract_ContentSender_Abstract
{
    public function sendContent($includeMaster)
    {
        $dataRow = (object)$this->_data->parent->getComponent()->getRow()->toArray();
        if (!isset($dataRow->lastname) || !isset($dataRow->firstname)) {
            throw new Kwf_Exception_NotFound();
        }
        $dataRow = (object)$this->_data->parent->getComponent()->getRow()->toArray();
        $imageData = $this->_data->parent->parent->getChildComponent('-image');
        $this->_outputVcard($dataRow, $imageData);
    }

    /**
     * Set default vCard settings here or in Team_Component
     */
    protected function _getDefaultValues()
    {
        $teamComponent = $this->_data->parent->parent->parent;
        if (Kwc_Abstract::hasSetting($teamComponent->componentClass, 'defaultVcardValues')) {
            $setting = Kwc_Abstract::getSetting($teamComponent->componentClass, 'defaultVcardValues');
        }

        if (isset($setting)) {
            return $setting;
        } else {
            return Kwc_Abstract::getSetting($this->_data->componentClass, 'defaultVcardValues');
        }
    }

    protected function _outputVcard($dataRow, $imageData)
    {
        $content = $this->_getVcardContent($dataRow, $imageData);
        $filename = $this->_getFilename($dataRow);

        if (!$filename) $filename = 'vcard';
        header('Content-Type: text/x-vcard; charset=UTF-8');
        header('Content-Length: '.strlen($content));
        header('Content-Disposition: attachment; filename="'.$filename.'.vcf"');
        echo $content;
    }

    protected function _getFilename($dataRow)
    {
        if ($dataRow && (!empty($dataRow->firstname) || !empty($dataRow->lastname))) {
            $filename = $dataRow->lastname.'_'.$dataRow->firstname;
            $filter = new Kwf_Filter_Ascii();
            return $filter->filter($filename);
        }
        return null;
    }

    /**
     * Gibt vCard Daten zurück. Statisch weil es auch von der Trl_Component
     * aufgerufen wird.
     */
    protected function _getVcardContent($dataRow, $imageData)
    {
        $defaults = $this->_getDefaultValues();
        $charset = "UTF-8";
        $vcfCardVersion = '3.0';

        $vcard = new Team_Kwc_Team_Member_Data_Vcard_Pear_Build($vcfCardVersion);

        $vcard->setName($dataRow->lastname, $dataRow->firstname, '', $dataRow->title, '');
        $vcard->addParam('CHARSET', $charset);

        $vcard->setFormattedName($dataRow->firstname.' '.$dataRow->lastname);
        $vcard->addParam('CHARSET', $charset);

        if (isset($defaults['ORG'])) {
            $vcard->addOrganization($defaults['ORG']);
            $vcard->addParam('CHARSET', $charset);
        }
        if (!empty($dataRow->working_position)) {
            $vcard->setRole($dataRow->working_position);
            $vcard->addParam('CHARSET', $charset);
        }
        if (!empty($dataRow->phone)) {
            $vcard->addTelephone($dataRow->phone);
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('TYPE', 'PREF');
            $vcard->addParam('CHARSET', $charset);
        }
        if (!empty($dataRow->mobile)) {
            $vcard->addTelephone($dataRow->mobile, 'mobile');
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('CHARSET', $charset);
        }
        $fax = null;
        if (!empty($dataRow->fax)) {
            $fax = $dataRow->fax;
        } else if (isset($defaults['TEL;WORK;FAX'])) {
            $fax = $defaults['TEL;WORK;FAX'];
        }
        if ($fax) {
            $vcard->addTelephone($fax, 'fax');
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('CHARSET', $charset);
        }
        if (!empty($dataRow->email)) {
            $vcard->addEmail($dataRow->email);
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('CHARSET', $charset);
        }
        if (isset($defaults['URL;WORK'])) {
            $vcard->setURL($defaults['URL;WORK']);
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('CHARSET', $charset);
        }
        if (isset($defaults['NOTE'])) {
            $vcard->setNote($defaults['NOTE']);
            $vcard->addParam('CHARSET', $charset);
        }
        if (isset($defaults['ADR;WORK']) || !empty($dataRow->street) || !empty($dataRow->city) || !empty($dataRow->zip) || !empty($dataRow->country)) {
            /**
             * muss ein array mit folgenden werten liefern:
             * 0 => ''
             * 1 => ''
             * 2 => street
             * 3 => city
             * 4 => province
             * 5 => zip
             * 6 => country
             */
            $values = array();
            if (!empty($defaults['ADR;WORK'])) {
                $values = explode(';', $defaults['ADR;WORK']);
            }
            for ($i=0; $i<=6; $i++) {
                if (!isset($values[$i])) $values[$i] = '';
            }
            if (!empty($dataRow->street)) $values[2] = $dataRow->street;
            if (!empty($dataRow->city)) $values[3] = $dataRow->city;
            if (!empty($dataRow->country)) $values[4] = $dataRow->country;
            if (!empty($dataRow->zip)) $values[5] = $dataRow->zip;
            if (!empty($dataRow->country)) $values[6] = $dataRow->country;
            $vcard->addAddress($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6]);
            $vcard->addParam('TYPE', 'WORK');
            $vcard->addParam('CHARSET', $charset);
        }

        if ($imageData && $imageData->hasContent()) {
            $data = call_user_func_array(
                array($imageData->componentClass, 'getMediaOutput'),
                array($imageData->componentId, 'default', $imageData->componentClass)
            );
            $type = explode('/', $data['mimeType']);
            $type[1] = strtoupper($type[1]);
            if ($type[1] == 'PJPEG') $type[1] = 'JPEG';

            if ($type[1] == 'JPEG') {
                $vcard->setPhoto(base64_encode($data['contents']));
                if ($vcfCardVersion == '3.0') $vcard->addParam('ENCODING', 'b');
                $vcard->addParam('TYPE', $type[1]);
                if ($vcfCardVersion == '2.1') $vcard->addParam('ENCODING', 'BASE64');
            }
        }

        $vcard->setRevision(date('Y-m-d').'T'.date('H:i:s').'Z');

        return $vcard->fetch();
    }
}
