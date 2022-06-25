<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpWhitespaceFilterTest;
class FilteredChangesRemain
{
/**
* This class has whitespace removed
*/
/**
* After applying the whitespace filter
*/
//But also
//   Some content that has changed
public function afterFunction() {
return 'some value';
}
/**
*
*/
/**
*
*
* The result files should have
* Whitespace that matches
*
*
*/
/**
* but changes still in place
*
*
*/
}