<?php

namespace ForestAdmin\LaravelForestAdmin\Exceptions;

/**
 * Class ForestException
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class ForestException extends \RuntimeException
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = '🌳🌳🌳 ' . $message;
        parent::__construct($message, $code, $previous);
    }
}
