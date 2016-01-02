<?php namespace Ace\RepoManUi\Remote;

use Exception;

/**
 * @author timrodger
 * Date: 29/03/15
 */
class UnavailableException extends Exception {

    public function __construct($message = "") {

        parent::__construct($message, 500, null);
    }
}