<?php
declare(strict_types=1);
namespace Ace\RepoManUi\Remote;

use Exception;

/**
 * @author timrodger
 * Date: 29/03/15
 */
class UnavailableException extends Exception {

    public function __construct(string $message = "") {

        parent::__construct($message, 500, null);
    }
}