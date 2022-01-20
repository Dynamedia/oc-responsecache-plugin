<?php namespace Dynamedia\ResponseCache\Classes\Exceptions;


/**
 * Class CacheDirectoryPathNotSetException
 * @package Dynamedia\ResponseCache\Classes\Exceptions
 */
class CacheDirectoryPathNotSetException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'Cache path not set.';
}
