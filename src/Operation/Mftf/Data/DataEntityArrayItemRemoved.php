<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Data;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Data Entity <array> <item> field was removed
 */
class DataEntityArrayItemRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M207';

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
    protected $reason = 'Entity <array> <item> element was removed';
}
