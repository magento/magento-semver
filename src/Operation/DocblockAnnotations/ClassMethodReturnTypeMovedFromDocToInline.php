<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation\DocblockAnnotations;

use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodReturnTypeMovedFromDocToInline extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M136', 'M156', 'M168'],
        'interface' => ['M140', 'M140', 'M140'],
        'trait'     => ['M144', 'M157', 'M169']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M136' => Level::MAJOR,
        'M140' => Level::MAJOR,
        'M144' => Level::MAJOR,
        'M156' => Level::MINOR,
        'M157' => Level::MINOR,
        'M168' => Level::PATCH,
        'M169' => Level::MINOR
    ];

    /**
     * @var string
     */
    protected $reason = 'Method return typehint was moved from doc block annotation to in-line.';

    /**
     * Returns level of error.
     *
     * @return int
     */
    public function getLevel() : int
    {
        return $this->mapping[$this->getCode()];
    }
}
