<?php
namespace Mike42\Escpos\CapabilityProfiles;

use Mike42\Escpos\CapabilityProfiles\CapabilityProfile;

class EposTepCapabilityProfile
{
    public static function getInstance()
    {
        return CapabilityProfile::load('TEP-200M');
    }
}
