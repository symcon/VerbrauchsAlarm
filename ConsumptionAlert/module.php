<?php

declare(strict_types=1);

class ConsumptionAlert extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger('MeterID', 0);
        $this->RegisterPropertyInteger('SmallUserInterval', 1);
        $this->RegisterPropertyInteger('LargeUserInterval', 5);
        $this->RegisterPropertyInteger('AlertThresholder', 6);
        $this->RegisterPropertyFloat('LargeUserThreshold', 0.0);
        $this->RegisterPropertyFloat('SmallUserThreshold', 0);

        //Timer
        $this->RegisterTimer('UpdateSmallUser', 0, 'VBA_CheckAlert($_IPS[\'TARGET\'], "SmallUserThreshold", "SmallUserBuffer");');
        $this->RegisterTimer('UpdateLargeUser', 0, 'VBA_CheckAlert($_IPS[\'TARGET\'], "LargeUserThreshold", "LargeUserBuffer");');

        //Variablenprofile
        //AlertLevel
        if (!IPS_VariableProfileExists('VBA.UseLevel')) {
            IPS_CreateVariableProfile('VBA.UseLevel', 1);
            IPS_SetVariableProfileValues('VBA.UseLevel', 0, 6, 1);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 0, $this->Translate('No activity'), 'IPS', 0x80FF80);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 1, $this->Translate('Everything fine'), 'HollowArrowUp', 0x00FF00);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 2, $this->Translate('Normal activity'), 'HollowDoubleArrowUp', 0x008000);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 3, $this->Translate('High activity'), 'Lightning', 0xFFFF00);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 4, $this->Translate('Abnormal activity'), 'Mail', 0xFF8040);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 5, $this->Translate('Pre-Alarm'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('VBA.UseLevel', 6, $this->Translate('Alarm triggered'), 'Alert', 0x800000);
        }

        //BorderValue
        if (!IPS_VariableProfileExists('VBA.ThresholdValue')) {
            IPS_CreateVariableProfile('VBA.ThresholdValue', 2);
            IPS_SetVariableProfileIcon('VBA.ThresholdValue', 'Distance');
            IPS_SetVariableProfileDigits('VBA.ThresholdValue', 1);
            IPS_SetVariableProfileValues('VBA.ThresholdValue', 0, 250, 0.5);
        }

        $this->RegisterVariableInteger('SmallUser', $this->Translate('Small user'), 'VBA.UseLevel');

        $this->RegisterVariableBoolean('LargeUser', $this->Translate('Large user'), '~Alert');

        $this->RegisterVariableBoolean('Alert', $this->Translate('Alert'), '~Alert');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $sourceID = $this->ReadPropertyInteger('MeterID');
        if ($sourceID != 0) {
            $MeterValue = GetValue($sourceID);
            $this->SetBuffer('SmallUserBuffer', json_encode($MeterValue));
            $this->SetBuffer('LargeUserBuffer', json_encode($MeterValue));
            $this->SetTimerInterval('UpdateSmallUser', $this->ReadPropertyInteger('SmallUserInterval') * 60 * 1000);
            $this->SetTimerInterval('UpdateLargeUser', $this->ReadPropertyInteger('LargeUserInterval') * 60 * 1000);
        }

        //Deleting references
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }
        //Add reference
        if (IPS_VariableExists($sourceID)) {
            $this->RegisterReference($sourceID);
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'SmallUserThreshold':
            case 'LargeUserThreshold':
                //Neuen Wert in die Statusvariable schreiben
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;

            default:
                throw new Exception($this->Translate('Invalid Ident'));
        }
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));
        //Options for AlertThresholder
        $var = IPS_GetVariable($this->GetIDForIdent('SmallUser'));
        if ($var['VariableCustomProfile'] != '') {
            $profile = IPS_GetVariableProfile($var['VariableCustomProfile']);
        } else {
            $profile = IPS_GetVariableProfile($var['VariableProfile']);
        }
        $assosiations = $profile['Associations'];
        for ($i = 0; $i < count($assosiations); $i++) {
            $option[] = ['caption' => $assosiations[$i]['Name'], 'value' => $assosiations[$i]['Value']];
        }
        $data->elements[4]->options = $option;

        //Suffix for the thresholds
        if ($this->ReadPropertyInteger('MeterID') != 0) {
            $var = IPS_GetVariable($this->ReadPropertyInteger('MeterID'));
            if ($var['VariableCustomProfile'] != '') {
                $profile = IPS_GetVariableProfile($var['VariableCustomProfile']);
            } else {
                $profile = IPS_GetVariableProfile($var['VariableProfile']);
            }
            //Small Threasholder
            $data->elements[3]->suffix = $profile['Suffix'];
            $data->elements[3]->digits = $profile['Digits'];
            //Large Thresholder
            $data->elements[7]->suffix = $profile['Suffix'];
            $data->elements[7]->digits = $profile['Digits'];
        }

        return json_encode($data);
    }

    public function UpdateSuffix(int $meterID)
    {
        //Get suffix and digits
        $var = IPS_GetVariable($meterID);
        if ($var['VariableCustomProfile'] != '') {
            $profile = IPS_GetVariableProfile($var['VariableCustomProfile']);
        } else {
            $profile = IPS_GetVariableProfile($var['VariableProfile']);
        }
        $suffix = $profile['Suffix'];
        $digits = $profile['Digits'];

        //UpdateFormfield
        $this->UpdateFormField('SmallUserThreshold', 'suffix', $suffix);
        $this->UpdateFormField('LargeUserThreshold', 'suffix', $suffix);
        $this->UpdateFormField('SmallUserThreshold', 'digits', $digits);
        $this->UpdateFormField('LargeUserThreshold', 'digits', $digits);
    }

    public function CheckAlert(string $ThresholdName, string $BufferName)
    {
        $MeterValue = GetValue($this->ReadPropertyInteger('MeterID'));
        $ValueOld = json_decode($this->GetBuffer($BufferName));

        // if Threshold is exceeded -> Set Alert
        if (($MeterValue - $ValueOld) > $this->ReadPropertyFloat($ThresholdName)) {
            if ($ThresholdName == 'SmallUserThreshold') {
                SetValue($this->GetIDForIdent('SmallUser'), GetValueInteger($this->GetIDForIdent('SmallUser')) + 1);
                $this->SetBuffer($BufferName, json_encode($MeterValue));
            } elseif ($this->ReadPropertyFloat($ThresholdName) != 0) {
                SetValue($this->GetIDForIdent('LargeUser'), true);
                $this->SetBuffer($BufferName, json_encode($MeterValue));
            }

            // if SmallUser is over the AlertThresholder or LargeUser is true -> send Alert
            if (GetValue($this->GetIDForIdent('SmallUser')) > $this->ReadPropertyInteger('AlertThresholder') || GetValue($this->GetIDForIdent('LargeUser'))) {
                SetValueBoolean($this->GetIDForIdent('Alert'), true);
            }
        }
        // if Threshold is not exceeded -> reset Alert
        else {
            if ($ThresholdName == 'SmallUserThreshold') {
                SetValue($this->GetIDForIdent('SmallUser'), 0);
                $this->SetBuffer($BufferName, json_encode($MeterValue));
            } elseif ($this->ReadPropertyFloat($ThresholdName) != 0) {
                SetValue($this->GetIDForIdent('LargeUser'), false);
                $this->SetBuffer($BufferName, json_encode($MeterValue));
            }
            //reset the Alert
            if (GetValue($this->GetIDForIdent('SmallUser')) < $this->ReadPropertyInteger('AlertThresholder') || !GetValue($this->GetIDForIdent('LargeUser'))) {
                SetValueBoolean($this->GetIDForIdent('Alert'), false);
            }
        }
    }
}