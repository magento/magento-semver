<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Data;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Data Entity <required-entity> field was removed
 */
class DataEntityReqEntityRemove extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M209';

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
    protected $reason = 'Entity <required-entity> element was removed';
}
