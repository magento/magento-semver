<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Data;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Data Entity <var> field was added
 */
class DataEntityVarAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M232';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = '<entity> <var> element was added';
}
