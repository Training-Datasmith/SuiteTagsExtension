<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite\Exception;

use Behat\Testwork\Exception\Testwork_Exception;
final class Suite_Filtration_Exception extends \InvalidArgumentException implements Testwork_Exception
{
    public function __construct(string $message, \Throwable $previous_exception = null)
    {
        parent::__construct($message, 0, $previous_exception);
    }
}