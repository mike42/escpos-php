<?php
namespace Mike42\Escpos\CapabilityProfiles;

use Mike42\Escpos\CapabilityProfiles\CapabilityProfile;

class StarCapabilityProfile
{
    public static function getInstance()
    {
        return CapabilityProfile::load('SP2000');
    }
}
