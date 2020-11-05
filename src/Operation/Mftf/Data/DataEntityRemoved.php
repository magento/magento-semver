<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Data;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Data Entity was removed from the Module
 */
class DataEntityRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M205';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Entity was removed';
}
