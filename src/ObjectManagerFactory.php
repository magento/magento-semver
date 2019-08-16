<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;

class ObjectManagerFactory extends \Magento\Framework\App\ObjectManagerFactory
{
    /**
     * Locator class name
     *
     * @var string
     */
    protected $_locatorClassName = \Magento\SemanticVersionChecker\ObjectManager::class;

    /**
     * Config class name
     *
     * @var string
     */
    protected $_configClassName = \Magento\SemanticVersionChecker\ObjectManager\Config::class;

    /**
     * @var string
     */
    protected $envFactoryClassName = \Magento\SemanticVersionChecker\ObjectManager\EnvironmentFactory::class;

    /**
     * @var array
     */
    protected $_primaryConfigData = null;

    /**
     * Restore locator instance
     *
     * @param ObjectManager $objectManager
     * @param DirectoryList $directoryList
     * @param array $arguments
     * @return ObjectManager
     */
    public function restore(ObjectManager $objectManager, $directoryList, array $arguments)
    {
        \Magento\SemanticVersionChecker\ObjectManager::setInstance($objectManager);
        $this->directoryList = $directoryList;
        $objectManager->configure($this->_primaryConfigData);
        $objectManager->addSharedInstance($this->directoryList, \Magento\Framework\App\Filesystem\DirectoryList::class);
        $objectManager->addSharedInstance($this->directoryList, \Magento\Framework\Filesystem\DirectoryList::class);
        $deploymentConfig = $this->createDeploymentConfig($directoryList, $this->configFilePool, $arguments);
        $this->factory->setArguments($arguments);
        $objectManager->addSharedInstance($deploymentConfig, \Magento\Framework\App\DeploymentConfig::class);
        $objectManager->addSharedInstance(
            $objectManager->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class),
            \Magento\Framework\ObjectManager\ConfigLoaderInterface::class
        );
        $objectManager->get(\Magento\Framework\Interception\PluginListInterface::class)->reset();
        $objectManager->configure(
            $objectManager->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class)->load('global')
        );

        return $objectManager;
    }

    /**
     * Load primary config
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param mixed $argumentMapper
     * @param string $appMode
     * @return array
     */
    protected function _loadPrimaryConfig(DirectoryList $directoryList, $driverPool, $argumentMapper, $appMode)
    {
        if (null === $this->_primaryConfigData) {
            $this->_primaryConfigData = parent::_loadPrimaryConfig($directoryList, $driverPool, $argumentMapper, $appMode);
            $diPreferences = [];
            $diPreferencesPath = __DIR__ . '/../../../etc/di/preferences/';

            $preferenceFiles = glob($diPreferencesPath . '*.php');

            foreach ($preferenceFiles as $file) {
                if (!is_readable($file)) {
                    throw new LocalizedException(__("'%1' is not readable file.", $file));
                }
                $diPreferences = array_replace($diPreferences, include $file);
            }

//            $this->_primaryConfigData['preferences'] = array_replace(
//                $this->_primaryConfigData['preferences'],
//                $diPreferences
//            );
        }
        return $this->_primaryConfigData;
    }
}