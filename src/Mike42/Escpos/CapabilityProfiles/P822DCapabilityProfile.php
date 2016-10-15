<?php
namespace Mike42\Escpos\CapabilityProfiles;

use Mike42\Escpos\CapabilityProfiles\CapabilityProfile;

class P822DCapabilityProfile
{
    public static function getInstance()
    {
        return CapabilityProfile::load('P822D');
    }
}
