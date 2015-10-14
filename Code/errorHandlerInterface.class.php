<?php

    interface ErrorHandlerInterface
    {
      public function add($errormessage, $showstopper);
      public function count();
      public function printErrors();
    }