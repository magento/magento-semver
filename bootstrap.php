<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\SemanticVersionChecker\ObjectManagerFactory;

require __DIR__ . '/vendor/autoload.php';

/**
 * Shortcut for data directory
 */
define('BP', dirname(__DIR__));

$directoryList = new \Magento\Framework\App\Filesystem\DirectoryList(dirname(__DIR__));
$driverPool = new \Magento\Framework\Filesystem\DriverPool;
$configFilePool = new \Magento\Framework\Config\File\ConfigFilePool;
$objectManagerFactory = new ObjectManagerFactory($directoryList, $driverPool, $configFilePool);

//$objectManagerFactory = new ObjectManagerFactory();
$objectManager = $objectManagerFactory->create([]);
