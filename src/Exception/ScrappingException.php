<?php

/*
 * This file is part of the RouterOS project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace RouterOS\Generator\Exception;

use RouterOS\Generator\Structure\ResourceStructure;
use Throwable;

class ScrappingException extends \Exception
{
    /**
     * @var ResourceStructure
     */
    private $resource;

    public function __construct(ResourceStructure $resource, int $code = 0, Throwable $previous = null)
    {
        $this->resource = $resource;
        $messages = [];
        foreach ($resource->getExceptions() as $exception) {
            $messages[] = $exception->getMessage();
        }

        $messages = implode("\n", $messages);
        $message = "Error when scrapping {$resource->getName()}: \n{$messages}";
        parent::__construct($message, $code, $previous);
    }
}
