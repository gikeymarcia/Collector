<?php
class ReturnVisitController
{
    private $user;
    private $jsonFile = false;

    public function __construct ($user)
    {
        $this->user = $user;
    }

    public function isReturning($jsonPath)
    {
        if (FileExists("{$jsonPath}/{$this->user}.json") == true) {
            $this->jsonFile = "{$jsonPath}/{$this->user}.json";
        }
        return FileExists("{$jsonPath}/{$this->user}.json");
    }
    private function loadPriorSession()
    {

    }
}

