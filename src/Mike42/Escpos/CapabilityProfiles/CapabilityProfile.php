<?php

namespace Mike42\Escpos\CapabilityProfiles;

use Mike42\Escpos\CodePage;

class CapabilityProfile
{
    protected static $encodings = null;

    protected static $profiles = null;
    
    private $features;
    
    private $profileId;
    
    private $name;
    
    private $notes;
    
    private $colors;
    
    private $fonts;
    
    private $media;
    
    private $codePages;

    private $codePageCacheKey;

    protected function __construct($profileId, array $profileData)
    {
        // Basic primitive fields
        $this -> profileId = $profileId;
        $this -> name = $profileData['name'];
        $this -> notes = $profileData['notes'];
        // More complex fields that are not currently loaded into custom objects
        $this -> features = $profileData['features'];
        $this -> colors = $profileData['colors'];
        $this -> fonts = $profileData['fonts'];
        $this -> media = $profileData['media'];
        // More complex fields that are loaded into custom objects
        $this -> codePages = array();
        $this -> codePageCacheKey = md5(json_encode($profileData['codePages']));
        foreach ($profileData['codePages'] as $k => $v) {
            $this -> codePages[$k] = new CodePage($v, self::$encodings[$v]);
        }
    }

    public function getId()
    {
        return $this -> profileId;
    }
    
    public function getName()
    {
        return $this -> name;
    }

    public function getCodePages()
    {
        return $this -> codePages;
    }

    public function getSupportsBarcodeB()
    {
        return $this -> getFeature('barcodeB') === true;
    }

    public function getSupportsBitImageRaster()
    {
        return $this -> getFeature('bitImageRaster') === true;
    }
    
    public function getSupportsGraphics()
    {
        return $this -> getFeature('graphics') === true;
    }

    public function getSupportsQrCode()
    {
        return $this -> getFeature('qrCode') === true;
    }

    public function getSupportsPdf417Code()
    {
        // TODO get this into the upstream profile code
        return $this -> getFeature('qrCode') === true;
    }

    public function getSupportsStarCommands()
    {
        return $this -> getFeature('starCommands') === true;
    }
    
    public function getCodePageCacheKey()
    {
        return $this -> codePageCacheKey;
    }

    public function getFeature($featureName)
    {
        if (isset($this -> features[$featureName])) {
            return $this -> features[$featureName];
        }
        $suggestionsArr = $this -> suggestFeatureName($featureName);
        $suggestionsStr = implode(", ", $suggestionsArr);
        throw new \InvalidArgumentException("The feature '$featureName' does not exist. Try one that does exist, such as $suggestionsStr");
    }

    public static function load($profileName)
    {
        self::loadCapabilitiesDataFile();
        if (!isset(self::$profiles[$profileName])) {
            $suggestionsArray = self::suggestProfileName($profileName);
            $suggestionsStr = implode(", ", $suggestionsArray);
            throw new \InvalidArgumentException("The CapabilityProfile '$profileName' does not exist. Try one that does exist, such as $suggestionsStr.");
        }
        return new CapabilityProfile($profileName, self::$profiles[$profileName]);
    }

    public static function getProfileNames()
    {
        self::loadCapabilitiesDataFile();
        return array_keys(self::$profiles);
    }

    protected static function loadCapabilitiesDataFile()
    {
        if (self::$profiles === null) {
            $filename = dirname(__FILE__) . "/resources/capabilities.json";
            $capabilitiesData = json_decode(file_get_contents($filename), true);
            self::$profiles = $capabilitiesData['profiles'];
            self::$encodings = $capabilitiesData['encodings'];
        }
    }

    protected function suggestFeatureName($featureName)
    {
        return self::suggestNearest($featureName, array_keys($this -> features), 3);
    }
    
    protected static function suggestProfileName($profileName)
    {
        // TODO http://php.net/manual/en/function.levenshtein.php to find profile names with small edit distances, and suggest those.
        $suggestions = self::suggestNearest($profileName, array_keys(self::$profiles), 3);
        $alwaysSuggest = array('simple', 'default');
        foreach ($alwaysSuggest as $item) {
            if (array_search($item, $suggestions) === false) {
                array_push($suggestions, $item);
            }
        }
        return $suggestions;
    }
    
    protected static function suggestNearest($input, array $choices, $num)
    {
        return array();
    }
}
