<?php
namespace Mike42\Escpos\CapabilityProfiles;

use Mike42\Escpos\CapabilityProfiles\CapabilityProfile;

class SimpleCapabilityProfile
{
    public static function getInstance()
    {
        return CapabilityProfile::load('simple');
    }
}
