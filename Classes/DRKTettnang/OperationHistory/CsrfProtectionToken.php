<?php

namespace DRKTettnang\OperationHistory;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility;

/**
 * @Flow\Scope("session")
 */
class CsrfProtectionToken {

   /**
    * @var string
    */
   protected $token = null;

   /**
    * @return string
    * @Flow\Session(autoStart = TRUE)
    */
   public function get() {
      if ($this->token == null) {
         $this->token = Utility\Algorithms::generateRandomString(32);
      }

      return $this->token;
   }

   /**
    * @param  string $t
    * @return boolean
    */
   public function verify($t) {
      return $this->token != null && $this->token == $t;
   }

   /**
    * @return void
    */
   public function delete() {
      $this->token = null;
   }
}
