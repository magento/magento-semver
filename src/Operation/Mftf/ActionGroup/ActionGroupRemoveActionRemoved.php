<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * ActionGroup remove action was removed
 */
class ActionGroupRemoveActionRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M406';

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
    protected $reason = '<actionGroup> <remove action> was removed';
}
