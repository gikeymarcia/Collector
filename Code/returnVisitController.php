<?php
class ReturnVisitController
{
    private $user;
    private $jsonDir;
    private $jsonPath;
    private $oldSession;
    private $currentRow;
    private $doneLink;

    public function __construct ()
    {
        global $user;
        $this->user = $user->getUsername();

        global $_PATH;
        $this->jsonDir  = $_PATH->get('JSON Dir');
        $this->doneLink = $_PATH->get('Done');
    }

    public function isReturning()
    {   
        $this->jsonPath = $this->jsonDir . '/' . $this->user . '.json';
        if (FileExists($this->jsonPath) == true) {
            $this->loadPriorSession();
            return true;
        } else {
            return false;
        }
    }
    private function loadPriorSession()
    {
        $handle = fopen($this->jsonPath, 'r');
        $old    = fread($handle, filesize($this->jsonPath));
        $old    = json_decode($old, true);
        $pos    = $old['Position'];

        $this->oldSession = $old;
        $this->currentRow = $old['Trials'][$pos];
        $this->pos = $pos;
    }
    public function alreadyDone() {
        // header("Location: {$this->doneLink}");
        // echo '<meta http-equiv="refresh"; content="5"; url="done.php">';
        // exit;
        $doneCode = 'ExperimentFinished';
        $flag = $this->currentRow['Procedure']['Item'];
        if ($flag == $doneCode) {
            $_SESSION = $this->oldSession;
            $_SESSION['alreadyDone'] = true;
            header("Location: {$this->doneLink}");
            // Need to figure out how to redirect to done
            exit;
        }
    }
    public function timeToReturn()
    {   
        if (isset($this->currentRow['Procedure']['Min Time'])){
            $min = $this->currentRow['Procedure']['Min Time'];
        } else {
            $min = 0;
        }
        if (isset($this->currentRow['Procedure']['Max Time'])) {
            $max = $this->currentRow['Procedure']['Max Time'];
        } else {
            $max = null;
        }
        $min = $this->durationFormatted($min);

        $doneCode = 'ExperimentFinished';
        $flag = $this->currentRow['Procedure']['Item'];
        $min  = $this->currentRow['Procedure']['Min Time'];
        $max  = $this->currentRow['Procedure']['Max Time'];
        if ($flag == $doneCode) {
            // send to done
        } else {

            # code...
        }

    }

    private function durationFormatted($string)
    {

    }
}

