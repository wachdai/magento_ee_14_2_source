<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Model_Resource_Sysreport_Tool
{
    /**
     * Report tool version
     */
    const REPORT_CLASS_VERSION = '1.9.0';

    /**
     * Earliest supported magento enterprise version
     */
    const REPORT_EARLIEST_SUPPORTED_MAGENTO_VERSION = '1.12.0.0';

    /**
     * Sysreport tool error messages/debug log file
     */
    const REPORT_LOG_FILE = 'sysreport.log';

    /**
     * Core files check sum local data file mask
     */
    const REPORT_FILES_CHECK_SUM_LOCAL_DATA_FILE_MASK = 'core_files_checksum_%s_v%s.local';

    /**
     * Core files check sum reference data file mask
     */
    const REPORT_FILES_CHECK_SUM_REF_DATA_FILE_MASK = 'core_files_checksum_%s_v%s.ref';

    /**
     * DB Structure snapshot local file mask
     */
    const REPORT_DB_STRUCTURE_SNAPSHOT_LOCAL_FILE_MASK = 'db_structure_snapshot_%s_v%s.local';

    /**
     * DB Structure snapshot reference file mask
     */
    const REPORT_DB_STRUCTURE_SNAPSHOT_REF_FILE_MASK = 'db_structure_snapshot_%s_v%s.ref';

    /**
     * Maintenance mode flag file name
     */
    const REPORT_MAINTENANCE_MODE_FLAG_FILE_NAME = 'maintenance.flag';

    /**
     * Applied patches list file name
     */
    const REPORT_APPLIED_PATCHES_LIST_FILE_NAME = 'applied.patches.list';

    /**
     * Maximum size to read for applied patches list file
     */
    const REPORT_APPLIED_PATCHES_LIST_FILE_MAX_SIZE = 1048576; // 1MB

    /**
     * CLI Table Data Limitations
     */
    const TABLE_DATA_ROW_MAXIMUM_COUNT_FOR_OUTPUT = 1300;

    /**
     * Limitation for File Permissions report to avoid huge list of files to be displayed
     * (for ex. from var/log/ directory)
     */
    const TABLE_DATA_PERMISSIONS_REPORT_MAX_FILES_PER_DIRECTORY = 100;

    /**
     * Maximum file size which will be considered to parse log files for entries calculation
     */
    const MAX_FILE_SIZE_TO_OPEN_FOR_LOG_ENTRIES_CALC = 367001600; // 350MB

    /**
     * Number of log messages to report
     */
    const TOP_SYSTEM_LOG_MESSAGES_NUMBER_TO_REPORT = 5;
    const TOP_EXCEPTION_LOG_MESSAGES_NUMBER_TO_REPORT = 5;

    /**
     * Disable custom modules settings
     */
    const MODULE_CONFIG_FILE_MAX_SIZE = 1048576; // 1MB

    /**
     * $this->_getFilesList() files list modes
     */
    const REPORT_FILE_LIST_ALL   = 0;
    const REPORT_FILE_LIST_FILES = 1;
    const REPORT_FILE_LIST_DIRS  = 2;

    /**
     * Default priority for supported commands
     */
    const REPORT_COMMAND_DEFAULT_PRIORITY = 200;

    /**
     * Magento Root path
     *
     * @var string
     */
    protected $_rootPath;

    /**
     * Store current magento version
     *
     * @var null|string
     */
    protected $_magentoVersion = null;

    /**
     * Store current magento edition
     *
     * @var null|string
     */
    protected $_magentoEdition = 'EE';

    /**
     * Store core resource model instance
     *
     * @var null|Mage_Core_Model_Resource_Resource
     */
    protected $_resourceModel = null;

    /**
     * Store read connection object
     *
     * @var null|Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_readConnection = null;

    /**
     * Weather to save debug/log data into self::REPORT_LOG_FILE file
     *
     * @var bool
     */
    protected $_debug = true;

    /**
     * Known core namespaces and modules
     *
     * @var array
     */
    protected $_allowedCodePools = array('local', 'community');
    protected $_coreNamespaces = array('Mage', 'Zend', 'Enterprise');
    protected $_additionalCoreModules = array(
        'community' => array('Cm_RedisSession', 'Phoenix_Moneybookers', 'Find_Feed', 'Social_Facebook'),
        'local'     => array(),
        'core'      => array(),
    );

    /**
     * List of supported commands and aliases
     *
     * @var array
     */
    protected $_supportedCommands = array(
        // Main data generation commands
        'version' => array(
            'method'      => '_generateMagentoVersionData'
        ),
        'classrewrites' => array(
            'method'      => '_generateClassRewritesData'
        ),
        'classrewriteconflicts' => array(
            'method'      => '_generateClassRewriteConflictsData'
        ),
        'hardclassrewrites' => array(
            'method'      => '_generateFileRewritesData'
        ),
        'controllerrewrites' => array(
            'method'      => '_generateControllerRewritesData'
        ),
        'routerrewrites' => array(
            'method'      => '_generateRouterRewritesData'
        ),
        'configuration' => array(
            'method'      => '_generateConfigurationData'
        ),
        'localxml' => array(
            'method'      => '_generateEtcLocalXmlData'
        ),
        'etcenterprisexml' => array(
            'method'      => '_generateEtcEnterpriseXmlData'
        ),
        'etcconfigxml' => array(
            'method'      => '_generateEtcConfigXmlData'
        ),
        'environment' => array(
            'method'      => '_generateEnvironmentData'
        ),
        'mysqlstatus' => array(
            'method'      => '_generateMysqlStatusData'
        ),
        'logfiles' => array(
            'method'      => '_generateLogFilesData'
        ),
        'datacount' => array(
            'method'      => '_generateCountData'
        ),
        'corruptedfiles' => array(
            'method'      => '_generateCorruptedCoreFilesData'
        ),
        'missingfiles' => array(
            'method'      => '_generateMissingCoreFilesData'
        ),
        'newfiles' => array(
            'method'      => '_generateNewLocalFilesData'
        ),
        'patches' => array(
            'method'      => '_generateProvidedPatchesData'
        ),
        'filepermissions' => array(
            'method'      => '_generateFilePermissionsData'
        ),
        'corruptedtables' => array(
            'method'      => '_generateCorruptedCoreDbTablesData'
        ),
        'missingtables' => array(
            'method'      => '_generateMissingCoreDbTablesData',
        ),
        'newtables' => array(
            'method'      => '_generateNewDbTablesData',
        ),
        'tablesstatus' => array(
            'method'      => '_generateDbTablesStatusData'
        ),
        'dbroutines' => array(
            'method'      => '_generateDbRoutinesListData',
        ),
        'missingdbroutines' => array(
            'method'      => '_generateMissingDbRoutinesData',
        ),
        'newdbroutines' => array(
            'method'      => '_generateNewDbRoutinesData',
        ),
        'dbtriggers' => array(
            'method'      => '_generateDbTriggersListData',
        ),
        'missingdbtriggers' => array(
            'method'      => '_generateMissingDbTriggersData',
        ),
        'newdbtriggers' => array(
            'method'      => '_generateNewDbTriggersData',
        ),

        // Duplicates: categories, products, orders, users
        'categoryduplicates' => array(
            'method'      => '_generateCategoryDuplicates',
        ),
        'productduplicates' => array(
            'method'      => '_generateProductDuplicates',
        ),
        'orderduplicates' => array(
            'method'      => '_generateOrderDuplicates',
        ),
        'userduplicates' => array(
            'method'      => '_generateUserDuplicates',
        ),
        // Corrupted data
        'corruptedcategoriesdata' => array(
            'method'      => '_generateCorruptedCategoriesData',
        ),

        // Websites, Stores, Store Views
        'websitestree' => array(
            'method'      => '_generateWebsitesTreeData'
        ),
        'websiteslist' => array(
            'method'      => '_generateWebsitesData'
        ),
        'storeslist' => array(
            'method'      => '_generateStoresData'
        ),
        'storeviewslist' => array(
            'method'      => '_generateStoreViewsData'
        ),

        // Shipping and Payment methods information
        'shippingmethods' => array(
            'method'      => '_generateShippingMethodsData'
        ),
        'paymentmethods' => array(
            'method'      => '_generatePaymentMethodsData'
        ),
        'paymentsmatrix' => array(
            'method'      => '_generatePaymentsFunctionalityMatrixData'
        ),

        // Cache, Index, Compiler, Cron status
        'cachestatus' => array(
            'method'      => '_generateCacheStatusData'
        ),
        'indexstatus' => array(
            'method'      => '_generateIndexStatusData'
        ),
        'compilerstatus' => array(
            'method'      => '_generateCompilerStatusData'
        ),
        'cronstatus' => array(
            'method'      => '_generateCronStatusData'
        ),
        'cronerrors' => array(
            'method'      => '_generateCronErrorsData'
        ),
        'cronschedules' => array(
            'method'      => '_generateCronSchedulesData'
        ),

        // Events
        'allevents' => array(
            'method'      => '_generateAllEventsData'
        ),
        'coreevents' => array(
            'method'      => '_generateCoreEventsData'
        ),
        'eeevents' => array(
            'method'      => '_generateEnterpriseEventsData'
        ),
        'customevents'  => array(
            'method'      => '_generateCustomEventsData'
        ),

        // Attributes
        'entitytypes' => array(
            'method'      => '_generateAllEntityTypesData',
        ),
        'allattributes' => array(
            'method'      => '_generateAllEavAttributesData',
        ),
        'newattributes' => array(
            'method'      => '_generateNewEavAttributesData',
        ),
        'userattributes' => array(
            'method'      => '_generateUserDefinedEavAttributesData',
        ),
        'categoryattributes' => array(
            'method'      => '_generateCategoryEavAttributesData',
        ),
        'productattributes' => array(
            'method'      => '_generateProductEavAttributesData',
        ),
        'customerattributes' => array(
            'method'      => '_generateCustomerEavAttributesData',
        ),
        'customeraddressattributes' => array(
            'method'      => '_generateCustomerAddressEavAttributesData',
        ),
        'rmaitemattributes' => array(
            'method'      => '_generateRMAItemEavAttributesData',
        ),

        // Modules
        'allmodules' => array(
            'method'      => '_generateAllModulesData'
        ),
        'coremodules' => array(
            'method'      => '_generateCoreModulesData'
        ),
        'eemodules' => array(
            'method'      => '_generateEnterpriseModulesData'
        ),
        'custommodules' => array(
            'method'      => '_generateCustomModulesData'
        ),
        'disabledmodules' => array(
            'method'      => '_generateDisabledModulesData'
        ),

        // Cron jobs
        'allcronjobs' => array(
            'method'      => '_generateAllCronJobsData'
        ),
        'corecronjobs' => array(
            'method'      => '_generateCoreCronJobsData'
        ),
        'eecronjobs' => array(
            'method'      => '_generateEnterpriseCronJobsData'
        ),
        'customcronjobs' => array(
            'method'      => '_generateCustomCronJobsData'
        ),

        // Design Themes, Skins
        'themes' => array(
            'method'      => '_generateDesignThemeListData'
        ),
        'themesconfig' => array(
            'method'      => '_generateDesignThemeConfigData'
        ),
        'skins' => array(
            'method'      => '_generateDesignSkinsListData'
        ),
    );

    /**
     * Command priorities list
     *
     * @var array
     */
    protected $_commandPriorities = array(
        // Initial information
        'version'           => 40,
        'datacount'         => 50,
        'websitestree'      => 60,
        'cachestatus'       => 70,
        'indexstatus'       => 80,
        'compilerstatus'    => 90,
        'cronstatus'        => 100,
        'cronerrors'        => 110,
        'logfiles'          => 120,

        // Corruption
        'corruptedfiles'            => 130,
        'corruptedtables'           => 140,
        'corruptedcategoriesdata'   => 150,
        'categoryduplicates'        => 160,
        'productduplicates'         => 170,
        'orderduplicates'           => 180,
        'userduplicates'            => 190,

        // Customization
        'classrewrites'         => 200,
        'classrewriteconflicts' => 210,
        'hardclassrewrites'     => 230,
        'controllerrewrites'    => 240,
        'routerrewrites'        => 250,
        'custommodules'         => 260,
        'disabledmodules'       => 270,
        'patches'               => 280,
        'missingfiles'          => 290,
        'newfiles'              => 300,
        'missingtables'         => 310,
        'newtables'             => 320,
        'missingdbroutines'     => 330,
        'newdbroutines'         => 340,
        'missingdbtriggers'     => 350,
        'newdbtriggers'         => 360,
        'customevents'          => 370,
        'userattributes'        => 380,
        'newattributes'         => 390,
        'customcronjobs'        => 400,


        // System
        'environment'       => 410,
        'mysqlstatus'       => 420,
        'filepermissions'   => 430,
        'tablesstatus'      => 440,
        'dbroutines'        => 450,
        'dbtriggers'        => 460,

        // Configuration
        'configuration'     => 470,
        'themesconfig'      => 480,
        'etcenterprisexml'  => 490,
        'localxml'          => 500,
        'etcconfigxml'      => 510,
        'shippingmethods'   => 520,
        'paymentmethods'    => 530,

        // Data
        'websiteslist'              => 540,
        'storeslist'                => 550,
        'storeviewslist'            => 560,
        'entitytypes'               => 570,
        'themes'                    => 580,
        'skins'                     => 590,
        'allattributes'             => 600,
        'categoryattributes'        => 610,
        'productattributes'         => 620,
        'customerattributes'        => 630,
        'customeraddressattributes' => 640,
        'rmaitemattributes'         => 650,
        'paymentsmatrix'            => 660,
        'cronschedules'             => 670,
        'allevents'                 => 680,
        'coreevents'                => 690,
        'eeevents'                  => 700,
        'allmodules'                => 710,
        'coremodules'               => 720,
        'eemodules'                 => 730,
        'allcronjobs'               => 740,
        'corecronjobs'              => 750,
        'eecronjobs'                => 760,
    );

    /**
     * Commands list to run
     *
     * @var array
     */
    protected $_inputCommands = array();

    /**
     * List of successfully executed commands
     *
     * @var array
     */
    protected $_succeededCommands = array();

    /**
     * Contain all sysreport tool generated data
     *
     * @var array
     */
    protected $_systemReport = array();

    /**
     * Fields in local.xml file that must be hidden due to privacy
     *
     * @var array
     */
    protected $_xmlConfigRestrictedFields = array('username', 'password', 'key');

    /**
     * DB Tables to skip when generating DB tables reports
     *
     * @var array
     */
    protected $_skipDBTablesToCheck = array(
        'enterprise_support_backup',
        'enterprise_support_backup_item',
        'enterprise_support_sysreport',
    );

    /**
     * Files to skip when generating files reports
     *
     * @var array
     */
    protected $_skipFilesToCheck = array(
        'app/code/core/Enterprise/Support/*',
        'app/design/adminhtml/default/default/layout/enterprise/support.xml',
        'app/design/adminhtml/default/default/template/enterprise/support/*',
        'lib/Support/*',
        'js/enterprise/adminhtml/support.js',
        'skin/adminhtml/default/enterprise/support/*',
        'app/etc/modules/Enterprise_Support.xml',
        'app/locale/en_US/Enterprise_Support.csv',
        'shell/support/*'
    );

    /**
     * Generate supported commands and its class methods
     * Sort supported commands by their priority to order them for execution
     *
     * Instantiate core resource model and get read connection
     * Determine Magento version and check if it is supported by system report tool
     */
    public function __construct()
    {
        register_shutdown_function(array($this, 'destruct'));
        $this->_magentoVersion = Mage::getVersion();
        if (version_compare($this->_magentoVersion, self::REPORT_EARLIEST_SUPPORTED_MAGENTO_VERSION, '<')) {
            Mage::throwException(
                Mage::helper('enterprise_support')->__('You are running the sysreport tool on Magento Enterprise Edition %s. This version of Magento is currently not compatible with the tool.', $this->_magentoVersion)
            );
        }

        foreach ($this->_supportedCommands as $key => $info) {
            // Set default priority for command
            if (!array_key_exists($key, $this->_commandPriorities)) {
                $this->_supportedCommands[$key]['priority'] = self::REPORT_COMMAND_DEFAULT_PRIORITY;
            } else {
                $this->_supportedCommands[$key]['priority'] = $this->_commandPriorities[$key];
            }
        }

        uasort($this->_supportedCommands, array(__CLASS__, 'commandPriorityCompare'));
        $this->_resourceModel = Mage::getResourceSingleton('enterprise_support/sysreport');
        $this->_readConnection = $this->_resourceModel->getReadConnection();
    }

    /**
     * Clean up temporary files on shutdown
     */
    public function destruct()
    {
        $edition   = strtolower($this->_magentoEdition);
        $localFile = Mage::getBaseDir('var') . DS . 'support' . DS;
        $localFile .= sprintf(self::REPORT_FILES_CHECK_SUM_LOCAL_DATA_FILE_MASK, $edition, $this->_magentoVersion);
        if (file_exists($localFile)) {
            unlink($localFile);
        }

        $localFile = Mage::getBaseDir('var') . DS . 'support' . DS;
        $localFile .= sprintf(self::REPORT_DB_STRUCTURE_SNAPSHOT_LOCAL_FILE_MASK, $edition, $this->_magentoVersion);
        if (file_exists($localFile)) {
            unlink($localFile);
        }
    }

    /**
     * Retrieve current Magento instance host name based on base url
     *
     * @return string
     */
    public function getClientHost()
    {
        $url = 'unknown';
        if ($this->_readConnection) {
            $url = Mage::getStoreConfig('web/secure/base_url', Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        $urlInfo = $this->_parseUrl($url);
        $host = null;
        if ($urlInfo) {
            if (isset($urlInfo['host'])) {
                $host = $urlInfo['host'];
            }
            if (isset($urlInfo['ip'])) {
                $host = $urlInfo['ip'];
            }
        }
        $host = $host !== null ? $host : 'N/A';

        return $host;
    }

    /**
     * Compare method used to sort commands before execution
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return array
     */
    public function commandPriorityCompare($a, $b)
    {
        return $a['priority'] > $b['priority'];
    }

    /**
     * Run command(s) to generate system reports
     *
     * @param array $commandsList
     *
     * @return Enterprise_Support_Model_Resource_Sysreport_Tool
     */
    public function run($commandsList)
    {
        $currentDevMode = Mage::getIsDeveloperMode();
        if ($currentDevMode) {
            $this->_debug = true;
        }
        Mage::setIsDeveloperMode(true);

        $_inputCommands = $this->_setInputCommands($commandsList);

        $this->_log(null, str_repeat('=', 80));
        $this->_log(null, 'Report START');
        $this->_log(null, str_repeat('=', 80));

        /**
         * Run requested commands
         */
        foreach ($this->_supportedCommands as $cmd => $info) {
            if ((in_array($cmd, $_inputCommands)) && !empty($info['method'])) {
                $result = false;
                try {
                    $methodTitle = strtolower(preg_replace('/(.)([A-Z])/', "$1 $2", $info['method']));
                    $methodTitle = trim($methodTitle, '_');
                    $this->_log(null, 'Started ' . $methodTitle . ' [' . $cmd . ']');

                    $result = call_user_func(array(__CLASS__, $info['method']));

                    $this->_log(null, 'Finished ' . $methodTitle . ' [' . $cmd . ']');
                } catch (Exception $e) {
                    $this->_log($e);
                }

                if ($result && is_array($result)) {
                    $this->_systemReport[$cmd] = $result;
                    $this->_succeededCommands[] = $cmd;
                }
            }
        }
        $this->_log(null, str_repeat('=', 80));
        $this->_log(null, 'Report END');
        $this->_log(null, str_repeat('=', 80));

        Mage::setIsDeveloperMode($currentDevMode);

        return $this;
    }

    /**
     * Retrieve generated system report data
     *
     * @return array
     */
    public function getReport()
    {
        return $this->_systemReport;
    }

    /**
     * Getter for sysreport tool version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::REPORT_CLASS_VERSION;
    }

    /**
     * Retrieve successfully executed commands list
     *
     * @return array
     */
    public function getSucceededCommands()
    {
        return $this->_succeededCommands;
    }

    ################################################
    ###                 FILES                    ###
    ################################################

    /**
     * Generate corrupted core files report
     *
     * @return array
     */
    protected function _generateCorruptedCoreFilesData()
    {
        $checkSumData   = $this->_getFilesCheckSumData();
        $referenceData  = $checkSumData['reference_data'];
        $localData      = $checkSumData['local_data'];

        $newLocalFiles  = array_diff_key($localData, $referenceData);
        $difference     = array_diff_assoc($localData, $newLocalFiles);
        $difference     = array_diff_assoc($difference, $referenceData);
        $_data          = array_keys($difference);
        $systemReport = $data = array();

        foreach ($_data as $file) {
            $data[] = array($file);
        }

        $systemReport['Modified Core Files'] = array(
            'header' => array('File'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate missing core files report
     *
     * @return array
     */
    protected function _generateMissingCoreFilesData()
    {
        $checkSumData     = $this->_getFilesCheckSumData();
        $referenceData    = $checkSumData['reference_data'];
        $localData        = $checkSumData['local_data'];

        $absentLocalFiles = array_diff_key($referenceData, $localData);
        $_data            = array_keys($absentLocalFiles);
        $systemReport = $data = array();

        foreach ($_data as $file) {
            $data[] = array($file);
        }

        $systemReport['Missing Core Files'] = array(
            'header' => array('File'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate new local files report
     *
     * @return array
     */
    protected function _generateNewLocalFilesData()
    {
        $checkSumData   = $this->_getFilesCheckSumData();
        $referenceData  = $checkSumData['reference_data'];
        $localData      = $checkSumData['local_data'];

        $newLocalFiles  = array_diff_key($localData, $referenceData);
        $data = $firstElements = array();
        $filesByPriority = array(
            'app' . DS . 'code' . DS => array(),
            'app' . DS . 'design' . DS => array(),
            'lib' . DS => array(),
            'js' . DS => array(),
            'skin' . DS => array(),
            '__other__files__' => array(),
        );
        $maxFilesToDisplayPerPath = floor(self::TABLE_DATA_ROW_MAXIMUM_COUNT_FOR_OUTPUT / sizeof($filesByPriority));
        foreach ($newLocalFiles as $fileName => $sum) {
            if (in_array($fileName, $this->_skipFilesToCheck)) {
                continue;
            }
            foreach ($this->_skipFilesToCheck as $_path) {
                if (substr($_path, -1) != '*') {
                    continue;
                }
                $_path = substr($_path, 0, -1);
                if (strpos($fileName, $_path) !== false) {
                    continue 2;
                }
            }

            $prioritized = false;
            foreach ($filesByPriority as $path => $files) {
                if (sizeof($filesByPriority[$path]) >= $maxFilesToDisplayPerPath) {
                    continue;
                }
                if (substr($fileName, 0, strlen($path)) == $path) {
                    $filesByPriority[$path][] = $fileName;
                    $prioritized = true;
                    break;
                }
            }
            if (!$prioritized) {
                $filesByPriority['__other__files__'][] = $fileName;
            }
        }

        foreach ($filesByPriority as $files) {
            foreach ($files as $file) {
                $data[] = array($file);
            }
        }

        $systemReport = array();
        $systemReport['New Files'] = array(
            'header' => array('File'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Get files check sum data
     *
     * @return array
     * @throws Exception
     */
    protected function _getFilesCheckSumData()
    {
        $edition        = strtolower($this->_magentoEdition);
        $refPath        = Mage::getBaseDir('lib') . DS . 'Support' . DS . 'ref' . DS;
        $referenceFile  = sprintf(self::REPORT_FILES_CHECK_SUM_REF_DATA_FILE_MASK, $edition, $this->_magentoVersion);
        $localPath      = Mage::getBaseDir('var') . DS . 'support' . DS;
        $localFile      = sprintf(self::REPORT_FILES_CHECK_SUM_LOCAL_DATA_FILE_MASK, $edition, $this->_magentoVersion);

        if (!is_readable($refPath . $referenceFile)) {
            throw new Exception(
                $referenceFile . ' doesn\'t exist or it is not readable. Checking files can\'t be performed.'
            );
        }
        if (!file_exists($localPath . $localFile)) {
            $this->_generateLocalFilesCheckSumData();
        }
        if (!is_readable($localPath . $localFile)) {
            throw new Exception(
                $localFile . ' wasn\'t generated or it is not readable. Checking files can\'t be performed.'
            );
        }

        $referenceData = unserialize(file_get_contents($refPath . $referenceFile));
        $localData     = unserialize(file_get_contents($localPath . $localFile));
        if (!is_array($referenceData) || !is_array($localData)
            || !array_key_exists('file_hashes', $referenceData) || !array_key_exists('file_hashes', $localData)
        ) {
            throw new Exception(
                'Sha1 hash data is corrupted. Checking files can\'t be performed.'
            );
        }

        return array('reference_data' => $referenceData['file_hashes'], 'local_data' => $localData['file_hashes']);
    }

    /**
     * Generate sha1 hashes for local files
     *
     * @return bool
     * @throws Exception
     */
    protected function _generateLocalFilesCheckSumData()
    {
        $path = Mage::getBaseDir('var') . DS . 'support' . DS;
        $edition = strtolower($this->_magentoEdition);
        $filename = sprintf(self::REPORT_FILES_CHECK_SUM_LOCAL_DATA_FILE_MASK, $edition, $this->_magentoVersion);

        if (!is_dir($path)) {
            mkdir($path);
            chmod($path, 0777);
        }

        if (!is_writable($path)) {
            throw new Exception(
                'Can\'t write to var' . DS . 'support' . DS
                . ' directory. Local files check sum data wasn\'t generated.'
            );
        }

        $result = $this->_getRecursiveCheckSum($this->_getRootPath(), true);
        $filesNumber = sizeof($result);
        $this->_log(null, 'Got sha1 sum for "' . $filesNumber . '" files.');
        $writtenBytes = file_put_contents($path . $filename, serialize(array('file_hashes' => $result)));
        $this->_log(null,
            ($writtenBytes !== false
                ? 'File "' . $filename . '" was successfully generated.'
                : 'File "' . $filename . '" wasn\'t generated!')
        );

        return $writtenBytes !== false;
    }

    /**
     * Get files check sum recursively
     *
     * @param string $directory
     * @param bool $resetStaticData
     *
     * @return array
     */
    protected function _getRecursiveCheckSum($directory, $resetStaticData = false)
    {
        static $filesNumber = 0;
        if ($resetStaticData) {
            $filesNumber = 0;
        }
        $result = array();
        $iterator = new DirectoryIterator($directory);
        $rootDirectory = $this->_getRootPath();

        /** @var $file SplFileInfo */
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            $filePath = $file->getPathname();
            $relativePath = str_replace($rootDirectory, '', $filePath);

            if ($fileName == 'Thumbs.db'
                || $relativePath == 'app' . DS . 'etc' . DS . 'local.xml'
                || $relativePath == 'app' . DS . 'etc' . DS . 'modules' .
                DS . 'XEnterprise_Enabler.xml'
                || in_array($fileName, array('.', '..', '.git', '.svn', '.gitignore', '.idea'))
                || (substr($relativePath, 0, 1) == '.' && $fileName != '.htaccess' && $fileName != '.htaccess.sample')
                // All files that are not in app/, lib/, js/, shell/, skin/ directories (except root files)
                || (strpos($relativePath, 'app' . DS) !== 0
                    && strpos($relativePath, 'js' . DS) !== 0
                    && strpos($relativePath, 'lib' . DS) !== 0
                    && strpos($relativePath, 'shell' . DS) !== 0
                    && strpos($relativePath, 'skin' . DS) !== 0
                    && $relativePath != $fileName)
            ) {
                continue;
            }

            if ($file->isFile()) {
                // Convert path to Unix style (because checking files method is using Unix style paths)
                $relativePath = str_replace('\\', '/', $relativePath);
                $result[$relativePath] = is_readable($filePath) ? sha1_file($filePath) : '0';

                $filesNumber++;
                if ($filesNumber % 1500 == 0) {
                    $this->_log(null, $filesNumber . ' files processed...');
                }
            } else if ($file->isDir()) {
                $_result = (array) $this->_getRecursiveCheckSum($filePath);
                $result = array_merge($result, $_result);
            }
        }

        return $result;
    }

    /**
     * Generate provided patches report
     *
     * @return array
     * @throws Exception
     */
    protected function _generateProvidedPatchesData()
    {
        clearstatcache();
        $appliedPatchesData = $systemReport = array();

        $appliedPatchesListFile = Mage::getBaseDir('etc') . DIRECTORY_SEPARATOR
            . self::REPORT_APPLIED_PATCHES_LIST_FILE_NAME;
        if (is_file($appliedPatchesListFile) && is_readable($appliedPatchesListFile)
            && $this->_getFileSize($appliedPatchesListFile) < self::REPORT_APPLIED_PATCHES_LIST_FILE_MAX_SIZE
        ) {
            try {
                $appliedListData = file($appliedPatchesListFile);
                foreach ($appliedListData as $line) {
                    $data = explode('|', $line);
                    $data = array_map('trim', $data);
                    if (sizeof($data) < 7) {
                        continue;
                    }
                    $appliedPatchesData[] = array(
                        $data[0],
                        $data[1],
                        $data[3],
                        $data[2],
                        isset($data[7]) ? 'Yes' : 'No',
                        $data[4],
                    );
                }
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        $systemReport['Applied Solutions List'] = array(
            'header' => array('Date', 'Solution', 'Solution Version', 'Magento Version', 'Reversion', 'Commit'),
            'data' => $appliedPatchesData
        );

        $baseDir = $this->_getRootPath();
        $filesList = $this->_getFilesList($baseDir, 1, true, array(), '^.*\.patch$');
        $filesList = array_merge(
            $filesList, $this->_getFilesList($baseDir, 1, self::REPORT_FILE_LIST_FILES, array(), '^PATCH.+\.sh$')
        );
        $baseDirNameLength = strlen($baseDir);
        $data = array();

        foreach ($filesList as $file) {
            $data[] = array(
                substr($file, $baseDirNameLength),
                $this->_formatBytes($this->_getFileSize($file), 3, 'IEC'),
                date('r', filemtime($file))
            );
        }

        $systemReport['Patch Files List'] = array(
            'header' => array('Patch', 'Size', 'Last Update'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate files permissions report
     *
     * @return array
     * @throws Exception
     */
    protected function _generateFilePermissionsData()
    {
        clearstatcache();
        $baseDir = $this->_getRootPath();
        $openDirList = array(
            'app',
            'app' . DS . 'etc',
            'media',
            'shell',
            'var',
            'var' . DS . 'locks',
            'var' . DS . 'log',
        );
        foreach ($openDirList as &$directory) {
            $directory = $baseDir . $directory;
        }

        $filesList = $this->_getFilesList($baseDir, 2, self::REPORT_FILE_LIST_ALL, $openDirList);
        $baseDirNameLength = strlen($baseDir);
        $rootFiles = $restFiles = array();
        sort($filesList);
        foreach ($filesList as $file) {
            $fileParts = explode('/', substr($file, $baseDirNameLength));
            if (is_file($file) && sizeof($fileParts) == 1) {
                $rootFiles[] = $file;
            } else {
                $restFiles[] = $file;
            }
        }
        $filesList = array_merge($restFiles, $rootFiles);

        $data = array();
        $everyFilesNumber = ceil(count($filesList) / 50);

        $filesNumber = 0;
        $directoryFilesCounter = array();
        foreach ($filesList as $file) {
            try {
                $filesNumber++;
                if ($filesNumber % ($everyFilesNumber * 8) == 0) {
                    $this->_log(null, $filesNumber . ' files processed...');
                }

                $fileName       = substr($file, $baseDirNameLength);
                $path           = dirname($fileName);
                $fileParts      = explode('/', $fileName);
                $fileName       = array_pop($fileParts);
                $pathPartsSize  = sizeof($fileParts);
                $fileName       = str_repeat('    ', $pathPartsSize) . $fileName .
                    (is_dir($file) ? DIRECTORY_SEPARATOR : '');

                if (!isset($directoryFilesCounter[$path])) {
                    $directoryFilesCounter[$path] = 0;
                }
                $directoryFilesCounter[$path]++;
                if ($directoryFilesCounter[$path] > self::TABLE_DATA_PERMISSIONS_REPORT_MAX_FILES_PER_DIRECTORY
                    && $path != '.' && $path != '..'
                ) {
                    continue;
                }

                $data[] = array(
                    $fileName . ($this->_isLink($file) ? ' -> ' . $this->_readlink($file) : ''),
                    $this->_parsePermissions(fileperms($file)),
                    $this->_getFileOwner($file),
                    is_file($file)
                        ? $this->_formatBytes($this->_getFileSize($file), 3, 'IEC')
                        : $this->_formatBytes($this->_getDirSize($file), 3, 'IEC'),
                    date('r', filemtime($file))
                );
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        $systemReport = array();
        $systemReport['Files Permissions'] = array(
            'header' => array('File', 'Permissions', 'Owner', 'Size', 'Last Update'),
            'data' => $data
        );

        return $systemReport;
    }

    ################################################
    ###                  DATABASE                ###
    ################################################

    /**
     * Generate DB corrupted tables report
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCorruptedCoreDbTablesData()
    {
        $tablesReport = $this->_getDbStructureData('tables');
        $tablesReport  = $this->_compareTablesStructure($tablesReport['local_data'], $tablesReport['reference_data']);
        $systemReport  = array();

        $systemReport['Modified Core Tables'] = array(
            'header' => array('Name', 'Missing Data', 'New Data', 'Corrupted Data'),
            'data' => $tablesReport['corrupted'],
        );

        return $systemReport;
    }

    /**
     * Generate DB missing tables report
     *
     * @return array
     * @throws Exception
     */
    protected function _generateMissingCoreDbTablesData()
    {
        $tablesReport = $this->_getDbStructureData('tables');
        $tablesReport = $this->_compareTablesStructure($tablesReport['local_data'], $tablesReport['reference_data']);
        $systemReport = array();

        $systemReport['Missing Core Tables'] = array(
            'header' => array('Name'),
            'data' => $tablesReport['missing']
        );

        return $systemReport;
    }

    /**
     * Generate DB new tables report
     *
     * @return array
     * @throws Exception
     */
    protected function _generateNewDbTablesData()
    {
        $tablesReport = $this->_getDbStructureData('tables');
        $tablesReport = $this->_compareTablesStructure($tablesReport['local_data'], $tablesReport['reference_data']);
        $systemReport = array();

        $systemReport['New DB Tables'] = array(
            'header' => array('Name'),
            'data' => $tablesReport['new']
        );

        return $systemReport;
    }

    /**
     * Generate stored functions and procedures list for current DB
     *
     * @return array
     * @throws Exception
     */
    protected function _generateDbRoutinesListData()
    {
        $routines = $data = array();
        try {
            $routines = $this->_getMySQLRoutinesList();
        } catch (Exception $e) {
            $this->_log($e);
        }

        if ($routines) {
            foreach ($routines as $name => $routine) {
                $data[] = array($name, $routine['type'], $routine['comment']);
            }
        }

        $systemReport = array();
        $systemReport['DB Routines List'] = array(
            'header' => array('Name', 'Type', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate missing stored functions and procedures list for current DB
     *
     * @return array
     * @throws Exception
     */
    protected function _generateMissingDbRoutinesData()
    {
        $structure      = $this->_getDbStructureData('routines');
        $routines       = array_diff_key($structure['reference_data'], $structure['local_data']);
        $systemReport   = $data = array();

        if ($routines) {
            foreach ($routines as $name => $routine) {
                $data[] = array($name, $routine['type'], $routine['comment']);
            }
        }

        $systemReport['Missing DB Routines List'] = array(
            'header' => array('Name', 'Type', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate missing stored functions and procedures list for current DB
     *
     * @return array
     * @throws Exception
     */
    protected function _generateNewDbRoutinesData()
    {
        $structure      = $this->_getDbStructureData('routines');
        $routines       = array_diff_key($structure['local_data'], $structure['reference_data']);
        $systemReport   = $data = array();

        if ($routines) {
            foreach ($routines as $name => $routine) {
                $data[] = array($name, $routine['type'], $routine['comment']);
            }
        }

        $systemReport['New DB Routines List'] = array(
            'header' => array('Name', 'Type', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate triggers list for current DB
     *
     * @return array
     */
    protected function _generateDbTriggersListData()
    {
        $triggers = $data = array();
        try {
            $triggers = $this->_getMySQLTriggersList();
        } catch (Exception $e) {
            $this->_log($e);
        }

        if ($triggers) {
            foreach ($triggers as $name => $trigger) {
                $data[] = array($name, $trigger['comment']);
            }
        }

        $systemReport = array();
        $systemReport['DB Triggers List'] = array(
            'header' => array('Name', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate missing triggers list for current DB
     *
     * @return array
     * @throws Exception
     */
    protected function _generateMissingDbTriggersData()
    {
        $structure      = $this->_getDbStructureData('triggers');
        $triggers       = array_diff_key($structure['reference_data'], $structure['local_data']);
        $systemReport   = $data = array();

        if ($triggers) {
            foreach ($triggers as $name => $trigger) {
                $data[] = array($name, $trigger['comment']);
            }
        }

        $systemReport['Missing DB Triggers List'] = array(
            'header' => array('Name', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate new triggers list for current DB
     *
     * @return array
     * @throws Exception
     */
    protected function _generateNewDbTriggersData()
    {
        $structure      = $this->_getDbStructureData('triggers');
        $triggers       = array_diff_key($structure['local_data'], $structure['reference_data']);
        $systemReport   = $data = array();

        if ($triggers) {
            foreach ($triggers as $name => $trigger) {
                $data[] = array($name, $trigger['comment']);
            }
        }

        $systemReport['New DB Triggers List'] = array(
            'header' => array('Name', 'Comment'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Compare local tables structure to reference (clean installation) and retrieve report
     *
     * @param array $localTablesStructure
     * @param array $referenceTablesStructure
     *
     * @return array
     */
    protected function _compareTablesStructure(array $localTablesStructure, array $referenceTablesStructure)
    {
        $corruptedLocalTablesReport = $missingTablesReport = $newTablesReport = array();

        // Collecting tables that are exist in local installation but not exist on clean native magento - New Tables
        $_newTablesReport = array_diff(array_keys($localTablesStructure), array_keys($referenceTablesStructure));
        foreach ($_newTablesReport as $table) {
            if (in_array($table, $this->_skipDBTablesToCheck)) {
                continue;
            }
            $newTablesReport[] = array($table);
        }

        // Collecting differences in tables structures
        foreach ($referenceTablesStructure as $table => $tableProp) {
            try {
                if (!isset($localTablesStructure[$table])) {
                    $missingTablesReport[] = array($table);
                } else {
                    $data = array();
                    $tableProp['keys_structure'] = array();
                    $tableProp['constraints_structure'] = array();
                    $localTablesStructure[$table]['keys_structure'] = array();
                    $localTablesStructure[$table]['constraints_structure'] = array();

                    // Compare columns
                    // Missing
                    $list = array_diff_key($tableProp['fields'], $localTablesStructure[$table]['fields']);
                    if ($list) {
                        $data[0][] = 'Columns: ' . join(', ', array_keys($list));
                    }
                    // New
                    $list = array_diff_key($localTablesStructure[$table]['fields'], $tableProp['fields']);
                    if ($list) {
                        $data[1][] = 'Columns: ' . join(', ', array_keys($list));
                    }

                    /**
                     * Compare indexes
                     */
                    // Prepare keys structure info for reference and local tables
                    foreach ($tableProp['keys'] as $keyInfo) {
                        $keyStructureHash = md5($keyInfo['type'] . join(':', $keyInfo['fields']));
                        $tableProp['keys_structure'][$keyStructureHash] = $keyInfo['name'];
                    }
                    foreach ($localTablesStructure[$table]['keys'] as $keyInfo) {
                        $keyStructureHash = md5($keyInfo['type'] . join(':', $keyInfo['fields']));
                        $localTablesStructure[$table]['keys_structure'][$keyStructureHash] = $keyInfo['name'];
                    }
                    $compareInfo = $this->_compareKeysStructure(
                        $localTablesStructure[$table]['keys_structure'], $tableProp['keys_structure']
                    );
                    if (!empty($compareInfo['missing'])) {
                        $data[0][] = 'Keys: ' . join(', ', $compareInfo['missing']);
                    }
                    if (!empty($compareInfo['new'])) {
                        $data[1][] = 'Keys: ' . join(', ', $compareInfo['new']);
                    }
                    if (!empty($compareInfo['corrupted'])) {
                        $data[2][] = 'Keys: ' . join(', ', $compareInfo['corrupted']);
                    }

                    /**
                     * Compare foreign keys
                     */
                    // Prepare constraints structure info for reference and local tables
                    foreach ($tableProp['constraints'] as $keyInfo) {
                        $keyStructureHash = md5($keyInfo['ref_db'] . $keyInfo['pri_table'] . $keyInfo['pri_field'] .
                            $keyInfo['ref_table'] . $keyInfo['ref_field'] . $keyInfo['on_delete'] .
                            $keyInfo['on_update']
                        );
                        $tableProp['constraints_structure'][$keyStructureHash] = $keyInfo['fk_name'];
                    }
                    foreach ($localTablesStructure[$table]['constraints'] as $keyInfo) {
                        $keyStructureHash = md5($keyInfo['ref_db'] . $keyInfo['pri_table'] . $keyInfo['pri_field'] .
                            $keyInfo['ref_table'] . $keyInfo['ref_field'] . $keyInfo['on_delete'] .
                            $keyInfo['on_update']
                        );
                        $localTablesStructure[$table]['constraints_structure'][$keyStructureHash] = $keyInfo['fk_name'];
                    }
                    $compareInfo = $this->_compareKeysStructure(
                        $localTablesStructure[$table]['constraints_structure'], $tableProp['constraints_structure']
                    );
                    if (!empty($compareInfo['missing'])) {
                        $data[0][] = 'Constraints: ' . join(', ', $compareInfo['missing']);
                    }
                    if (!empty($compareInfo['new'])) {
                        $data[1][] = 'Constraints: ' . join(', ', $compareInfo['new']);
                    }
                    if (!empty($compareInfo['corrupted'])) {
                        $data[2][] = 'Constraints: ' . join(', ', $compareInfo['corrupted']);
                    }

                    // Check charset
                    if ($tableProp['charset'] != $localTablesStructure[$table]['charset']) {
                        $info = 'Local table charset is "' . $localTablesStructure[$table]['charset'] .
                            '", but must be "' . $tableProp['charset'] .
                            '" (collate "' . $tableProp['collate'] . '")';
                        $data[2][] = $info;
                    }

                    // Check storage
                    if ($tableProp['engine'] != $localTablesStructure[$table]['engine']) {
                        $info = 'Local table storage engine type is "' . $localTablesStructure[$table]['engine'] .
                            '", but must be "' . $tableProp['engine'] . '"';
                        $data[2][] = $info;
                    }

                    if ($data) {
                        // Determine maximum size of data for each column. This number will represent how many rows
                        // must be generated in report for one DB table
                        $dataCount = 0;
                        for ($i = 0; $i < 3; $i++) {
                            if (!isset($data[$i])) {
                                continue;
                            }
                            $size = sizeof($data[$i]);
                            if ($size > $dataCount) {
                                $dataCount = $size;
                            }
                        }

                        for ($i = 0; $i < $dataCount; $i++) {
                            $corruptedLocalTablesReport[] = array(
                                $i == 0 ? $table : '',
                                isset($data[0][$i]) ? $data[0][$i] : '',
                                isset($data[1][$i]) ? $data[1][$i] : '',
                                isset($data[2][$i]) ? $data[2][$i] : '',
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        return array(
            'new'       => $newTablesReport,
            'missing'   => $missingTablesReport,
            'corrupted' => $corruptedLocalTablesReport
        );
    }

    /**
     * Compare table keys by structure
     *
     * So if key names are different but they have same structure, that means that they are equal.
     * Such approach is used because client's DB can contain table prefixes that make sometimes keys to be renamed, so
     * keys name length will be limited to 64 characters (at least after EE 1.11.0.0)
     *
     * @param array $localKeysStructure
     * @param array $referenceKeysStructure
     * @return array
     */
    protected function _compareKeysStructure(array $localKeysStructure, array $referenceKeysStructure)
    {
        // Keys that are exist in local table but not exist in reference table, so they can be new
        $maybeNew    = array_diff_key($localKeysStructure, $referenceKeysStructure);
        // Keys that are exist in reference table but not exist in local table, so they can be missing
        $maybeMissing = array_diff_key($referenceKeysStructure, $localKeysStructure);
        // Same keys by name that are exist in both local and reference tables, but they can be different by structure
        $sameNames   = array_intersect(array_values($maybeMissing), array_values($maybeNew));

        // array_merge() used to reset indices
        // Actually new keys
        $new         = array_merge(array_diff(array_values($maybeNew), $sameNames), array());
        // Actually missing keys
        $missing     = array_merge(array_diff(array_values($maybeMissing), $sameNames), array());
        // Corrupted keys by structure
        $corrupted   = array_merge(array_intersect($sameNames, array_values($maybeNew)), array());

        return array('missing' => $missing, 'new' => $new, 'corrupted' => $corrupted);
    }

    /**
     * Generate DB tables and views information.
     * Tables information contains: Name, Engine, Rows count and table Size (data length + index length), Collation
     * Views information contains: Name
     *
     * @return array
     * @throws Exception
     */
    protected function _generateDbTablesStatusData()
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Count data can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $dbName = $this->_getMagentoDBName();
        $data = $myIsamData = array();
        $totalSize = $totalRows = null;

        try {
            $info = $connection->fetchAll("
                SELECT
                    TABLE_NAME AS `table_name`,
                    ENGINE AS `engine`,
                    TABLE_ROWS AS `rows`,
                    (DATA_LENGTH + INDEX_LENGTH) AS `size`,
                    TABLE_COLLATION AS `collation`,
                    TABLE_TYPE AS `type`,
                    CREATE_TIME as `create_time`,
                    UPDATE_TIME as `update_time`,
                    TABLE_COMMENT as `comment`
                FROM information_schema.TABLES
                WHERE TABLES.TABLE_SCHEMA = '$dbName'
                      AND (TABLES.TABLE_TYPE = 'BASE TABLE' OR TABLES.TABLE_TYPE = 'VIEW')
                ORDER BY `size` DESC, TABLE_ROWS DESC
            ");

            if ($info) {
                $totalSize = $totalRows = $counter = 0;
                foreach ($info as $_data) {
                    if ($counter < self::TABLE_DATA_ROW_MAXIMUM_COUNT_FOR_OUTPUT - 2) {
                        if ($_data['type'] == 'BASE TABLE') {
                            $collectedData = array(
                                $_data['table_name'],
                                $_data['engine'],
                                $_data['rows'],
                                $this->_formatBytes($_data['size'], 3, 'IEC'),
                                $_data['create_time'],
                                $_data['update_time'],
                                $_data['collation'],
                                $_data['comment'],
                            );
                            $data[] = $collectedData;
                            if ($_data['engine'] == 'MyISAM') {
                                $myIsamData[] = $collectedData;
                            }
                        } else {
                            $data[] = array($_data['table_name'], '[VIEW]', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a');
                        }
                    }
                    $totalSize += $_data['size'];
                    $totalRows += $_data['rows'];
                    $counter++;
                    if ($counter == self::TABLE_DATA_ROW_MAXIMUM_COUNT_FOR_OUTPUT - 2) {
                        $data[] = array('And more...');
                    }
                }
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        if ($totalSize !== null && $totalRows !== null) {
            $data[] = array('TOTALS: ', '', $totalRows, $this->_formatBytes($totalSize, 3, 'IEC'), '', '', '', '');
        }

        $systemReport = array();
        $systemReport['DB Tables Status'] = array(
            'header' => array('Name', 'Engine', '~ Rows', '~ Size', 'Create Time', 'Update Time','Collation','Comment'),
            'data' => $data
        );
        $systemReport['DB MyISAM Tables Status'] = array(
            'header' => array('Name', 'Engine', '~ Rows', '~ Size', 'Create Time', 'Update Time','Collation','Comment'),
            'data' => $myIsamData
        );

        return $systemReport;
    }

    /**
     * Retrieve DB structure and data information by specified type
     * Available types: all, tables, triggers, routines, eav_attributes
     *
     * @param string $type
     * @param bool $forceRegenerateLocalSnapshot
     *
     * @return array
     *
     * @throws Exception
     */
    protected function _getDbStructureData($type = 'all', $forceRegenerateLocalSnapshot = false)
    {
        static $referenceData = null, $localData = null;
        if ($referenceData === null || $localData === null || $forceRegenerateLocalSnapshot) {
            $edition = strtolower($this->_magentoEdition);
            $refPath        = Mage::getBaseDir('lib') . DS . 'Support' . DS . 'ref' . DS;
            $referenceFile = sprintf(
                self::REPORT_DB_STRUCTURE_SNAPSHOT_REF_FILE_MASK, $edition, $this->_magentoVersion
            );
            $localPath      = Mage::getBaseDir('var') . DS . 'support' . DS;
            $localFile     = sprintf(
                self::REPORT_DB_STRUCTURE_SNAPSHOT_LOCAL_FILE_MASK, $edition, $this->_magentoVersion
            );

            if (!is_readable($refPath . $referenceFile)) {
                throw new Exception(
                    $referenceFile . ' doesn\'t exist or it is not readable. DB structure data can\'t be retrieved.'
                );
            }
            if (!file_exists($localPath . $localFile)) {
                $this->_generateDbDataAndStructureSnapshot();
            }
            if (!is_readable($localPath . $localFile)) {
                throw new Exception(
                    $localFile . ' wasn\'t generated or it is not readable. DB structure data can\'t be retrieved.'
                );
            }

            $referenceData = unserialize(file_get_contents($refPath . $referenceFile));
            $localData     = unserialize(file_get_contents($localPath . $localFile));
        }

        if (!is_array($referenceData) || !is_array($localData)) {
            throw new Exception('DB snapshot data is corrupted. DB structure data can\'t be retrieved.');
        }

        $type = in_array($type, array('tables', 'triggers', 'routines', 'eav_attributes')) ? $type : 'all';

        if ($type == 'all') {
            return array('reference_data' => $referenceData, 'local_data' => $localData);
        }
        if ($type == 'eav_attributes') {
            if (!array_key_exists('data', $referenceData) || !array_key_exists('data', $localData)) {
                throw new Exception('DB snapshot data is corrupted. DB structure data can\'t be retrieved.');
            }
            $referenceData = $referenceData['data'];
            $localData     = $localData['data'];
        }

        if (!array_key_exists($type, $referenceData) || !array_key_exists($type, $localData)) {
            throw new Exception('DB snapshot data is corrupted. DB structure data can\'t be retrieved.');
        }

        return array('reference_data' => $referenceData[$type], 'local_data' => $localData[$type]);
    }

    /**
     * Generate DB structure and data consistency snapshot
     *
     * @return bool
     * @throws Exception
     */
    protected function _generateDbDataAndStructureSnapshot()
    {
        $path = Mage::getBaseDir('var') . DS . 'support' . DS;

        if (!is_dir($path)) {
            mkdir($path);
            chmod($path, 0777);
        }

        if (!is_writable($path)) {
            throw new Exception(
                'Can\'t write to var' . DS . 'support' . DS
                . ' directory. DB structure data file wasn\'t generated.'
            );
        }

        $tables        = $this->_getMySQLTablesList();
        $triggers      = $this->_getMySQLTriggersList();
        $routines      = $this->_getMySQLRoutinesList();
        $eavAttributes = $this->_getEavAttributes('all');

        $structureData = array(
            'tables'    => $tables,
            'triggers'  => $triggers,
            'routines'  => $routines,
            'data'      => array('eav_attributes' => $eavAttributes)
        );
        $this->_log(null, 'Got data for "' . sizeof($tables) . '" tables.');
        $this->_log(null, 'Got data for "' . sizeof($triggers) . '" triggers.');
        $this->_log(null, 'Got data for "' . sizeof($routines) . '" routines.');
        $this->_log(null, 'Got data for "' . sizeof($eavAttributes) . '" eav attributes.');

        $edition = strtolower($this->_magentoEdition);
        $filename = sprintf(self::REPORT_DB_STRUCTURE_SNAPSHOT_LOCAL_FILE_MASK, $edition, $this->_magentoVersion);
        $writtenBytes = file_put_contents($path . $filename, serialize($structureData));
        $this->_log(null,
            ($writtenBytes !== false
                ? 'File "' . $filename . '" was successfully generated.'
                : 'File "' . $filename . '" wasn\'t generated!')
        );

        return $writtenBytes !== false;
    }

    /**
     * Collect MySQL triggers
     *
     * @return array
     *
     * @throws Exception
     */
    protected function _getMySQLTriggersList()
    {
        static $data = array();
        if (!empty($data)) {
            return $data;
        }
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Routines list can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $dbName = $this->_getMagentoDBName();

        $triggers = $connection->fetchAll("
            SELECT TRIGGER_NAME AS `name`,
                    CONCAT('On ', EVENT_MANIPULATION, ': ', EVENT_OBJECT_TABLE) AS `comment`
            FROM information_schema.TRIGGERS
            WHERE EVENT_OBJECT_SCHEMA = '$dbName'
        ");

        if (!$triggers) {
            return $data;
        }

        foreach ($triggers as $trigger) {
            $data[$trigger['name']] = array('comment' => $trigger['comment']);
        }

        return $data;
    }

    /**
     * Collect stored MySQL procedures and functions
     *
     * @return array
     *
     * @throws Exception
     */
    protected function _getMySQLRoutinesList()
    {
        static $data = array();
        if (!empty($data)) {
            return $data;
        }
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Routines list can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $dbName = $this->_getMagentoDBName();

        $routines = $connection->fetchAll("
            SELECT ROUTINE_NAME AS `name`,
                   ROUTINE_TYPE AS `type`,
                   IF(DTD_IDENTIFIER IS NOT NULL, CONCAT('Returns: ', DTD_IDENTIFIER), '') AS `comment`
            FROM information_schema.ROUTINES
            WHERE ROUTINE_SCHEMA = '$dbName'
            ORDER BY 2,1
        ");

        if (!$routines) {
            return $data;
        }

        foreach ($routines as $routine) {
            $data[$routine['name']] = array('type' => $routine['type'], 'comment' => $routine['comment']);
        }

        return $data;
    }

    /**
     * Retrieve MySQL tables list for current DB.
     * List contains full tables structure.
     *
     * @return array
     *
     * @throws Exception
     */
    protected function _getMySQLTablesList()
    {
        static $structureData = array();
        if (!empty($structureData)) {
            return $structureData;
        }
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. MySQL tables structure cannot be collected.');
        }

        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $tables = $this->_readConnection->fetchAll('SHOW TABLES');
        $tablesNumber = sizeof($tables);

        for ($rowIndex = 0; $rowIndex < $tablesNumber; $rowIndex++) {
            try {
                $originalTableName = $tables[$rowIndex];
                $tableName = substr(current($originalTableName), strlen($tablePrefix));
                $structureData[$tableName] = $this->_getTableProperties($tablePrefix . $tableName);
            } catch (Exception $e) {
                $this->_log($e);
            }
            if (($rowIndex + 1) % 50 == 0) {
                $this->_log(null, $rowIndex . ' tables processed...');
            }
        }

        return $structureData;
    }

    /**
     * Collect DB table structure data
     *
     * @param $table
     *
     * @return array
     * @throws Exception
     */
    protected function _getTableProperties($table)
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. DB table properties can\'t be retrieved.');
        }

        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        $prefixLength = strlen($tablePrefix);
        $tableProp = array(
            'fields'      => array(),
            'keys'        => array(),
            'constraints' => array(),
            'engine'      => 'MYISAM',
            'charset'     => 'utf8',
            'collate'     => null,
            'create_sql'  => null
        );

        $connection = $this->_readConnection;

        // collect fields
        $columnsInfo = $connection->fetchAll("SHOW FULL COLUMNS FROM `{$table}`");
        foreach ($columnsInfo as $field) {
            $tableProp['fields'][$field['Field']] = array(
                'type'      => $field['Type'],
                'is_null'   => strtoupper($field['Null']) == 'YES' ? true : false,
                'default'   => $field['Default'],
                'extra'     => $field['Extra'],
                'collation' => $field['Collation'],
            );
        }

        // create sql
        $createSql = $connection->fetchAll("SHOW CREATE TABLE `{$table}`");
        $tableProp['create_sql'] = $createSql[0]['Create Table'];

        // collect keys
        $regExp  = '#(PRIMARY|UNIQUE|FULLTEXT|FOREIGN)?\s+KEY\s+(`[^`]+` )?(\([^\)]+\))#';
        $matches = array();
        preg_match_all($regExp, $tableProp['create_sql'], $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (isset($match[1]) && $match[1] == 'PRIMARY') {
                $keyName = 'PRIMARY';
            }
            elseif (isset($match[1]) && $match[1] == 'FOREIGN') {
                continue;
            }
            else {
                $keyName = strtoupper(substr($match[2], 1, -2));
            }
            $fields = $fieldsMatches = array();
            preg_match_all("#`([^`]+)`#", $match[3], $fieldsMatches, PREG_SET_ORDER);
            foreach ($fieldsMatches as $field) {
                $fields[] = $field[1];
            }

            $tableProp['keys'][$keyName] = array(
                'type'   => !empty($match[1]) ? $match[1] : 'INDEX',
                'name'   => $keyName,
                'fields' => $fields
            );
        }

        // collect CONSTRAINT
        $regExp  = '#,\s+CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\) '
            . 'REFERENCES (`[^`]*\.)?`([^`]*)` \(`([^`]*)`\)'
            . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?'
            . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?#';
        $matches = array();
        preg_match_all($regExp, $tableProp['create_sql'], $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $keyName = strtoupper($match[1]);
            $tableProp['constraints'][$keyName] = array(
                'fk_name'   => $keyName,
                'ref_db'    => isset($match[3]) ? $match[3] : null,
                'pri_table' => substr($table, $prefixLength),
                'pri_field' => $match[2],
                'ref_table' => substr($match[4], $prefixLength),
                'ref_field' => $match[5],
                'on_delete' => isset($match[6]) ? $match[7] : '',
                'on_update' => isset($match[8]) ? $match[9] : ''
            );
        }

        // engine
        $regExp = "#(ENGINE|TYPE)="
            . "(MEMORY|HEAP|INNODB|MYISAM|ISAM|BLACKHOLE|BDB|BERKELEYDB|MRG_MYISAM|ARCHIVE|CSV|EXAMPLE)"
            . "#i";
        $match  = array();
        if (preg_match($regExp, $tableProp['create_sql'], $match)) {
            $tableProp['engine'] = strtoupper($match[2]);
        }

        //charset
        $regExp = "#DEFAULT CHARSET=([a-z0-9]+)( COLLATE=([a-z0-9_]+))?#i";
        $match  = array();
        if (preg_match($regExp, $tableProp['create_sql'], $match)) {
            $tableProp['charset'] = strtolower($match[1]);
            if (isset($match[3])) {
                $tableProp['collate'] = $match[3];
            }
        }

        return $tableProp;
    }

    /**
     * Retrieve Magento default DB name from config
     *
     * @return string
     */
    protected function _getMagentoDBName()
    {
        static $dbName = null;
        if ($dbName === null) {
            $dbName = (string) Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
        }

        return $dbName;
    }

    ################################################
    ###                  OTHER                   ###
    ################################################

    /**
     * Generate all events report
     *
     * @return array
     */
    protected function _generateAllEventsData()
    {
        $systemReport = array();
        $systemReport['All Global Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('global')
        );

        $systemReport['All Admin Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('adminhtml')
        );

        return $systemReport;
    }

    /**
     * Generate core events report
     *
     * @return array
     */
    protected function _generateCoreEventsData()
    {
        $systemReport = array();
        $systemReport['Core Global Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('global', 'core')
        );

        $systemReport['Core Admin Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('adminhtml', 'core')
        );

        return $systemReport;
    }

    /**
     * Generate enterprise events report
     *
     * @return array
     */
    protected function _generateEnterpriseEventsData()
    {
        $systemReport = array();
        $systemReport['Enterprise Global Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('global', 'enterprise')
        );

        $systemReport['Enterprise Admin Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('adminhtml', 'enterprise')
        );

        return $systemReport;
    }

    /**
     * Generate custom events report
     *
     * @@return array
     */
    protected function _generateCustomEventsData()
    {
        $systemReport = array();
        $systemReport['Custom Global Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('global', 'custom')
        );

        $systemReport['Custom Admin Events'] = array(
            'header' => array('Event Name', 'Observer Class', 'Method'),
            'data' => $this->_getEvents('adminhtml', 'custom')
        );

        return $systemReport;
    }

    /**
     * Get configured events in the system by scope and type
     *
     * @param string $scope
     * @param string $type
     *
     * @return array
     */
    protected function _getEvents($scope, $type = 'all')
    {
        $scope = $scope == 'adminhtml' || $scope == 'global' ? $scope : 'global';
        $type = !in_array($type, array('all', 'core', 'enterprise', 'custom')) ? 'all' : $type;
        $data = $eventsData = array();
        $coreNamespaces = array('Mage', 'Zend');

        $events = Mage::app()->getConfig()->getNode($scope . '/events');
        if (!$events) {
            return array();
        }
        $suffix = 0;
        foreach ($events->children() as $event) {
            foreach ($event->observers->children() as $info) {
                $class = $info->class ? (string)$info->class : $info->getClassName();
                $className = Mage::getConfig()->getModelClassName($class);
                if ($type != 'all') {
                    $nameSpace = substr($className, 0, strpos($className, '_'));
                    $_className = str_replace($nameSpace . '_', '', $className);
                    $module = $nameSpace . '_' . substr($_className, 0, strpos($_className, '_'));
                }  else {
                    $nameSpace = '';
                    $module = '';
                }
                if (($type == 'core' && !in_array($nameSpace, $coreNamespaces)
                        && !in_array($module, $this->_additionalCoreModules['community']))
                    || ($type == 'custom' && (in_array($nameSpace, $coreNamespaces) || $nameSpace == 'Enterprise'
                            || in_array($module, $this->_additionalCoreModules['community'])))
                    || ($type == 'enterprise' && $nameSpace != 'Enterprise')
                ) {
                    continue;
                }

                $classPath = $this->_getClassPath($className, $this->_getModuleCodePoolByClassName($className));
                $arrayKey = $eventName = $event->getName();
                if (isset($eventsData[$eventName])) {
                    $arrayKey .= '_' . (++$suffix);
                }

                $eventsData[$arrayKey] = array(
                    $eventName,
                    $className . "\n" . '{' . $classPath . '}',
                    (string)$info->method
                );
            }
        }

        ksort($eventsData);
        foreach ($eventsData as $_data) {
            $data[] = $_data;
        }

        return $data;
    }

    /**
     * Generate class rewrite conflicts
     *
     * @return array
     */
    protected function _generateClassRewriteConflictsData()
    {
        $modules = Mage::app()->getConfig()->getNode('modules')->children();
        $data = $systemReport = $_conflicts = $_rewrites = array();

        foreach ($modules as $modName => $module) {
            $configFile = $this->_getModulePath(
                $modName,
                $this->_getModuleCodePoolByClassName($modName)
            );
            $configFile .= 'etc' . DS . 'config.xml';

            try {
                $config = new Mage_Core_Model_Config_Base($configFile);
            } catch (Exception $e) {
                //
            }

            if (!isset($config)) {
                continue;
            }
            $classes = $config->getXpath('global/*/*/rewrite');
            if (!$classes) {
                continue;
            }
            /** @var $element Mage_Core_Model_Config_Element */
            foreach ($classes as $element) {
                //module node
                $moduleNode = $element->getParent();
                //scope node (models|blocks|helpers)
                $scopeNode = $moduleNode->getParent();
                //scope name
                $scopeName = $scopeNode->getName();
                if (!in_array($scopeName, array('models', 'blocks', 'helpers'))) {
                    continue;
                }
                /** @var $rewrite Mage_Core_Model_Config_Element */
                foreach ($element as $rewrite) {
                    $_rewriteFactoryName = $element->getParent()->getName() . '/' . $rewrite->getName();
                    if (!array_key_exists($_rewriteFactoryName, $_rewrites)) {
                        $_rewrites[$_rewriteFactoryName] = array(
                            'pool' => (string)$module->codePool,
                            'rewrite' => trim($rewrite),
                            'is_active' => $this->_isModuleActiveByClassName($modName),
                        );
                    } else {
                        if (!array_key_exists($_rewriteFactoryName, $_conflicts)) {
                            $_conflicts[$_rewriteFactoryName][] = $_rewrites[$_rewriteFactoryName];
                        }
                        $_conflicts[$_rewriteFactoryName][] = array(
                            'pool' => (string)$module->codePool,
                            'rewrite' => trim($rewrite),
                            'is_active' => $this->_isModuleActiveByClassName($modName),
                        );
                    }
                }
            }
            unset($config);
        }

        if ($_conflicts) {
            foreach ($_conflicts as $factoryName => $conflicts) {
                foreach ($conflicts as $conflict) {
                    $data[] = array(
                        $factoryName,
                        $conflict['rewrite'] . "\n" .
                        '    {' . $this->_getClassPath($conflict['rewrite'], $conflict['pool']) . '}',
                        $conflict['is_active'] ? 'Yes' : 'No',
                    );
                }
            }
        }

        $systemReport['Class Rewrite Conflicts'] = array(
            'header' => array('Factory Name', 'Class', 'Is Active'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate classes rewrite data
     *
     * @return array
     */
    protected function _generateClassRewritesData()
    {
        //global/models|blocks|helpers/module/rewrite/mage_class/to_new_class
        $systemReport = $rewrites = array();
        $classes = Mage::app()->getConfig()->getNode('global')->xpath('.//*/*/rewrite');
        if (!$classes) {
            $systemReport['Active Class Rewrites'] = array(
                'header' => array('Original Class', 'New Class', 'Type'),
                'data' => array()
            );

            return $systemReport;
        }
        /** @var $element Mage_Core_Model_Config_Element */
        foreach ($classes as $element) {
            //module node
            $moduleNode = $element->getParent();
            //scope node (models|blocks|helpers)
            $scopeNode = $moduleNode->getParent();
            //scope name
            $scopeName = $scopeNode->getName();
            if (!in_array($scopeName, array('models', 'blocks', 'helpers'))) {
                continue;
            }
            $deprecatedNode = $scopeNode->xpath('.//*/deprecatedNode[text()="' . $moduleNode->getName() . '"]');
            /** @var $rewrite Mage_Core_Model_Config_Element */
            foreach ($element as $rewrite) {
                // By default and in most cases in each scope of each module there is <class> node
                // which specifies class name pattern (e.g.: Mage_Adminhtml_Model)
                if (!empty($moduleNode->class)) {
                    $originalClass = $moduleNode->class . '_' . uc_words($rewrite->getName());
                }
                // But sometimes it's not specified, for ex.: deprecated resource model names, not defined helper name
                // Case when <deprecatedNode> is in use
                else if ($deprecatedNode && !empty($deprecatedNode[0]->getParent()->class)) {
                    $originalClass = trim($deprecatedNode[0]->getParent()->class) . '_' . uc_words($rewrite->getName());
                }
                // Otherwise specifically for certain scope try to resolve original class name
                else {
                    $combinedFactoryClassName = $moduleNode->getName() . '_' . $rewrite->getName();
                    switch ($scopeName) {
                        case 'helpers':
                            $originalClass = Mage::getConfig()->getHelperClassName($combinedFactoryClassName);
                            break;
                        default:
                            $originalClass = $combinedFactoryClassName;
                            break;
                    }
                }

                $newClass = trim($rewrite);
                // Resolve new (rewrite to) class name if it was specified in factory format
                if (sizeof(explode('/', $newClass)) == 2) {
                    switch ($scopeName) {
                        case 'models':
                            $newClass = Mage::getConfig()->getModelClassName($newClass);
                            break;
                        case 'blocks':
                            $newClass = Mage::getConfig()->getBlockClassName($newClass);
                            break;
                        case 'helpers':
                            $newClass = Mage::getConfig()->getHelperClassName($newClass);
                            break;
                        default:
                            break;
                    }
                }

                // Retrieve code pool information
                $originalCodePool = $this->_getModuleCodePoolByClassName($originalClass);
                $newCodePool = $this->_getModuleCodePoolByClassName($newClass);

                $rewrites[$scopeName][] = array(
                    'original_class' => $originalClass,
                    'original_code_pool' => $originalCodePool,
                    'new_class' => $newClass,
                    'new_code_pool' => $newCodePool,
                );
            }
        }

        $data = array();
        if (!empty($rewrites)) {
            foreach ($rewrites as $type => $rewrite) {
                foreach ($rewrite as $item) {
                    $data[] = array(
                        $item['original_class'] .
                        (!empty($item['original_code_pool'])
                            ? ' [' . $item['original_code_pool'] . ']' . "\n"
                            : ''
                        ) .
                        (!empty($item['original_code_pool'])
                            ? '{' . $this->_getClassPath($item['original_class'], $item['original_code_pool']) . '}'
                            : ''
                        ),
                        $item['new_class'] .
                        (!empty($item['new_code_pool'])
                            ? ' [' . $item['new_code_pool'] . ']' . "\n"
                            : ''
                        ) .
                        (!empty($item['new_code_pool'])
                            ? '{' . $this->_getClassPath($item['new_class'], $item['new_code_pool']) . '}'
                            : ''
                        ),
                        substr($type, 0, -1)
                    );
                }
            }
        }

        $systemReport['Active Class Rewrites'] = array(
            'header' => array('Original Class', 'New Class', 'Type'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate core php file rewrites data
     *
     * @return array
     */
    protected function _generateFileRewritesData()
    {
        /**
         * Magento autoloader loads classes in this sequence:
         * 1. Try to find class in "local" code pool
         * 2. If not found, try to find class in "community" code pool
         * 3. If not found, try to find class in "code" code pool
         * 4. If not found, try to find class in "lib" code pool
         *
         * "local" and "community" - are "custom" code pools. That is why rewritten files will be searched there.
         *
         * But collecting custom files must be done in reverse sequence withing "custom" code pools, because
         * if autoloader finds file - it doesn't try to find it further down through rest of code pools - it is obvious.
         * That is why $customCodePools contains "custom" code pools in reverse sequence to default autoloader load
         * sequence.
         */
        $customCodePools = array(
            'community' => $this->_getRootPath() . 'app' . DS . 'code' . DS .
                'community' . DS,
            'local' => $this->_getRootPath() . 'app' . DS . 'code' . DS .
                'local' . DS,
        );

        /**
         * Core code pools are set in same sequence here as they watched by loader by default
         */
        $coreCodePools = array(
            'core' => $this->_getRootPath() . 'app' . DS . 'code' . DS .
                'core' . DS,
            'lib' => $this->_getRootPath() . 'lib' . DS,
        );

        // Collecting "custom" php files
        $files = $customFiles = array();
        foreach ($customCodePools as $pool => $poolDirectory) {
            if (!file_exists($poolDirectory) || !is_dir($poolDirectory)) {
                continue;
            }
            try {
                $directory = new RecursiveDirectoryIterator($poolDirectory);
                $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
                $files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
            } catch (Exception $e) {
                $this->_log($e);
            }
            foreach ($files as $file) {
                $filePath = $file[0];
                $relativePath = str_replace($poolDirectory, '', $filePath);
                $customFiles[$relativePath] = $pool;
            }
        }

        $_rewriteFilesCache = $rewritesData = array();
        foreach ($customFiles as $relativePath => $pool) {
            foreach ($coreCodePools as $corePool => $poolDirectory) {
                $coreFile = $poolDirectory . $relativePath;
                // If file exists in core code pool then remember only occurrence in code pool which goes first in
                // load sequence
                if (file_exists($coreFile) && !isset($_rewriteFilesCache[$relativePath])) {
                    $rewritesData[] = array(
                        'app' . DS . 'code' . DS .
                        $pool . DS . $relativePath,
                        $corePool,
                        $pool
                    );
                    $_rewriteFilesCache[$relativePath] = true;
                }
            }
        }

        $systemReport = array();
        $systemReport['File Rewrites'] = array(
            'header' => array('Core File', 'Core Pool', 'Custom Pool'),
            'data' => $rewritesData
        );

        return $systemReport;
    }

    /**
     * Generate controllers rewrite data
     *
     * Example of configuration to parse:
     * <global>
     *   <routers>
     *     <core_module>
     *       <rewrite>
     *         <core_controller>
     *           <to>new_route/new_controller</to>
     *           <override_actions>true</override_actions>
     *           <actions>
     *             <core_action><to>new_module/new_controller/new_action</core_action>
     *           </actions>
     *         <core_controller>
     *       </rewrite>
     *     </core_module>
     *   </routers>
     * </global>
     *
     * This will override:
     * 1. core_module/core_controller/core_action to new_module/new_controller/new_action
     * 2. all other actions of core_module/core_controller to new_module/new_controller
     *
     * @return array
     */
    protected function _generateControllerRewritesData()
    {
        $systemReport = $rewritesData = array();
        $routers = Mage::app()->getConfig()->getNode('global')->xpath('.//routers/*/rewrite');
        if (!$routers) {
            $systemReport['Controller Rewrites'] = array(
                'header' => array('Core Controller', 'Core Action(s)', 'Custom Controller', 'Custom Action(s)'),
                'data' => array()
            );

            return $systemReport;
        }
        /** @var $rewrites Mage_Core_Model_Config_Element */
        foreach ($routers as $rewrites) {
            $coreFrontName = $rewrites->getParent()->getName();
            /** @var $rewrite Mage_Core_Model_Config_Element */
            foreach ($rewrites as $rewrite) {
                $coreController = $rewrite->getName();
                if (!empty($rewrite->to)) {
                    $rewriteInfo = explode('/', (string)$rewrite->to);
                    if (sizeof($rewriteInfo) !== 2 || empty($rewriteInfo[0]) || empty($rewriteInfo[1])) {
                        continue;
                    }
                    $rewritesData[] = $this->_getControllerRewriteData(
                        $coreFrontName, $coreController, '*',
                        $rewriteInfo[0], $rewriteInfo[1], '*'
                    );
                }
                if (!empty($rewrite->actions)) {
                    /** @var $action Mage_Core_Model_Config_Element */
                    foreach ($rewrite->actions->children() as $action) {
                        if (empty($action->to)) {
                            continue;
                        }
                        $rewriteInfo = explode('/', (string)$action->to);
                        if (sizeof($rewriteInfo) !== 3
                            || empty($rewriteInfo[0]) || empty($rewriteInfo[1]) || empty($rewriteInfo[2])
                        ) {
                            continue;
                        }
                        $rewritesData[] = $this->_getControllerRewriteData(
                            $coreFrontName, $coreController, $action->getName(),
                            $rewriteInfo[0], $rewriteInfo[1], $rewriteInfo[2]
                        );
                    }
                }
            }
        }

        $systemReport['Controller Rewrites'] = array(
            'header' => array('Core Controller', 'Core Action(s)', 'Custom Controller', 'Custom Action(s)'),
            'data' => $rewritesData
        );

        return $systemReport;
    }

    /**
     * Generate controller rewrite data
     *
     * @param string $coreFrontName
     * @param string $coreController
     * @param string $coreAction
     * @param string $customFrontName
     * @param string $customController
     * @param string $customAction
     *
     * @return array
     */
    protected function _getControllerRewriteData($coreFrontName, $coreController, $coreAction, $customFrontName,
                                                 $customController, $customAction
    ) {
        $coreModule = $this->_getRealModuleNameByFrontName($coreFrontName);
        if ($coreModule) {
            $coreClass = $this->_getControllerClassName($coreModule, $coreController);
            $coreClass .= ' [' . $this->_getModuleCodePoolByClassName($coreClass) . ']';
            $coreClass .= "\n" . '{' . $this->_getControllerFileName($coreModule, $coreController) . '}';
        } else {
            $coreClass = $coreFrontName . '/' . $coreController;
        }

        $customModule = $this->_getRealModuleNameByFrontName($customFrontName);
        if ($customModule) {
            $customClass = $this->_getControllerClassName($customModule, $customController);
            $customClass .= ' [' . $this->_getModuleCodePoolByClassName($customClass) . ']';
            $customClass .= "\n" . '{' . $this->_getControllerFileName($customModule, $customController) . '}';
        } else {
            $customClass = $customFrontName . '/' . $customController;
        }

        return array($coreClass, $coreAction, $customClass, $customAction);
    }


    /**
     * Get real module name by controller front name
     *
     * @param string $frontName
     * @return bool|string
     */
    protected function _getRealModuleNameByFrontName($frontName)
    {
        /** @var $frontendNameNode Mage_Core_Model_Config_Element */
        $frontendNameNode = Mage::app()->getConfig()->getNode('frontend')
            ->xpath('.//routers/*/*/frontName[text()="' . $frontName . '"]');
        if (!$frontendNameNode) {
            return false;
        }
        if ($frontendNameNode[0]->getParent() && !empty($frontendNameNode[0]->getParent()->module)) {
            return (string)$frontendNameNode[0]->getParent()->module;
        }

        return false;
    }

    /**
     * Get controller file name by it's name and module
     *
     * @param string $realModule
     * @param string $controller
     *
     * @return string
     */
    protected function _getControllerFileName($realModule, $controller)
    {
        $parts = explode('_', $realModule);
        $realModule = implode('_', array_splice($parts, 0, 2));
        $file = Mage::getModuleDir('controllers', $realModule);
        $file = str_replace($this->_getRootPath(), '', $file);
        if (count($parts)) {
            $file .= DS . implode(DS, $parts);
        }
        $file .= DS . uc_words($controller, DS) . 'Controller.php';

        return $file;
    }

    /**
     * Get controller class name by it's name and module
     *
     * @param string $realModule
     * @param string $controller
     *
     * @return string
     */
    protected function _getControllerClassName($realModule, $controller)
    {
        $class = $realModule.'_'.uc_words($controller).'Controller';
        return $class;
    }

    /**
     * Generate routers rewrite data
     *
     * @return array
     */
    protected function _generateRouterRewritesData()
    {
        $systemReport = array();
        $rewrites = Mage::getConfig()->getNode('global/rewrite');
        if (!$rewrites) {
            $systemReport['Router Rewrites'] = array(
                'header' => array('From', 'To'),
                'data' => array()
            );
            return $systemReport;
        }

        $rewritesData = array();
        foreach ($rewrites->children() as $rewrite) {
            $from = (string)$rewrite->from;
            $to = (string)$rewrite->to;
            if (empty($from) || empty($to)) {
                continue;
            }
            $rewritesData[] = array($from, $to);
        }

        $systemReport['Router Rewrites'] = array(
            'header' => array('From', 'To'),
            'data' => $rewritesData
        );

        return $systemReport;
    }

    /**
     * Generate Magento version data
     *
     * @return array
     */
    protected function _generateMagentoVersionData()
    {
        $systemReport = array();
        $systemReport['Magento Version'] = array(
            'header' => array('Version'),
            'data' => array($this->_magentoEdition . ' ' . $this->_magentoVersion)
        );

        return $systemReport;
    }

    /**
     * Generate installed all modules data
     *
     * @return array
     */
    protected function _generateAllModulesData()
    {
        $systemReport = array();
        $systemReport['All Modules List'] = array(
            'header' => array(
                'Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'
            ),
            'data' => $this->_getModules('all')
        );

        return $systemReport;
    }

    /**
     * Generate installed core modules data
     *
     * @return array
     */
    protected function _generateCoreModulesData()
    {
        $systemReport = array();
        $systemReport['Core Modules List'] = array(
            'header' => array(
                'Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'
            ),
            'data' => $this->_getModules('core')
        );

        return $systemReport;
    }

    /**
     * Generate installed enterprise modules data
     *
     * @return array
     */
    protected function _generateEnterpriseModulesData()
    {
        $systemReport = array();
        $systemReport['Enterprise Modules List'] = array(
            'header' => array(
                'Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'
            ),
            'data' => $this->_getModules('enterprise')
        );

        return $systemReport;
    }

    /**
     * Generate installed custom modules data
     *
     * @return array
     */
    protected function _generateCustomModulesData()
    {
        $systemReport = array();
        $systemReport['Custom Modules List'] = array(
            'header' => array(
                'Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'
            ),
            'data' => $this->_getModules('custom')
        );

        return $systemReport;
    }

    /**
     * Generate disabled and installed modules data
     *
     * @return array
     */
    protected function _generateDisabledModulesData()
    {
        $systemReport = array();
        $systemReport['Disabled Modules List'] = array(
            'header' => array(
                'Module', 'Code Pool', 'Config Version', 'DB Version', 'DB Data Version', 'Output', 'Enabled'
            ),
            'data' => $this->_getModules('all', true)
        );

        return $systemReport;
    }

    /**
     * Collect installed modules information by scope
     *
     * @param string $scope
     * @param bool $disabledOnly
     *
     * @return array
     * @throws Exception
     */
    protected function _getModules($scope = 'all', $disabledOnly = false)
    {
        /**
         * Collect modules DB versions
         */
        $dbVersions = array();
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. Modules DB Version data can\'t be retrieved.');
            }

            $info = $this->_readConnection->fetchAll("SELECT * FROM `{$this->_getTableName('core/resource')}`");
            foreach ($info as $_moduleInfo) {
                $setupNode = Mage::app()->getConfig()->getNode('global/resources')->$_moduleInfo['code'];
                if ($setupNode) {
                    $moduleName = (string)$setupNode->setup->module;
                    $dbVersions[$moduleName] = array(
                        'version' => $_moduleInfo['version'],
                        'data_version' => $_moduleInfo['data_version']
                    );
                }
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $scope = !in_array($scope, array('all', 'core', 'enterprise', 'custom')) ? 'all' : $scope;

        $modulesData = array();
        $coreNamespaces = array_flip($this->_coreNamespaces);
        unset($coreNamespaces['Enterprise']);
        $coreNamespaces = array_flip($coreNamespaces);
        $additionalCoreModules = $this->_additionalCoreModules['community'];

        $modules = Mage::app()->getConfig()->getNode('modules');
        if (!$modules) {
            return array();
        }

        /**
         * Collect modules config files to determine if module disabled
         */
        clearstatcache();
        $codeDir = Mage::getBaseDir('code') . DS;
        $moduleToConfigFileMap = $this->_getModulesConfigFileMap();
        if (empty($moduleToConfigFileMap)) {
            $this->_log(null, 'Can\'t determine if modules enabled/disabled because none of config files can be read.');
        }

        /**
         * Generate modules data list
         */
        foreach ($modules->children() as $module => $info) {
            if ($scope != 'all') {
                $nameSpace = substr($module, 0, strpos($module, '_'));
            } else {
                $nameSpace = '';
            }
            $codePool = (string)$info->codePool;
            if (($scope == 'core' && !in_array($nameSpace, $coreNamespaces)
                    && !in_array($module, $additionalCoreModules))
                || ($scope == 'custom' &&
                    (
                        in_array($nameSpace, $coreNamespaces)
                        || $nameSpace == 'Enterprise'
                        || (
                            isset($this->_additionalCoreModules[$codePool])
                            && in_array($module, $this->_additionalCoreModules[$codePool])
                        )
                    )
                )
                || ($scope == 'enterprise' && $nameSpace != 'Enterprise')
            ) {
                continue;
            }

            $modulePath = $codeDir . $codePool . DS .
                str_replace('_', DS, $module) . DS;
            $moduleExists = is_dir($modulePath);
            $moduleEnabled = isset($moduleToConfigFileMap[$module]);
            if (isset($moduleToConfigFileMap[$module])) {
                $configData = is_readable($moduleToConfigFileMap[$module])
                    ? file_get_contents($moduleToConfigFileMap[$module])
                    : '';
                $searchPattern =
                    '<' . preg_quote($module) . '>\s+' .
                    '.*<active>([^<]+)</active>.+' .
                    '</' . preg_quote($module) . '>';
                if (preg_match('~' . $searchPattern . '~s', $configData, $matches)) {
                    $moduleEnabled = (bool)in_array($matches[1], array('1', 'true'));
                }
            }

            if ($disabledOnly && $moduleExists && $moduleEnabled) {
                continue;
            }

            $modulesData[] = array(
                $module . "\n" . '{' . $this->_getModulePath($module, $codePool) . '}',
                $codePool,
                (string)$info->version ? (string)$info->version : 'n/a',
                isset($dbVersions[$module]) ? $dbVersions[$module]['version'] : 'n/a',
                isset($dbVersions[$module]) ? $dbVersions[$module]['data_version'] : 'n/a',
                !$this->_readConnection
                    ? 'n/a'
                    : (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $module) ? 'No' : 'Yes'),
                empty($moduleToConfigFileMap) ? 'n/a' : ($moduleExists && $moduleEnabled ? 'Yes' : 'No')
            );
        }

        return $modulesData;
    }

    /**
     * Generate module => config_file map
     * Files being collected in app/etc/modules directory
     *
     * @return array
     */
    protected function _getModulesConfigFileMap()
    {
        $etcModulesDir = Mage::getBaseDir('etc')  . DS . 'modules' . DS;
        if (!is_readable($etcModulesDir)) {
            return array();
        }

        $moduleToConfigFileMap = array();
        $modulesConfigFiles = $this->_getFilesList($etcModulesDir, 1, self::REPORT_FILE_LIST_FILES,array(),'^.*\.xml$');
        foreach ($modulesConfigFiles as $configFile) {
            // If config file is not possible to read then it will be skipped
            if (!is_readable($configFile)
                || $this->_getFileSize($configFile) > self::MODULE_CONFIG_FILE_MAX_SIZE
            ) {
                continue;
            }
            $configData = file_get_contents($configFile);
            preg_match_all('~<([a-z0-9]+)\_([a-z0-9]+)>~i', $configData, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                /*
                 * Note: each module can be defined only once in any config, so it will be defined only for
                 * one code pool
                 */
                $moduleToConfigFileMap[$match[1] . '_' . $match[2]] = $configFile;
            }
        }

        return $moduleToConfigFileMap;
    }

    /**
     * Generate relative path to module directory by its name and code pool
     *
     * @param string $moduleName
     * @param string $codePool
     *
     * @return string
     */
    protected function _getModulePath($moduleName, $codePool)
    {
        return 'app' . DS . 'code' . DS . $codePool . DS
        . implode(DS, explode('_', $moduleName)) . DS;
    }


    /**
     * Generate count data information
     *
     * Supported counting for:
     * Stores, Tax Rules, Customers, Customer Attributes, Customer Segments, Orders, Categories, Products,
     * Product Attributes, URL Rewrites, Shopping Cart Price Rules, Catalog Price Rules, CMS Pages, Banners,
     * Log Visitors, Log Visitors Online, Log URLs, Log Quotes, Log Customers
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCountData(array $arguments = array())
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Count data can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $dataCount = array();

        // Stores number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('core/store')}`");
            $dataCount[] = array('Stores', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Tax Rules number
        try {
            $info = $connection->fetchAll(
                "SELECT COUNT(1) as cnt FROM `{$this->_getTableName('tax/tax_calculation_rule')}`"
            );
            $dataCount[] = array('Tax Rules', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Customers number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('customer/entity')}`");
            $dataCount[] = array('Customers', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
        } catch (Exception $e) {
            $this->_log($e);
        }

        $count = sizeof($dataCount);

        // Customer Attributes number
        try {
            $_info = $this->_getAttributesCount('customer');
            foreach ($_info as $_infoEntry) {
                $dataCount[] = $_infoEntry;
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Customer Address Attributes number
        try {
            $_info = $this->_getAttributesCount('customer_address');
            foreach ($_info as $_infoEntry) {
                $dataCount[] = $_infoEntry;
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Customer Segments
        try {
            $info = $connection->fetchAll(
                "SELECT `is_active` FROM `{$this->_getTableName('enterprise_customersegment/segment')}`"
            );
            if ($info) {
                $counter = 0;
                foreach ($info as $_data) {
                    if ($_data['is_active']) {
                        $counter++;
                    }
                }
                $dataCount[] = array('Customer Segments', sizeof($info), 'Active Segments: ' . $counter);
            } else {
                $dataCount[] = array('Customer Segments', 0);
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Orders number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('sales/order')}`");
            $dataCount[] = array('Sales Orders', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Categories number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('catalog/category')}`");
            $dataCount[] = array('Categories', isset($info[0]['cnt']) ? --$info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Category Attributes number
        try {
            $_info = $this->_getAttributesCount('category');
            foreach ($_info as $_infoEntry) {
                $dataCount[] = $_infoEntry;
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Products number
        try {
            $info = $connection->fetchAll("
                SELECT COUNT(1) as cnt, `type_id` FROM `{$this->_getTableName('catalog/product')}` GROUP BY `type_id`
            ");
            if ($info) {
                $counter = 0;
                $extra = '';
                foreach ($info as $_data) {
                    $counter += $_data['cnt'];
                    $extra .= $_data['type_id'] . ': ' . $_data['cnt'] . '; ';
                }
                $dataCount[] = array('Products', $counter, 'Product Types: ' . $extra);
            } else {
                $dataCount[] = array('Products', 0);
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Product Attributes number
        try {
            $_info = $this->_getAttributesCount('product');
            foreach ($_info as $_infoEntry) {
                $dataCount[] = $_infoEntry;
            }
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Product Attributes Flat Table Row Size
        try {
            $_info = $this->_getProductAttributesRowSizeForFlatTable();
            $dataCount[] = array(
                'Product Attributes Flat Table Row Size',
                $_info > 0 ? $this->_formatBytes($_info) : 'n/a',
                $_info . ' bytes'
            );
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Shopping Cart Price Rules number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('salesrule/rule')}`");
            $dataCount[] = array('Shopping Cart Price Rules', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Catalog Price Rules number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('catalogrule/rule')}`");
            $dataCount[] = array('Catalog Price Rules', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Target Rules (Rule-Based Relations) number
        try {
            $info = $connection->fetchAll("
                SELECT COUNT(1) as cnt FROM `{$this->_getTableName('enterprise_targetrule/rule')}`
            ");
            $dataCount[] = array('Target Rules', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // CMS Pages number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('cms/page')}`");
            $dataCount[] = array('CMS Pages', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Banners number
        try {
            $info = $connection->fetchAll("
                SELECT COUNT(1) as cnt FROM `{$this->_getTableName('enterprise_banner/banner')}`
            ");
            $dataCount[] = array('Banners', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // URL Rewrites number
        try {
            $urlRewriteTable = $this->_getTableName('core/url_rewrite');
            if ((version_compare($this->_magentoVersion, '1.13.0.0', '>=') && $this->_magentoEdition == 'EE')) {
                $urlRewriteTable = $this->_getTableName('enterprise_urlrewrite/url_rewrite');
            }
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$urlRewriteTable}`");
            $dataCount[] = array('URL Rewrites', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // URL Redirects number
        if ((version_compare($this->_magentoVersion, '1.13.0.0', '>=') && $this->_magentoEdition == 'EE')) {
            try {
                $info = $connection->fetchAll(
                    "SELECT COUNT(1) as cnt FROM `{$this->_getTableName('enterprise_urlrewrite/redirect')}`"
                );
                $dataCount[] = array('URL Redirects', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
                $count++;
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        // Core Cache records
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('core/cache')}`");
            $dataCount[] = array('Core Cache Records', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Core Cache Tag records
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('core/cache_tag')}`");
            $dataCount[] = array('Core Cache Tags', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Log Visitors number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('log/visitor')}`");
            $dataCount[] = array('Log Visitors', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Log Visitors Online number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('log/visitor_online')}`");
            $dataCount[] = array('Log Visitors Online', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Log URLs number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('log/url_table')}`");
            $dataCount[] = array('Log URLs', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Log Quotes number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('log/quote_table')}`");
            $dataCount[] = array('Log Quotes', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Log Customers number
        try {
            $info = $connection->fetchAll("SELECT COUNT(1) as cnt FROM `{$this->_getTableName('log/customer')}`");
            $dataCount[] = array('Log Customers', isset($info[0]['cnt']) ? $info[0]['cnt'] : 0);
            $count++;
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Data Count'] = array(
            'header' => array('Entity', 'Count', 'Extra'),
            'data' => $dataCount,
            'count' => $count
        );

        return $systemReport;
    }

    /**
     * Collect catalog attributes information
     *
     * @param string $type
     * @return array
     *
     * @throws Exception
     */
    protected function _getAttributesCount($type = 'product')
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Count data can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $entityTypeCode = null;
        switch ($type) {
            case 'customer':
                $title = 'Customer Attributes';
                $entityTypeCode = $type;
                $flagColumns = array(
                    'main_table.`is_system`',
                    'main_table.`is_used_for_customer_segment`',
                    'main_table.`is_visible`',
                );
                $eavMainTable = $this->_getTableName('customer/eav_attribute');
                break;
            case 'customer_address':
                $title = 'Customer Address Attributes';
                $entityTypeCode = $type;
                $flagColumns = array(
                    'main_table.`is_system`',
                    'main_table.`is_used_for_customer_segment`',
                    'main_table.`is_visible`',
                );
                $eavMainTable = $this->_getTableName('customer/eav_attribute');
                break;
            case 'category':
                $title = 'Category Attributes';
                $entityTypeCode = Mage_Catalog_Model_Category::ENTITY;
                $flagColumns = array(
                    'main_table.`is_visible_on_front`',
                    'main_table.`is_used_for_promo_rules`'
                );
                $eavMainTable = $this->_getTableName('catalog/eav_attribute');
                break;
            case 'product':
                $title = 'Product Attributes';
                $entityTypeCode = Mage_Catalog_Model_Product::ENTITY;
                $flagColumns = array(
                    'main_table.`is_visible_on_front`',
                    'main_table.`is_searchable`',
                    'main_table.`is_filterable`',
                    'main_table.`is_used_for_promo_rules`'
                );
                $eavMainTable = $this->_getTableName('catalog/eav_attribute');
                break;
            default:
                throw new Exception(
                    '_getAttributesInfo() doesn\'t support specified attributes entity type: "' . (string)$type . '".'
                    . ' Count data can\'t be retrieved.'
                );
                break;
        }

        $result = array();
        $entityTypeId = (int)Mage::getSingleton('eav/config')->getEntityType($entityTypeCode)->getId();
        $flagColumns = implode(', ', $flagColumns);
        $info = $connection->fetchAll("
            SELECT ea.`backend_type`, ea.`is_user_defined`, {$flagColumns}
            FROM `{$eavMainTable}` `main_table`
            INNER JOIN `{$this->_getTableName('eav/attribute')}` ea ON
                (ea.`attribute_id` = main_table.`attribute_id` AND ea.`entity_type_id` = '{$entityTypeId}')
        ");
        if ($info) {
            $_byType = $_extra = array();
            foreach ($info as $_data) {
                foreach ($_data as $key => $data) {
                    if ($key == 'backend_type') {
                        if (!isset($_byType[$_data[$key]])) {
                            $_byType[$_data[$key]] = 0;
                        }
                        $_byType[$_data[$key]]++;
                    } else {
                        if (!isset($_extra[$key])) {
                            $_extra[$key] = 0;
                        }
                        if ($_data[$key]) {
                            $_extra[$key]++;
                        }
                    }
                }
            }
            $extra1 = $extra2 = '';
            foreach ($_extra as $key => $num) {
                $extra1 .= $key . ': ' . $num . '; ';
            }
            foreach ($_byType as $key => $num) {
                $extra2 .= $key . ': ' . $num . '; ';
            }
            $result[] = array($title, sizeof($info), 'Attributes Flags: ' . $extra1);
            $result[] = array('', '', 'Attributes Types: ' . $extra2);
        } else {
            $result[] = array($title, 0);
        }

        return $result;
    }

    /**
     * Calculate approximately the size of table row if using flat functionality based on product attributes list
     *
     * @return int
     *
     * @throws Exception
     */
    protected function _getProductAttributesRowSizeForFlatTable()
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Count data can\'t be retrieved.');
        }

        $connection = $this->_readConnection;
        $entityTypeId = (int)Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
        $info = $connection->fetchAll("
            SELECT ea.`backend_type`
            FROM `{$this->_getTableName('catalog/eav_attribute')}` `main_table`
            INNER JOIN `{$this->_getTableName('eav/attribute')}` ea ON
                (ea.`attribute_id` = main_table.`attribute_id` AND ea.`entity_type_id` = '{$entityTypeId}')
        ");

        /**
         * Dynamic EAV attributes
         *
         * @see http://dev.mysql.com/doc/refman/5.0/en/storage-requirements.html
         */
        $typeSizes = array(
            'varchar'   => (255 + 1) * 3,
            'int'       => 4,
            'datetime'  => 8,
            'decimal'   => 4 + 2, // because decimal type = DECIMAL(12, 4)
        );
        $result = 0;
        if (!$info) {
            return false;
        }
        $_byType = array();
        foreach ($info as $_data) {
            if ($_data['backend_type'] == 'static') {
                continue;
            }
            if (!isset($_byType[$_data['backend_type']])) {
                $_byType[$_data['backend_type']] = 0;
            }
            $_byType[$_data['backend_type']]++;
        }
        foreach ($_byType as $type => $count) {
            if (isset($typeSizes[$type])) {
                $result += $typeSizes[$type] * $count;
            }
        }

        /**
         * Static product entity attributes
         *
         * @see http://dev.mysql.com/doc/refman/5.0/en/storage-requirements.html
         */
        $typeSizes = array(
            'tinyint'   => 1,
            'smallint'  => 2,
            'mediumint' => 3,
            'int'       => 4,
            'integer'   => 4,
            'bigint'    => 8,
            'float'     => 4,
            'double'    => 8,
            'real'      => 8,
            'date'      => 3,
            'time'      => 3,
            'datetime'  => 8,
            'timestamp' => 4,
            'year'      => 1,
        );
        $describe = $connection->describeTable($this->_getTableName('catalog/product'));
        if (empty($describe) || !is_array($describe)) {
            return false;
        }
        foreach ($describe as $column) {
            if (isset($typeSizes[$column['DATA_TYPE']])) {
                $result += $typeSizes[$column['DATA_TYPE']];
            } else if ($column['DATA_TYPE'] == 'varchar') {
                $result += ($column['LENGTH'] + 1) * 3;
            } else if ($column['DATA_TYPE'] == 'decimal') {
                $leftOver = $column['PRECISION'] - floor($column['PRECISION'] / 9) * 9;
                $result += floor($column['PRECISION'] / 9) * 4 + ceil($leftOver / 2);
            }
        }

        return (int)$result;
    }

    /**
     * Generate major configuration data
     *
     * @return array
     */
    protected function _generateConfigurationData()
    {
        // Supported configurations
        $configPaths = array(
            // url, STORE VIEW
            array('path' => 'web/secure/base_url', 'name' => 'Base Secured URL'),
            // url, STORE VIEW
            array('path' => 'web/unsecure/base_url', 'name' => 'Base Unsecured URL'),
            // text, WEBSITE
            array('path' => 'currency/options/base', 'name' => 'Base Currency'),
            // 1, STORE VIEW
            array('path' => 'dev/log/active', 'name' => 'Enable Log', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'system/log/enabled', 'name' => 'Log Tables Cleaning', 'enabled_flag' => true),
            // 1, STORE VIEW
            array('path' => 'dev/js/merge_files', 'name' => 'Merge JavaScript Files', 'enabled_flag' => true),
            // 1, STORE VIEW
            array('path' => 'dev/css/merge_css_files', 'name' => 'Merge CSS Files', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'admin/security/use_form_key', 'name' => 'Add Secret Key to URLs', 'enabled_flag' => true),
            // 1, GLOBAL
            array(
                'path' => 'catalog/frontend/flat_catalog_category',
                'name' => 'Flat Catalog Category',
                'enabled_flag' => true
            ),
            // 1, GLOBAL
            array(
                'path' => 'catalog/frontend/flat_catalog_product',
                'name' => 'Flat Catalog Product',
                'enabled_flag' => true
            ),
            // 1, WEBSITE
            array('path' => 'tax/weee/enable', 'name' => 'Fixed Product Taxes (FPT)', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'compiler', 'name' => 'Compilation', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'maintenance_mode', 'name' => 'Maintenance Mode', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'solr_engine', 'name' => 'Solr Search', 'enabled_flag' => true),
            // 1, GLOBAL
            array('path' => 'catalog/search/engine', 'name' => 'Search Engine'),
            // 1, STORE VIEW
            array('path' => 'system/page_crawl/enable', 'name' => 'Full Page Cache Crawler', 'enabled_flag' => true),
            // 1, GLOBAL
            array(
                'path' => 'customer/enterprise_customersegment/is_enabeld',
                'name' => 'Customer Segment Functionality',
                'enabled_flag' => true
            ),
            // custom
            array('path' => 'table_prefix', 'name' => 'DB Table Prefix'),
            // text, STORE VIEW
            array('path' => 'web/cookie/cookie_lifetime', 'name' => 'Cookie Lifetime'),
            // text, STORE VIEW
            array('path' => 'web/cookie/cookie_path', 'name' => 'Cookie Path'),
            // text, STORE VIEW
            array('path' => 'web/cookie/cookie_domain', 'name' => 'Cookie Domain'),
            // 1, STORE VIEW
            array('path' => 'web/cookie/cookie_httponly', 'name' => 'Use HTTP Only', 'enabled_flag' => true),
            // 1, WEBSITE
            array('path' => 'web/cookie/cookie_restriction', 'name' =>'Cookie Restriction Mode','enabled_flag' => true),
            // 1, GLOBAL
            array(
                'path' => 'web/session/use_remote_addr',
                'name' => 'Validate REMOTE_ADDR',
                'enabled_flag' => true
            ),
            // 1, GLOBAL
            array(
                'path' => 'web/session/use_http_via',
                'name' => 'Validate HTTP_VIA',
                'enabled_flag' => true
            ),
            // 1, GLOBAL
            array(
                'path' => 'web/session/use_http_x_forwarded_for',
                'name' => 'Validate HTTP_X_FORWARDED_FOR',
                'enabled_flag' => true
            ),
            // 1, GLOBAL
            array(
                'path' => 'web/session/use_http_user_agent',
                'name' => 'Validate HTTP_USER_AGENT',
                'enabled_flag' => true
            ),
            // 1, WEBSITE
            array('path' => 'web/session/use_frontend_sid', 'name' => 'Use SID on Frontend', 'enabled_flag' => true),
        );

        $data = array();
        $configData = $this->_getConfigValues($configPaths);
        foreach ($configData as $info) {
            $data[] = array(
                $info['name'],
                $info['enabled'],
                $info['value'],
                $info['scope']
            );
        }

        $systemReport = array();
        $systemReport['Configuration'] = array(
            'header' => array('Name', 'Enabled', 'Value', 'Scope'),
            'data' => $data
        );

        return $systemReport;
    }


    /**
     * Collect configuration values by specified config paths
     *
     * @param array $configPaths
     *
     * @return array
     * @throws Exception
     */
    protected function _getConfigValues(array $configPaths)
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Config data for stores can\'t be retrieved.');
        }
        $configData = array();
        $stores = Mage::app()->getStores();

        foreach ($configPaths as $info) {
            try {
                $_configData = $this->_prepareConfigData($info);
                if (!is_null($_configData)) {
                    $configData[] = $_configData;
                    continue;
                }

                $originalDefaultValue = Mage::getStoreConfig($info['path'], Mage_Core_Model_App::DISTRO_STORE_ID);
                $originalDefaultValue = $this->_prepareConfigValue($info, $originalDefaultValue);
                $configData[] = array(
                    'name' => $info['name'],
                    'enabled' => (isset($info['enabled_flag']) ? ($originalDefaultValue ? 'Yes' : 'No') : ''),
                    'value' => isset($info['enabled_flag']) ? '' : $originalDefaultValue,
                    'scope' => '[Default]',
                    'extra' => isset($info['extra']) ? $info['extra'] : null
                );

                // Then determine values which are different from default one
                /** @var $store Mage_Core_Model_Store */
                foreach ($stores as $store) {
                    $value = Mage::getStoreConfig($info['path'], $store);
                    $value = $this->_prepareConfigValue($info, $value);
                    if ($value == $originalDefaultValue) {
                        continue;
                    }
                    $configData[] = array(
                        'name' => $info['name'],
                        'enabled' => (isset($info['enabled_flag']) ? ($value ? 'Yes' : 'No') : ''),
                        'value' => isset($info['enabled_flag']) ? '' : $value,
                        'scope' => '['. $store->getWebsite()->getName() . '] -> ['
                            . $store->getGroup()->getName()   . '] -> [' . $store->getName() . ']',
                        'extra' => isset($info['extra']) ? $info['extra'] : null
                    );
                }
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        return $configData;
    }

    /**
     * Prepare config value
     *
     * @param array $configInfo
     * @param string $value
     *
     * @return string
     */
    protected function _prepareConfigValue($configInfo, $value)
    {
        if (substr($configInfo['path'], 0, 7) == 'design/' && empty($value)) {
            if ($configInfo['path'] == 'design/package/name') {
                $value = Mage_Core_Model_Design_Package::BASE_PACKAGE;
            } else {
                $value = Mage_Core_Model_Design_Package::DEFAULT_THEME;
            }
        }

        if ($configInfo['path'] == 'catalog/search/engine') {
            switch ($value) {
                case 'catalogsearch/fulltext_engine':
                    $value = 'MySQL Fulltext';
                    break;
                case 'enterprise_search/engine':
                    $value = 'Solr';
                    break;
                default:
                    break;

            }
        }

        return $value;
    }

    /**
     * Prepare config data
     *
     * @param array $configInfo
     * @return array|null
     */
    protected function _prepareConfigData($configInfo)
    {
        if ($configInfo['path'] == 'compiler') {
            $enabled = $this->_isCompilerEnabled() ? 'Yes' : 'No';
            return array(
                'name' => $configInfo['name'],
                'enabled' => $enabled,
                'value' => '',
                'scope' => '[Default]',
                'extra' => null
            );
        }

        if ($configInfo['path'] == 'table_prefix') {
            return array(
                'name' => $configInfo['name'],
                'enabled' => '',
                'value' => (string) Mage::getConfig()->getTablePrefix(),
                'scope' => '[Default]',
                'extra' => null
            );
        }

        if ($configInfo['path'] == 'solr_engine') {
            $value = Mage::getStoreConfig('catalog/search/engine', Mage_Core_Model_App::ADMIN_STORE_ID);
            return array(
                'name' => $configInfo['name'],
                'enabled' => $value == 'enterprise_search/engine' ? 'Yes' : 'No',
                'value' => '',
                'scope' => '[Default]',
                'extra' => null
            );
        }

        if ($configInfo['path'] == 'maintenance_mode') {
            $maintenanceFile = $this->_getRootPath() . self::REPORT_MAINTENANCE_MODE_FLAG_FILE_NAME;
            $value = is_file($maintenanceFile);
            return array(
                'name' => $configInfo['name'],
                'enabled' => $value ? 'Yes' : 'No',
                'value' => '',
                'scope' => '[Default]',
                'extra' => null
            );
        }

        return null;
    }

    /**
     * Generate app/etc/local.xml configuration data
     *
     * @param array $arguments
     *
     * @return array
     */
    protected function _generateEtcLocalXmlData(array $arguments = array())
    {
        $data = array();
        $xmlFile = Mage::getBaseDir('etc') . DIRECTORY_SEPARATOR . 'local.xml';
        $parsedXmlData = $this->_loadAndParseXmlConfigFile($xmlFile);
        foreach ($parsedXmlData as $key => $value) {
            $data[] = array($key, $value);
        }

        $systemReport = array();
        $systemReport['Data from app/etc/local.xml'] = array(
            'header' => array('Path', 'Value'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate app/etc/config.xml configuration data
     *
     * @param array $arguments
     *
     * @return array
     */
    protected function _generateEtcConfigXmlData(array $arguments = array())
    {
        $data = array();
        $xmlFile = Mage::getBaseDir('etc') . DIRECTORY_SEPARATOR . 'config.xml';
        $parsedXmlData = $this->_loadAndParseXmlConfigFile($xmlFile);
        foreach ($parsedXmlData as $key => $value) {
            $data[] = array($key, $value);
        }

        $systemReport = array();
        $systemReport['Data from app/etc/config.xml'] = array(
            'header' => array('Path', 'Value'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate app/etc/enterprise.xml configuration data
     *
     * @param array $arguments
     *
     * @return array
     */
    protected function _generateEtcEnterpriseXmlData(array $arguments = array())
    {
        $data = array();
        $xmlFile = Mage::getBaseDir('etc') . DIRECTORY_SEPARATOR . 'enterprise.xml';
        $parsedXmlData = $this->_loadAndParseXmlConfigFile($xmlFile);
        foreach ($parsedXmlData as $key => $value) {
            $data[] = array($key, $value);
        }

        $systemReport = array();
        $systemReport['Data from app/etc/enterprise.xml'] = array(
            'header' => array('Path', 'Value'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Load and parse local.xml config file
     *
     * @param $file
     *
     * @return array
     */
    protected function _loadAndParseXmlConfigFile($file)
    {
        static $parsedXmlData = array();
        if (!isset($parsedXmlData[$file])) {
            if (!is_readable($file)) {
                return array();
            }
            $xmlObject = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
            $parsedXmlData[$file] = $this->_parseXmlObject($xmlObject);
        }

        return $parsedXmlData[$file];
    }

    /**
     * Parse XML tree
     *
     * @param SimpleXMLElement $xmlObject
     * @param bool $resetStaticData
     *
     * @return array
     */
    protected function _parseXmlObject($xmlObject, $resetStaticData = true)
    {
        static $nodeLevel = 0;
        if ($resetStaticData) {
            $nodeLevel = 0;
        }
        $data = array();
        $indent = str_repeat(' ', $nodeLevel * 4);
        /** @var $value SimpleXMLElement */
        foreach ($xmlObject as $key => $value) {
            if (sizeof($value->children()) > 0) {
                $data[$indent . '<' . $key . '>'] = '';
                $nodeLevel++;
                $data = array_merge($data, $this->_parseXmlObject($value, false));
            } else {
                $data[$indent . '<' . $key . '>'] = in_array($key, $this->_xmlConfigRestrictedFields)
                    ? '****'
                    : (string) $value;
            }
        }
        $nodeLevel--;

        return $data;
    }


    /**
     * Generate Shipping Methods information
     *
     * @return array
     */
    protected function _generateShippingMethodsData()
    {
        $data = $configPaths = array();
        $methods = Mage::app()->getConfig()->getNode('default/carriers');
        if ($methods) {
            foreach ($methods->children() as $code => $info) {
                if ((string)$code == 'googlecheckout') {
                    $codes = array(
                        'checkout_shipping_merchant' => 'Google Checkout Shipping - Merchant Calculated',
                        'checkout_shipping_carrier'  => 'Google Checkout Shipping - Carrier Calculated',
                        'checkout_shipping_flatrate' => 'Google Checkout Shipping - Flat Rate',
                        'checkout_shipping_virtual'  => 'Google Checkout Shipping - Digital Delivery',
                    );
                    foreach ($codes as $_code => $title) {
                        $configPaths[] = array(
                            'path' => 'google/' . $_code . '/active',
                            'name' => $title,
                            'enabled_flag' => true,
                            'extra' => array(
                                'code' => $_code,
                                'title' => $title,
                                'name' => ''
                            )
                        );
                    }
                    continue;
                }
                $configPaths[] = array(
                    'path' => 'carriers/' . $code . '/active',
                    'name' => (string)$info->title ? (string)$info->title : $code,
                    'enabled_flag' => true,
                    'extra' => array(
                        'code' => (string)$code,
                        'title' => (string)$info->title,
                        'name' => (string)$info->name
                    )
                );
            }
        }
        $configData = $this->_getConfigValues($configPaths);
        foreach ($configData as $path) {
            $data[] = array(
                $path['extra']['code'],
                $path['extra']['name'],
                $path['extra']['title'],
                $path['enabled'],
                $path['scope']
            );
        }

        $systemReport = array();
        $systemReport['Shipping Methods'] = array(
            'header' => array('Code', 'Name', 'Title', 'Enabled', 'Scope'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate Payment Methods information
     *
     * @return array
     */
    protected function _generatePaymentMethodsData()
    {
        $methods = Mage::app()->getConfig()->getNode('default/payment');
        $firstMethods = $nextMethods = $methodsConfig = array();
        if (!$methods) {
            return array();
        }

        foreach ($methods->children() as $code => $info) {
            if (substr($code, 0, 8) == 'pbridge_' && $code != 'pbridge_ogone_direct') {
                continue;
            }
            $scopes = array();

            $group = (string)$info->group;
            if (!$group && $code != 'authorizenet') {
                $group = substr($code, 0, 7) == 'pbridge' ? 'pbridge' : '';
                $group = $group ? $group : ($info->using_pbridge ? 'pbridge' : '');
            }

            $path = 'payment/' . $code . '/active';
            if ($code == 'googlecheckout') {
                $path = 'google/checkout/active';
            }
            $isEnabledValues = $this->_getConfigValues(
                array(
                    array(
                        'path' => $path,
                        'name' => (string)$code,
                        'enabled_flag' => true,
                    )
                )
            );
            foreach ($isEnabledValues as $value) {
                $scopes[] = $value['scope'];
                $isEnabledValues[$value['scope']] = $value;
            }
            $viaPBridgeValues = $this->_getConfigValues(
                array(
                    array(
                        'path' => 'payment/' . $code . '/using_pbridge',
                        'name' => (string)$code,
                        'enabled_flag' => true,
                    )
                )
            );
            foreach ($viaPBridgeValues as $value) {
                $scopes[] = $value['scope'];
                $viaPBridgeValues[$value['scope']] = $value;
            }
            $methodsConfig = array(
                'code' => (string)$code,
                'title' => (string)$info->title,
                'group' => $group,
                'enabled' => $isEnabledValues,
                'viapbridge' => $viaPBridgeValues,
                'scopes' => array_merge(array_unique($scopes), array()),
            );

            if ($group == '' || $group == 'offline' || $group == 'pbridge') {
                $firstMethods[] = $methodsConfig;
            } else {
                $nextMethods[] = $methodsConfig;
            }
        }

        $methodsConfig = array_merge($firstMethods, $nextMethods);
        $data = array();
        foreach ($methodsConfig as $config) {
            foreach ($config['scopes'] as $scope) {
                $data[] = array(
                    $config['code'],
                    $config['group'],
                    $config['title'],
                    isset($config['enabled'][$scope]['enabled'])
                        ? $config['enabled'][$scope]['enabled']
                        : $config['enabled']['[Default]']['enabled'],
                    isset($config['viapbridge'][$scope]['enabled'])
                        ? $config['viapbridge'][$scope]['enabled']
                        : $config['viapbridge']['[Default]']['enabled'],
                    $scope,
                );
            }
        }

        $systemReport = array();
        $systemReport['Payment Methods'] = array(
            'header' => array('Code', 'Group', 'Title', 'Enabled', 'VIA PBridge', 'Scope'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate Payments Functionality Matrix
     * Data will be collected only in PHP >= 5.3.0, because required method ReflectionProperty::setAccessible()
     * was implemented in PHP 5.3.0
     *
     * Supported Payments Info:
     * - Code
     * - Name
     * - Group
     * - Is Gateway
     * - Can Void
     * - Is Used For Checkout
     * - Is Used For Multishipping
     * - Capture Online
     * - Partial Capture Online
     * - Refund Online
     * - Partial Refund Online
     * - Capture Offline
     * - Partial Capture Offline
     * - Refund Offline
     * - Partial Refund Offline
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generatePaymentsFunctionalityMatrixData(array $arguments = array())
    {
        $systemReport = $data = array();
        /**
         * ReflectionProperty::setAccessible was implemented in PHP 5.3.0
         *
         * @link http://de2.php.net/manual/en/reflectionproperty.setaccessible.php
         */
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $message = 'Payments Functionality Matrix is not available. ' .
                'ReflectionProperty::setAccessible is required for data collection, ' .
                'but it was implemented in PHP 5.3.0; your PHP version is lower.';
            $systemReport['Payments Functionality Matrix'] = array(
                'header' => array('Warning'),
                'data' => array(array($message))
            );

            return $systemReport;
        }

        $methods = Mage::app()->getConfig()->getNode('default/payment');
        if ($methods) {
            $methods = $methods->children();
            foreach ($methods as $code => $info) {
                try {
                    $name = (string)$info->title ? (string)$info->title : 'n/a';
                    $group = (string)$info->group;
                    if (!$group && $code != 'authorizenet') {
                        $group = substr($code, 0, 7) == 'pbridge' ? 'pbridge' : '';
                        $group = $group ? $group : ($info->using_pbridge ? 'pbridge' : '');
                    }

                    if (substr($code, 0, 8) == 'pbridge_' && $code != 'pbridge_ogone_direct') {
                        continue;
                    }

                    /** @var $paymentHelper Mage_Payment_Helper_Data */
                    $paymentHelper = Mage::helper('payment');
                    $payment = $paymentHelper->getMethodInstance($code);
                    // Try to define if method proxies through Payment Bridge,
                    // if yes then collect data for PB payment method
                    if (!$payment) {
                        $pBridgePaymentMethodCode = 'pbridge_' . $code;
                        $payment = $paymentHelper->getMethodInstance($pBridgePaymentMethodCode);
                    }

                    if (!$payment) {
                        $data[] = array(
                            $code, $name, $group,
                            'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a'
                        );
                        continue;
                    }
                    $reflectionPayment = new ReflectionObject($payment);

                    $isGateway = $reflectionPayment->getProperty('_isGateway');
                    $isGateway->setAccessible(true);

                    $canVoid = $reflectionPayment->getProperty('_canVoid');
                    $canVoid->setAccessible(true);
                    $canUseCheckout = $reflectionPayment->getProperty('_canUseCheckout');
                    $canUseCheckout->setAccessible(true);
                    $canUseForMultishipping = $reflectionPayment->getProperty('_canUseForMultishipping');
                    $canUseForMultishipping->setAccessible(true);

                    $canCapture = $reflectionPayment->getProperty('_canCapture');
                    $canCapture->setAccessible(true);
                    $canCapturePartial = $reflectionPayment->getProperty('_canCapturePartial');
                    $canCapturePartial->setAccessible(true);


                    $canRefund = $reflectionPayment->getProperty('_canRefund');
                    $canRefund->setAccessible(true);
                    $canRefundInvoicePartial = $reflectionPayment->getProperty('_canRefundInvoicePartial');
                    $canRefundInvoicePartial->setAccessible(true);

                    $data[] = array(
                        $code,
                        $name,
                        $group,
                        $isGateway->getValue($payment) ? 'Yes' : 'No',
                        $canVoid->getValue($payment) ? 'Yes' : 'No',
                        $canUseCheckout->getValue($payment) ? 'Yes' : 'No',
                        $canUseForMultishipping->getValue($payment) ? 'Yes' : 'No',

                        $canCapture->getValue($payment) ? 'Yes' : 'No',
                        $canCapture->getValue($payment) && $canCapturePartial->getValue($payment) ? 'Yes' : 'No',
                        $canRefund->getValue($payment) ? 'Yes' : 'No',
                        $canRefund->getValue($payment) && $canRefundInvoicePartial->getValue($payment) ? 'Yes' : 'No',

                        'Yes',
                        $canCapture->getValue($payment) && $canCapturePartial->getValue($payment) ? 'Yes' : 'No',
                        'Yes',
                        $canRefund->getValue($payment) && $canRefundInvoicePartial->getValue($payment) ? 'Yes' : 'No',
                    );
                } catch (Exception $e) {
                    $this->_log($e);
                }
            }
        }

        $systemReport['Payments Functionality Matrix'] = array(
            'header' => array(
                'Code',
                'Title',
                'Group',
                'Is Gateway',
                'Void',
                'For Checkout',
                'For Multishipping',
                'Capture Online',
                'Partial Capture Online',
                'Refund Online',
                'Partial Refund Online',
                'Capture Offline',
                'Partial Capture Offline',
                'Refund Offline',
                'Partial Refund Offline'
            ),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate log files entries count and log files sizes
     * Generate top messages and their last occurrence dates
     *
     * @return array
     */
    protected function _generateLogFilesData()
    {
        $systemReport     = array();
        $logDir           = Mage::getBaseDir('log') . DIRECTORY_SEPARATOR;
        $directoryHandler = opendir($logDir);
        $data             = $exceptions = $sysMessages = $currentSystemMessages = $currentExceptionMessages = array();
        $filesCount       = 0;
        $currentDate      = date('Y-m-d');
        $systemLogFile    = Mage::getStoreConfig('dev/log/file');
        $exceptionLogFile = Mage::getStoreConfig('dev/log/exception_file');

        if ($directoryHandler) {
            clearstatcache();
            while (($entry = readdir($directoryHandler)) !== false
                && $filesCount <= self::TABLE_DATA_ROW_MAXIMUM_COUNT_FOR_OUTPUT
            ) {
                $file = $logDir . $entry;

                // Take into account only files with "log" extension
                if (!is_file($file) || substr($entry, strrpos($entry, '.') + 1) != 'log') {
                    continue;
                }

                $logEntriesNumber = 0;
                $fileSize = $this->_getFileSize($file);

                $exceptionStarted = $exceptionEnded = false;
                $exceptionMessage = $exceptionStack = '';

                // If file is not too big then calculate log entries number
                if ($fileSize <= self::MAX_FILE_SIZE_TO_OPEN_FOR_LOG_ENTRIES_CALC && is_readable($file)) {
                    $lines = 0;
                    // To use just small portion of memory fgets() must be used
                    $handle = fopen($file, 'r');
                    while (!feof($handle)) {
                        // But sometimes file can contain long one line which can be very huge,
                        // so defend against such case by reading just 4 KB of data per line
                        $line = fgets($handle, 4096);
                        // This is regular expression for Zend produced log file entries
                        // like 2012-05-11T06:04:45+00:00 ERR (3): ...
                        $matched = (int)preg_match('~^[-0-9]+T[:0-9]+[-+][:0-9]+[^\(]+\([^\)]+\).+$~im', $line);
                        $logEntriesNumber += $matched;

                        // Collect system log messages
                        if ($entry == $systemLogFile
                            && preg_match(
                                '~^([-0-9]+)T([:0-9]+)([-+][:0-9]+)[^\(]+\([^\)]+\):\s(.+)$~im', $line, $matches
                            )
                        ) {
                            $lastDate = $matches[1] . ', ' . $matches[2] . ' [' . $matches[3] . ']';
                            if (!isset($sysMessages[$matches[4]])) {
                                $sysMessages[$matches[4]] = array('count' => 1, 'last_occurrence_date' => $lastDate);
                            } else {
                                $sysMessages[$matches[4]]['count']++;
                                $sysMessages[$matches[4]]['last_occurrence_date'] = $lastDate;
                            }

                            if ($matches[1] == $currentDate) {
                                if (!isset($currentSystemMessages[$matches[4]])) {
                                    $currentSystemMessages[$matches[4]] = array(
                                        'count' => 1,
                                        'last_occurrence_date' => $lastDate
                                    );
                                } else {
                                    $currentSystemMessages[$matches[4]]['count']++;
                                    $currentSystemMessages[$matches[4]]['last_occurrence_date'] = $lastDate;
                                }
                            }
                        }

                        // Collect exception log messages
                        if ($entry == $exceptionLogFile) {
                            // Record date
                            if ($exceptionStarted === false
                                && preg_match('~^([-0-9]+)T([:0-9]+)([-+][:0-9]+)[^\(]+\([^\)]+\).+$~im',$line,$matches)
                            ) {
                                $exceptionStarted = true;
                                $lastDate = $matches[1] . ', ' . $matches[2] . ' [' . $matches[3] . ']';
                                $_exceptionDatePart = $matches[1];
                            }

                            // Record message
                            if ($exceptionStarted === true && $exceptionMessage === ''
                                && (preg_match('~^exception (.+)$~im', $line, $matches)
                                    ||
                                    preg_match('~Exception message:\s*(.+)$~im', $line, $matches)
                                )
                            ) {
                                $exceptionMessage = $matches[1];
                            }

                            // Record exception end flag
                            if ($exceptionEnded === false && $exceptionStarted === true
                                && preg_match('~^\#[0-9]+ \{main\}$~im', $line)
                            ) {
                                $exceptionEnded = true;
                            }

                            // Record stack trace
                            if ($exceptionEnded !== true && $exceptionMessage !== ''
                                && !preg_match('~^(?:Stack trace|Trace)\:\s*$~im', $line)
                                && !preg_match('~^exception .+$~im', $line)
                                && !preg_match('~Exception message:\s*(.+)$~im', $line)
                            ) {
                                $exceptionStack .= $line;
                            }

                            // Add exception data
                            if ($exceptionStarted === true && $exceptionEnded === true) {
                                if (!isset($exceptions[$exceptionMessage])) {
                                    $exceptions[$exceptionMessage] = array(
                                        'count' => 1,
                                        'last_occurrence_date' => $lastDate,
                                        'exception_stack' => $exceptionStack
                                    );
                                } else {
                                    $exceptions[$exceptionMessage]['count']++;
                                    $exceptions[$exceptionMessage]['last_occurrence_date'] = $lastDate;
                                    $exceptions[$exceptionMessage]['exception_stack'] = $exceptionStack;
                                }

                                if ($_exceptionDatePart == $currentDate) {
                                    if (!isset($currentExceptionMessages[$exceptionMessage])) {
                                        $currentExceptionMessages[$exceptionMessage] = array(
                                            'count' => 1,
                                            'last_occurrence_date' => $lastDate,
                                            'exception_stack' => $exceptionStack
                                        );
                                    } else {
                                        $currentExceptionMessages[$exceptionMessage]['count']++;
                                        $currentExceptionMessages[$exceptionMessage]['last_occurrence_date']=$lastDate;
                                        $currentExceptionMessages[$exceptionMessage]['exception_stack']=$exceptionStack;
                                    }
                                }

                                $exceptionStarted = $exceptionEnded = false;
                                $exceptionMessage = $exceptionStack = '';
                            }
                        }

                        // For long files output progress
                        if ($lines % 50000 == 0) {
                            $this->_log(null, 'File "' . $entry . '": ' . $lines . ' lines processed...');
                        }
                        if (substr_count($line, "\n") || substr_count($line, "\r")) {
                            $lines++;
                        }
                    }
                    fclose($handle);
                }

                $entriesNumber = $logEntriesNumber;
                if ($fileSize > self::MAX_FILE_SIZE_TO_OPEN_FOR_LOG_ENTRIES_CALC) {
                    $entriesNumber = 'File is too big';
                }
                if (!is_readable($file)) {
                    $entriesNumber = 'File is not readable';
                }

                $data[] = array(
                    $entry,
                    $this->_formatBytes($fileSize, 3, 'IEC'),
                    $entriesNumber,
                    date('r', filemtime($file))
                );
                $filesCount++;
            }
            closedir($directoryHandler);
        }

        // Log Files
        $systemReport['Log Files'] = array(
            'header' => array('File', 'Size', 'Log Entries', 'Last Update'),
            'data' => $data
        );

        // Top System Messages
        $systemReport['Top System Messages'] = array(
            'header' => array('Count', 'Message', 'Last Occurrence'),
            'data' => $this->_prepareSystemMessagesReportData($sysMessages),
        );

        // Today's Top System Messages
        $systemReport['Today\'s Top System Messages'] = array(
            'header' => array('Count', 'Message', 'Last Occurrence'),
            'data' => $this->_prepareSystemMessagesReportData($currentSystemMessages),
        );

        // Top Exception Messages
        $systemReport['Top Exception Messages'] = array(
            'header' => array('Count', 'Message', 'Stack Trace', 'Last Occurrence'),
            'data' => $this->_prepareExceptionMessagesReportData($exceptions),
        );

        // Today's Top Exception Messages
        $systemReport['Today\'s Top Exception Messages'] = array(
            'header' => array('Count', 'Message', 'Stack Trace', 'Last Occurrence'),
            'data' => $this->_prepareExceptionMessagesReportData($currentExceptionMessages),
        );

        return $systemReport;
    }

    /**
     * Sort and prepare top system messages data for report
     *
     * @param array $messagesData
     * @return array
     */
    protected function _prepareSystemMessagesReportData($messagesData)
    {
        $data = array();
        if (empty($messagesData)) {
            return $data;
        }

        $counts = array();
        foreach ($messagesData as $key => $messageData) {
            $counts[$key]  = $messageData['count'];
        }

        array_multisort($counts, SORT_DESC, $messagesData);

        $i = 0;
        foreach ($messagesData as $message => $messageData) {
            if ($i == self::TOP_SYSTEM_LOG_MESSAGES_NUMBER_TO_REPORT) {
                break;
            }
            $data[] = array(
                $messageData['count'],
                $message,
                $messageData['last_occurrence_date']
            );
            $i++;
        }

        return $data;
    }

    /**
     * Sort and prepare top system messages data for report
     *
     * @param array $messagesData
     * @return array
     */
    protected function _prepareExceptionMessagesReportData($messagesData)
    {
        $data = array();
        if (empty($messagesData)) {
            return $data;
        }
        $counts = array();
        foreach ($messagesData as $key => $messageData) {
            $counts[$key]  = $messageData['count'];
        }

        array_multisort($counts, SORT_DESC, $messagesData);

        $i = 0;
        foreach ($messagesData as $message => $messageData) {
            if ($i == self::TOP_EXCEPTION_LOG_MESSAGES_NUMBER_TO_REPORT) {
                break;
            }
            $data[] = array(
                $messageData['count'],
                $message,
                $messageData['exception_stack'],
                $messageData['last_occurrence_date']
            );
            $i++;
        }

        return $data;
    }

    /**
     * Generate server environment information such as:
     * - OS Version
     * - Apache version
     * - Apache Loaded Modules
     * - PHP Version
     * - PHP Loaded Modules
     * - PHP Major Configuration Values
     * - MySQL Server Version (retrieved from DB adapter)
     * - MySQL Supported Engines (took into account only enabled engines)
     * - MySQL Databases Present
     * - MySQL Plugins
     * - MySQL Major Global Variables
     *
     * Current method uses cURL request to sysreport.php tool (web mode only for phpinfo).
     * Sometimes it is not allowed or not applicable to request sysreport.php from outside. In this case information
     * will be not complete.
     *
     * @return array
     * @throws Exception
     */
    protected function _generateEnvironmentData()
    {
        $data = array();
        $count = 0;
        try {
            $phpInfo = $this->_collectPHPInfo();
        } catch (Exception $e) {
            $this->_log($e);
            $phpInfo = null;
        }
        if ($phpInfo === null) {
            $this->_log(null, 'So Environment Information will not be fully collected.');
        }

        if (is_array($phpInfo) && !empty($phpInfo)) {
            if (isset($phpInfo['General']['System'])) {
                $data[] = array('OS Information', $phpInfo['General']['System']);
                $count++;
            }
            if (isset($phpInfo['apache2handler']['Apache Version'])) {
                $data[] = array('Apache Version', $phpInfo['apache2handler']['Apache Version']);
                $count++;
            }
            if (isset($phpInfo['Apache Environment']['DOCUMENT_ROOT'])) {
                $data[] = array('Document Root', $phpInfo['Apache Environment']['DOCUMENT_ROOT']);
                $count++;
            } else if (isset($phpInfo['PHP Variables']['_SERVER["DOCUMENT_ROOT"]'])) {
                $data[] = array('Document Root', $phpInfo['PHP Variables']['_SERVER["DOCUMENT_ROOT"]']);
                $count++;
            }
            if (isset($phpInfo['Apache Environment']['SERVER_ADDR'])
                && isset($phpInfo['Apache Environment']['SERVER_PORT'])
            ) {
                $data[] = array(
                    'Server Address',
                    $phpInfo['Apache Environment']['SERVER_ADDR'] . ':' . $phpInfo['Apache Environment']['SERVER_PORT']
                );
                $count++;
            } else if (isset($phpInfo['PHP Variables']['_SERVER["SERVER_ADDR"]'])
                && isset($phpInfo['PHP Variables']['_SERVER["SERVER_PORT"]'])
            ) {
                $data[] = array(
                    'Server Address',
                    $phpInfo['PHP Variables']['_SERVER["SERVER_ADDR"]'] . ':' .
                    $phpInfo['PHP Variables']['_SERVER["SERVER_PORT"]']
                );
                $count++;
            }
            if (isset($phpInfo['Apache Environment']['REMOTE_ADDR'])
                && isset($phpInfo['Apache Environment']['REMOTE_PORT'])
            ) {
                $data[] = array(
                    'Remote Address',
                    $phpInfo['Apache Environment']['REMOTE_ADDR'] . ':' . $phpInfo['Apache Environment']['REMOTE_PORT']
                );
                $count++;
            } else if (isset($phpInfo['PHP Variables']['_SERVER["REMOTE_ADDR"]'])
                && isset($phpInfo['PHP Variables']['_SERVER["REMOTE_PORT"]'])
            ) {
                $data[] = array(
                    'Remote Address',
                    $phpInfo['PHP Variables']['_SERVER["REMOTE_ADDR"]'] . ':' .
                    $phpInfo['PHP Variables']['_SERVER["REMOTE_PORT"]']
                );
                $count++;
            }
        }
        // Apache Loaded Modules
        if (is_array($phpInfo) && !empty($phpInfo) && isset($phpInfo['apache2handler']['Loaded Modules'])) {
            $modulesInfo = '';
            $modules = explode(' ', $phpInfo['apache2handler']['Loaded Modules']);
            foreach ($modules as $module) {
                $modulesInfo .= $module . "\n";
            }
            $data[] = array('Apache Loaded Modules', trim($modulesInfo));
            $count++;
        }

        try {
            // DB (MySQL) Server Version
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. MySQL version can\'t be retrieved.');
            }

            $data[] = array('MySQL Server Version', $this->_readConnection->getServerVersion());
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('MySQL Server Version', 'n/a');
        }
        $count++;

        // MySQL Enabled and Supported Engines
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. MySQL supported engines list can\'t be retrieved.');
            }

            $engines = $this->_readConnection->fetchAll('SHOW ENGINES');
            $supportedEngines = '';

            if ($engines) {
                foreach ($engines as $engine) {
                    if ($engine['Support'] != 'NO' && $engine['Engine'] != 'DISABLED') {
                        $supportedEngines .= $engine['Engine'] . '; ';
                    }
                }
            }
            $data[] = array('MySQL Supported Engines', $supportedEngines);
            unset($engines, $supportedEngines);
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('MySQL Supported Engines', 'n/a');
        }
        $count++;

        // MySQL Databases amount
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. Database number can\'t be collected.');
            }

            $databases = $this->_readConnection->fetchAll('SHOW DATABASES');
            $dbNumber = $databases ? sizeof($databases) : 0;
            $data[] = array('MySQL Databases Present', $dbNumber);
            unset($databases);
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('MySQL Databases Present', 'n/a');
        }
        $count++;

        // MySQL Configuration
        $importantConfig = array(
            'datadir',
            'default_storage_engine',
            'general_log',
            'general_log_file',
            'innodb_buffer_pool_size',
            'innodb_io_capacity',
            'innodb_log_file_size',
            'innodb_thread_concurrency',
            'innodb_flush_log_at_trx_commit',
            'innodb_open_files',
            'join_buffer_size',
            'key_buffer_size',
            'max_allowed_packet',
            'max_connect_errors',
            'max_connections',
            'max_heap_table_size',
            'query_cache_size',
            'query_cache_limit',
            'read_buffer_size',
            'skip_name_resolve',
            'slow_query_log',
            'slow_query_log_file',
            'sync_binlog',
            'table_open_cache',
            'tmp_table_size',
            'wait_timeout',
            'version',
        );
        $maxSettingNameLength = 0;
        foreach ($importantConfig as $settingName) {
            $length = strlen($settingName);
            if ($length > $maxSettingNameLength) {
                $maxSettingNameLength = $length;
            }
        }
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. MySQL config settings can\'t be collected.');
            }

            $variables = $this->_readConnection->fetchAssoc('SHOW GLOBAL VARIABLES');
            if ($variables) {
                $configuration = '';
                foreach ($variables as $variable) {
                    if (!in_array($variable['Variable_name'], $importantConfig)) {
                        continue;
                    }
                    if (substr($variable['Variable_name'], -4) == 'size') {
                        $variable['Value'] = $this->_formatBytes($variable['Value'], 3, 'IEC');
                    }
                    $indent = str_repeat(' ', $maxSettingNameLength - strlen($variable['Variable_name']) + 4);
                    $configuration .= $variable['Variable_name'] . $indent . ' => "' . $variable['Value'] . '"' . "\n";
                }
                $data[] = array('MySQL Configuration', trim($configuration));
            } else {
                $data[] = array('MySQL Configuration', 'n/a');
            }
            unset($variables);
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('MySQL Configuration', 'n/a');
        }
        $count++;

        // MySQL Plugins
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. MySQL plugins list can\'t be retrieved.');
            }

            $plugins = $this->_readConnection->fetchAssoc('SHOW PLUGINS');
            $installedPlugins = '';

            if ($plugins) {
                foreach ($plugins as $plugin) {
                    $installedPlugins .= ($plugin['Status'] == 'DISABLED' ? '-disabled- ' : '') .
                        $plugin['Name'] . "\n";
                }
            }
            $data[] = array('MySQL Plugins', trim($installedPlugins));
            unset($plugins, $installedPlugins);
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('MySQL Plugins', 'n/a');
        }
        $count++;

        // PHP Version
        $data[] = array('PHP Version', PHP_VERSION);
        $count++;

        if (is_array($phpInfo) && !empty($phpInfo)) {
            if (isset($phpInfo['General']['Loaded Configuration File'])) {
                $data[] = array('PHP Loaded Config File', $phpInfo['General']['Loaded Configuration File']);
                $count++;
            }
            if (isset($phpInfo['General']['Additional .ini files parsed'])) {
                $data[] =array('PHP Additional .ini files parsed', $phpInfo['General']['Additional .ini files parsed']);
                $count++;
            }
        }

        // PHP Important Config Settings
        $importantConfig = array(
            'memory_limit',
            'register_globals',
            'safe_mode',
            'upload_max_filesize',
            'post_max_size',
            'allow_url_fopen',
            'default_charset',
            'error_log',
            'error_reporting',
            'extension_dir',
            'file_uploads',
            'upload_tmp_dir',
            'log_errors',
            'magic_quotes_gpc',
            'max_execution_time',
            'max_file_uploads',
            'max_input_time',
            'max_input_vars',
        );
        $maxSettingNameLength = 0;
        foreach ($importantConfig as $settingName) {
            $length = strlen($settingName);
            if ($length > $maxSettingNameLength) {
                $maxSettingNameLength = $length;
            }
        }
        if (is_array($phpInfo) && !empty($phpInfo)) {
            $coreEntry = isset($phpInfo['Core'])
                ? $phpInfo['Core']
                : (isset($phpInfo['PHP Core']) ? $phpInfo['PHP Core'] : null);
            if ($coreEntry !== null) {
                $configuration = '';
                foreach ($coreEntry as $key => $info) {
                    if (in_array($key, $importantConfig)) {
                        $indent = str_repeat(' ', $maxSettingNameLength - strlen($key) + 4);
                        $configuration .= $key . $indent . ' => Local = "' . $info['local'] .
                            '", Master = "' . $info['master'] . '"' . "\n";
                    }
                }
                $data[] = array('PHP Configuration', trim($configuration));
                $count++;
            }
        } else {
            $iniValues = ini_get_all();
            if (!empty($iniValues) && is_array($iniValues)) {
                $configuration = '';
                foreach ($iniValues as $key => $info) {
                    if (in_array($key, $importantConfig)) {
                        $configuration .= $key . ' => Local = "' . $info['local_value'] .
                            '", Master = "' . $info['global_value']. '"' . "\n";
                    }
                }
                $data[] = array('PHP Configuration', $configuration);
                $count++;
            }
        }

        try {
            /**
             * PHP Loaded Modules
             */
            $defaultPhpInfoCategories = array(
                'General',
                'apache2handler',
                'Apache Environment',
                'PHP Core',
                'Core',
                'HTTP Headers Information',
                'Environment',
                'PHP Variables'
            );
            if (is_array($phpInfo) && !empty($phpInfo)) {
                $modulesInfo = '';
                foreach ($phpInfo as $module => $info) {
                    if (!in_array($module, $defaultPhpInfoCategories)) {
                        // Collect additional information for required modules by Magento
                        switch ($module) {
                            case 'curl':
                                if (isset($info['cURL Information'])) {
                                    $module .= ' [' . $info['cURL Information'] . ']';
                                }
                                break;
                            case 'dom':
                                if (isset($info['libxml Version'])) {
                                    $module .= ' [' . $info['libxml Version'] . ']';
                                }
                                break;
                            case 'gd':
                                if (isset($info['GD Version'])) {
                                    $module .= ' [' . $info['GD Version'] . ']';
                                }
                                break;
                            case 'iconv':
                                if (isset($info['iconv library version'])) {
                                    $module .= ' [' . $info['iconv library version'] . ']';
                                }
                                break;
                            case 'mcrypt':
                                if (isset($info['Version'])) {
                                    $module .= ' [' . $info['Version'] . ']';
                                }
                                break;
                            case 'pdo_mysql':
                                if (isset($info['Client API version'])) {
                                    $module .= ' [' . $info['Client API version'] . ']';
                                } else if (isset($info['PDO Driver for MySQL, client library version'])) {
                                    $module .= ' [' . $info['PDO Driver for MySQL, client library version'] . ']';
                                }
                                break;
                            case 'SimpleXML':
                                if (isset($info['Revision'])) {
                                    $module .= ' [' . $info['Revision'] . ']';
                                }
                                break;
                            case 'soap':
                            case 'hash':
                            default:
                                $module .= phpversion($module) ? ' [' . phpversion($module) . ']' : '';
                                break;
                        }
                        $modulesInfo .= $module . "\n";
                    }
                }
                $data[] = array('PHP Loaded Modules', trim($modulesInfo));
                $count++;
            } else {
                $modules = get_loaded_extensions();
                if (is_array($modules) && !empty($modules)) {
                    $modules = array_map('strtolower', $modules);
                    sort($modules);
                    $modulesInfo = '';
                    foreach ($modules as $module) {
                        $modulesInfo .= $module . (phpversion($module) ? ' [' . phpversion($module) . ']' : '') . "\n";
                    }
                    $data[] = array('PHP Loaded Modules', trim($modulesInfo));
                    $count++;
                }
            }
        } catch (Exception $e) {
            $this->_log($e);
            $data[] = array('PHP Loaded Modules', 'n/a');
        }

        $systemReport = array();
        $systemReport['Environment Information'] = array(
            'header' => array('Parameter', 'Value'),
            'data' => $data,
            'count' => $count
        );

        return $systemReport;
    }

    /**
     * Generate MySQL status information.
     * Additionally generate MySQL status after 10 seconds delay to see the difference.
     *
     * @return array
     * @throws Exception
     */
    protected function _generateMysqlStatusData()
    {
        // MySQL Status
        $data = array();
        $importantConfig = array(
            'Aborted_clients',
            'Aborted_connects',
            'Com_select',
            'Connections',
            'Created_tmp_disk_tables',
            'Created_tmp_files',
            'Created_tmp_tables',
            'Handler_read_rnd_next',
            'Innodb_buffer_pool_read_requests',
            'Innodb_buffer_pool_write_requests',
            'Innodb_log_waits',
            'Innodb_log_write_requests',
            'Innodb_log_writes',
            'Open_files',
            'Open_streams',
            'Open_table_definitions',
            'Open_tables',
            'Opened_files',
            'Opened_table_definitions',
            'Opened_tables',
            'Qcache_lowmem_prunes',
            'Select_full_join',
            'Select_full_range_join',
            'Select_range',
            'Select_range_check',
            'Select_scan',
            'Slow_queries',
            'Slave_running',
            'Sort_range',
            'Sort_rows',
            'Sort_scan',
            'Table_locks_immediate',
            'Table_locks_waited',
            'Threads_cached',
            'Threads_connected',
            'Threads_created',
            'Threads_running',
        );
        try {
            if (!$this->_readConnection) {
                throw new Exception('Cant\'t connect to DB. MySQL Status data can\'t be collected.');
            }

            $variables = $this->_readConnection->fetchPairs('SHOW GLOBAL STATUS');
            $this->_log(null, '10 seconds wait time to collect MySQL status data:');
            for ($i = 1; $i <= 10; $i++) {
                $this->_log(null, $i . '...');
                sleep(1);
            }
            $variablesAfter10Sec = $this->_readConnection->fetchPairs('SHOW GLOBAL STATUS');
            if ($variables && $variablesAfter10Sec) {
                foreach ($variables as $name => $value) {
                    if (!in_array($name, $importantConfig)) {
                        continue;
                    }
                    $valueAfter10Sec = 'n/a';
                    if (isset($variablesAfter10Sec[$name])) {
                        $difference = '';
                        if (is_numeric($variablesAfter10Sec[$name])) {
                            $difference = $variablesAfter10Sec[$name] - $value;
                            if ($difference != 0) {
                                $difference = ' (diff: ' . ($difference > 0 ? '+' : '') . $difference . ')';
                            } else {
                                $difference = '';
                            }
                        }
                        $valueAfter10Sec = $variablesAfter10Sec[$name] . $difference;
                    }
                    $data[] = array($name, $value, $valueAfter10Sec);
                }
            }
            unset($variables, $variablesAfter10Sec);
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['MySQL Status'] = array(
            'header' => array('Variable', 'Value', 'Value after 10 sec'),
            'data' => $data,
        );

        return $systemReport;
    }

    /**
     * Convert phpinfo() HTML output into array and output it in serialized format
     *
     * @link http://www.php.net/manual/en/function.phpinfo.php#106862
     *
     * @return array
     */
    protected function _collectPHPInfo()
    {
        ob_start();
        phpinfo(INFO_ALL);
        $info = array();
        $infoLines = explode("\n", strip_tags(ob_get_clean(), '<tr><td><h2>'));

        $category = 'General';
        foreach($infoLines as $line) {
            if (preg_match('~<h2>(.*)</h2>~', $line, $title)) {
                $category = $title[1];
            }

            if (preg_match('~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~', $line, $value)) {
                $info[$category][trim($value[1])] = trim($value[2]);
            } else if(preg_match(
                '~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~',
                $line,
                $value
            )
            ) {
                $info[$category][trim($value[1])] = array('local' => $value[2], 'master' => $value[3]);
            }
        }

        return $info;
    }

    /**
     * Generate Cache Status information
     *
     * @return array
     */
    protected function _generateCacheStatusData()
    {
        $invalidated = Mage::app()->getCacheInstance()->getInvalidatedTypes();
        $cacheTypes = Mage::app()->getCacheInstance()->getTypes();
        $data = array();
        /** @var $type Varien_Object */
        foreach ($cacheTypes as $typeName => $type) {
            $data[] = array(
                $type->getCacheType(),
                isset($invalidated[$type->getId()]) ? 'Invalidated' : ($type->getStatus() ? 'Enabled' : 'Disabled'),
                $typeName,
                $type->getTags(),
                $type->getDescription()
            );
        }

        $systemReport = array();
        $systemReport['Cache Status'] = array(
            'header' => array('Cache', 'Status', 'Type', 'Associated Tags', 'Description'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate Index Status Information
     *
     * @return array
     * @throws Exception
     */
    protected function _generateIndexStatusData()
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Index status can\'t be retrieved.');
        }

        if ((version_compare($this->_magentoVersion, '1.13.0.0', '>=') && $this->_magentoEdition == 'EE')) {
            $newEnterpriseIndexers = true;
            /** @var $indexer Enterprise_Index_Model_Indexer */
            $indexer = Mage::getSingleton('enterprise_index/indexer');
            /** @var $processModel Enterprise_Index_Model_Process */
            $processModel = Mage::getSingleton('enterprise_index/process');
        } else {
            $newEnterpriseIndexers = false;
            /** @var $indexer Mage_Index_Model_Indexer */
            $indexer = Mage::getSingleton('index/indexer');
            /** @var $processModel Mage_Index_Model_Process */
            $processModel = Mage::getSingleton('index/process');
        }
        $processStatuses = $processModel->getStatusesOptions();
        $processModes = $processModel->getModesOptions();
        /** @var $collection  Enterprise_Index_Model_Resource_Process_Collection */
        $collection = $indexer->getProcessesCollection();

        $data = array();
        /** @var $item Enterprise_Index_Model_Process */
        foreach ($collection as $item) {
            try {
                if (!$newEnterpriseIndexers || ($newEnterpriseIndexers && !$item->isEnterpriseProcess())) {
                    $status = $item->isLocked() ? Mage_Index_Model_Process::STATUS_RUNNING : $item->getStatus();
                } else {
                    $status = $item->getStatus();
                }
                $status = isset($processStatuses[$status]) ? $processStatuses[$status] : $status;
                $mode = isset($processModes[$item->getMode()]) ? $processModes[$item->getMode()] : $item->getMode();
                $updateRequired = $item->getUnprocessedEventsCollection()->count() > 0 ? 'Yes' : 'No';
                if ($newEnterpriseIndexers) {
                    $updateRequired = !$item->isEnterpriseProcess()
                        ? $item->getUnprocessedEventsCollection()->count() > 0 ? 'Yes' : 'No'
                        : '';
                }
                if (is_null($mode)) {
                    $mode = '';
                }
                $name = $item->getIndexer()->getName();
                if (empty($name)) {
                    $name = $item->getIndexerCode();
                }

                $data[] = array(
                    $name,
                    $status,
                    $updateRequired,
                    $item->getEndedAt() ? $item->getEndedAt() : 'Never',
                    $mode,
                    $item->getIndexer()->isVisible() ? 'Yes' : 'No',
                    $item->getIndexer()->getDescription()
                );
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        $systemReport = array();
        $systemReport['Index Status'] = array(
            'header' => array('Index', 'Status', 'Update Required', 'Updated At', 'Mode', 'Is Visible', 'Description'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate Compiler Status Information
     *
     * @return array
     */
    protected function _generateCompilerStatusData()
    {
        $systemReport = $data = array();
        /** @var $compiler Mage_Compiler_Model_Process */
        $compiler = Mage::getModel('compiler/process');
        $data[] = array(
            $this->_isCompilerEnabled() ? 'Enabled' : 'Disabled',
            $compiler->getCollectedFilesCount() > 0 ? 'Compiled' : 'Not Compiled',
            $compiler->getCollectedFilesCount(),
            $compiler->getCompiledFilesCount()
        );

        $systemReport['Compiler Status'] = array(
            'header' => array('Status', 'State', 'Files Count', 'Scopes Count'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Determine if compiler configured as Enabled
     *
     * @return array
     */
    protected function _isCompilerEnabled()
    {
        try {
            $compilerConfig = $this->_getRootPath() . 'includes' . DIRECTORY_SEPARATOR . 'config.php';
            if (file_exists($compilerConfig)) {
                include_once $compilerConfig;
            }
        } catch (Exception $e) {
            $this->_log($e);
        }
        return defined('COMPILER_INCLUDE_PATH');
    }

    /**
     * Generate Cron Schedules List
     * Can be filtered by cron job code, status and ID
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCronSchedulesData()
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Cron schedule status can\'t be retrieved.');
        }
        $data = $cronSchedules = array();
        $collection = null;
        try {
            /** @var $collection Mage_Cron_Model_Resource_Schedule_Collection */
            $collection = Mage::getModel('cron/schedule')->getCollection();
            $collection->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_ERROR);
        } catch (Exception $e) {
            $this->_log($e);
        }
        if ($collection) {
            $cronSchedules = $collection->load();
        }
        /** @var $schedule Mage_Cron_Model_Schedule */
        foreach ($cronSchedules as $schedule) {
            try {
                $data[] = array(
                    $schedule->getId(),
                    $schedule->getJobCode(),
                    $schedule->getStatus(),
                    $schedule->getCreatedAt(),
                    $schedule->getScheduledAt(),
                    $schedule->getExecutedAt(),
                    $schedule->getFinishedAt(),
                );
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        $systemReport = array();
        $systemReport['Cron Schedules List'] = array(
            'header' => array(
                'Schedule Id', 'Job Code', 'Status', 'Created At', 'Scheduled At', 'Executed At', 'Finished At'
            ),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate Cron Status Information
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCronStatusData()
    {
        $systemReport = array();
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Cron schedule status can\'t be retrieved.');
        }
        // Cron status by status code
        $data = array();
        try {
            $info = $this->_readConnection->fetchAll("
                SELECT COUNT( * ) AS `cnt` , `status`
                FROM `{$this->_getTableName('cron/schedule')}`
                GROUP BY `status`
                ORDER BY `status`
            ");

            if ($info) {
                foreach ($info as $_data) {
                    $data[] = array($_data['status'], $_data['cnt']);
                }
            }

            $systemReport['Cron Schedules by status code'] = array(
                'header' => array(
                    'Status Code', 'Count'
                ),
                'data' => $data
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        // Cron status by job code
        $data = array();
        try {
            $info = $this->_readConnection->fetchAll("
                SELECT COUNT( * ) AS `cnt` , `job_code`
                FROM `{$this->_getTableName('cron/schedule')}`
                GROUP BY `job_code`
                ORDER BY `job_code`
            ");

            if ($info) {
                foreach ($info as $_data) {
                    $data[] = array($_data['job_code'], $_data['cnt']);
                }
            }

            $systemReport['Cron Schedules by job code'] = array(
                'header' => array(
                    'Job Code', 'Count'
                ),
                'data' => $data
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }
    /**
     * Generate the most frequent error per each cron job code
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCronErrorsData()
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Cron schedule status can\'t be retrieved.');
        }
        $collection = null;
        $cronSchedules = array();
        try {
            /** @var $collection Mage_Cron_Model_Resource_Schedule_Collection */
            $collection = Mage::getModel('cron/schedule')->getCollection();
            $collection->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_ERROR);
        } catch (Exception $e) {
            $this->_log($e);
        }
        if ($collection) {
            $cronSchedules = $collection->load();
        }

        $jobs = $data = array();
        /** @var $schedule Mage_Cron_Model_Schedule */
        foreach ($cronSchedules as $schedule) {
            try {
                $jobData = array(
                    $schedule->getId(),
                    $schedule->getJobCode(),
                    $schedule->getMessages(),
                    1,
                    $schedule->getCreatedAt(),
                    $schedule->getScheduledAt(),
                    $schedule->getExecutedAt(),
                    $schedule->getFinishedAt(),
                );
                // Calculate error message occurrence rate
                if (preg_match('~^exception (.+)$~im', $schedule->getMessages(), $matches)) {
                    $exceptionMessage = $matches[1];
                    if (empty($jobs[$schedule->getJobCode()][$exceptionMessage])) {
                        $jobs[$schedule->getJobCode()][$exceptionMessage]['cnt'] = 1;
                    } else {
                        $jobs[$schedule->getJobCode()][$exceptionMessage]['cnt']++;
                    }
                    $jobs[$schedule->getJobCode()][$exceptionMessage]['data'] = $jobData;
                } else {
                    $data[] = $jobData;
                }
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        foreach ($jobs as $messages) {
            $counts = array();
            foreach ($messages as $messageData) {
                $counts[] = $messageData['cnt'];
            }
            array_multisort($counts, SORT_DESC, $messages);
            $topMessage = current($messages);
            $topMessage['data'][3] = $topMessage['cnt'];
            $data[] = $topMessage['data'];
        }

        $systemReport = array();
        $systemReport['Errors in Cron Schedules Queue'] = array(
            'header' => array(
                'Schedule Id', 'Job Code', 'Error', 'Count', 'Created At', 'Scheduled At', 'Executed At', 'Finished At'
            ),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate all cron jobs information
     *
     * @return array
     */
    protected function _generateAllCronJobsData()
    {
        $systemReport = array();
        $systemReport['All Global Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('global')
        );

        $systemReport['All Configurable Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('configurable')
        );

        return $systemReport;
    }

    /**
     * Generate core cron jobs information
     *
     * @return array
     */
    protected function _generateCoreCronJobsData()
    {
        $systemReport = array();
        $systemReport['Core Global Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('global', 'core')
        );

        $systemReport['Core Configurable Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('configurable', 'core')
        );

        return $systemReport;
    }

    /**
     * Generate enterprise cron jobs information
     *
     * @return array
     */
    protected function _generateEnterpriseCronJobsData()
    {
        $systemReport = array();
        $systemReport['Enterprise Global Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('global', 'enterprise')
        );

        $systemReport['Enterprise Configurable Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('configurable', 'enterprise')
        );

        return $systemReport;
    }

    /**
     * Generate custom cron jobs information
     *
     * @return array
     */
    protected function _generateCustomCronJobsData()
    {
        $systemReport = array();
        $systemReport['Custom Global Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('global', 'custom')
        );

        $systemReport['Custom Configurable Cron Jobs'] = array(
            'header' => array('Job Code', 'Cron Expression', 'Run Class', 'Run Method'),
            'data' => $this->_getCronJobs('configurable', 'custom')
        );

        return $systemReport;
    }

    /**
     * Collect Cron Jobs by specified scope and type
     *
     * @param string $scope
     * @param string $type
     *
     * @return array
     */
    protected function _getCronJobs($scope, $type = 'all')
    {
        $scope = $scope == 'configurable' || $scope == 'global' ? $scope : 'global';
        $type = !in_array($type, array('all', 'core', 'enterprise', 'custom')) ? 'all' : $type;
        $data = $jobsData = array();
        $coreNamespaces = array('Mage', 'Zend');

        $jobs = Mage::getConfig()->getNode(($scope == 'configurable' ? 'default/' : '') . 'crontab/jobs');
        if (!($jobs instanceof Mage_Core_Model_Config_Element)) {
            return $data;
        }

        foreach ($jobs->children() as $jobCode => $jobConfig) {
            $runClass = $runMethod = $cronExpr = 'n/a';
            if ($jobConfig) {
                if ($jobConfig->run && $jobConfig->run->model) {
                    $modelName = (string)$jobConfig->run->model;
                    if (preg_match(Mage_Cron_Model_Observer::REGEX_RUN_MODEL, $modelName, $run)) {
                        $runClass = Mage::app()->getConfig()->getModelClassName($run[1]);
                        $runMethod = $run[2];
                    }
                }

                if ($jobConfig->schedule && $jobConfig->schedule->config_path) {
                    $cronExpr = Mage::getStoreConfig((string)$jobConfig->schedule->config_path);
                }
                if ($cronExpr == 'n/a' && $jobConfig->schedule && $jobConfig->schedule->cron_expr) {
                    $cronExpr = (string)$jobConfig->schedule->cron_expr;
                }
                if (!$cronExpr) {
                    $cronExpr = 'n/a';
                }
            }

            if ($runClass != 'n/a') {
                if ($type != 'all') {
                    $nameSpace = substr($runClass, 0, strpos($runClass, '_'));
                    $_className = str_replace($nameSpace . '_', '', $runClass);
                    $module = $nameSpace . '_' . substr($_className, 0, strpos($_className, '_'));
                }  else {
                    $module = '';
                    $nameSpace = '';
                }
                if (($type == 'core' && !in_array($nameSpace, $coreNamespaces)
                        && !in_array($module, $this->_additionalCoreModules['community']))
                    || ($type == 'custom' && (in_array($nameSpace, $coreNamespaces) || $nameSpace == 'Enterprise'
                            || in_array($module, $this->_additionalCoreModules['community'])))
                    || ($type == 'enterprise' && $nameSpace != 'Enterprise')
                ) {
                    continue;
                }
            }

            $classPath = $this->_getClassPath($runClass, $this->_getModuleCodePoolByClassName($runClass));
            $jobsData[$jobCode] = array($jobCode, $cronExpr, $runClass . "\n" . '{' . $classPath . '}', $runMethod);
        }
        ksort($jobsData);

        foreach ($jobsData as $_data) {
            $data[] = $_data;
        }

        return $data;
    }

    /**
     * Generate category duplicates by URL key
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generateCategoryDuplicates(array $arguments = array())
    {
        $systemReport = array();

        $systemReport['Duplicate Categories By URL Key'] = array(
            'header' => array('ID', 'URL key', 'Name', 'Store'),
            'data' => $this->_getDuplicateUrlKeys('category'),
        );

        return $systemReport;
    }

    /**
     * Generate corrupted categories data
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generateCorruptedCategoriesData(array $arguments = array())
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Corrupted categories data can\'t be retrieved.');
        }
        $connection = $this->_readConnection;
        $data = $systemReport = array();

        try {
            $expected = $connection->fetchAll(
                "SELECT c.entity_id,
                        COUNT(c2.children_count) as `children_count`,
                        (LENGTH(c.path) - LENGTH(REPLACE(c.path,'/',''))) as `level`
                FROM `{$this->_getTableName('catalog/category')}` c
                LEFT JOIN `{$this->_getTableName('catalog/category')}` c2 ON c2.path like CONCAT(c.path,'/%')
                GROUP BY c.path"
            );
            $_expected = $_actual = array();
            foreach ($expected as $row) {
                $_expected[$row['entity_id']] = array(
                    'children_count' => $row['children_count'],
                    'level' => $row['level'],
                );
            }
            $actual = $connection->fetchAll(
                "SELECT `entity_id`, `children_count`, `level`
                FROM `{$this->_getTableName('catalog/category')}`"
            );
            foreach ($actual as $row) {
                $_actual[$row['entity_id']] = array(
                    'children_count' => $row['children_count'],
                    'level' => $row['level'],
                );
            }
            foreach ($_actual as $entityId => $_data) {
                $actualChildrenCount = $_data['children_count'];
                $actualLevel = $_data['level'];
                if (!array_key_exists($entityId, $_expected)) {
                    $data[] = array($entityId, 'n/a', $actualChildrenCount, 'n/a', $actualLevel);
                    continue;
                }
                $expectedChildrenCount = $_expected[$entityId]['children_count'];
                $expectedLevel = $_expected[$entityId]['level'];
                if ($actualChildrenCount == $expectedChildrenCount && $actualLevel == $expectedLevel) {
                    continue;
                }

                $difference = $actualChildrenCount - $expectedChildrenCount;
                if ($difference != 0) {
                    $difference = ' (diff: ' . ($difference > 0 ? '+' : '') . $difference . ')';
                } else {
                    $difference = '';
                }
                $actualChildrenCount .= $difference;

                $difference = $actualLevel - $expectedLevel;
                if ($difference != 0) {
                    $difference = ' (diff: ' . ($difference > 0 ? '+' : '') . $difference . ')';
                } else {
                    $difference = '';
                }
                $actualLevel .= $difference;

                $data[] = array(
                    $entityId,
                    $expectedChildrenCount,
                    $actualChildrenCount,
                    $expectedLevel,
                    $actualLevel
                );
            }

            $systemReport['Corrupted Categories Data'] = array(
                'header' => array(
                    'ID',
                    'Expected Children Count',
                    'Actual Children Count',
                    'Expected Level',
                    'Actual Level',
                ),
                'data' => $data
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Generate product duplicates by sku and URL key
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generateProductDuplicates(array $arguments = array())
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Product duplicates data can\'t be retrieved.');
        }
        $connection = $this->_readConnection;
        $data = $systemReport = array();

        $systemReport['Duplicate Products By URL Key'] = array(
            'header' => array('ID', 'URL key', 'Name', 'Store'),
            'data' => $this->_getDuplicateUrlKeys('product'),
        );

        try {
            $entityTypeCode = Mage_Catalog_Model_Product::ENTITY;
            $entityTypeId = (int)Mage::getSingleton('eav/config')->getEntityType($entityTypeCode)->getId();

            $entityTable  = $this->_getTableName('catalog/product');
            $varCharTable  = $this->_getTableName(array('catalog/product', 'varchar'));

            $nameAttributeId = (int)$connection->fetchOne(
                "SELECT `attribute_id`
                    FROM `{$this->_getTableName('eav/attribute')}`
                    WHERE `attribute_code` = 'name' AND `entity_type_id` = {$entityTypeId}"
            );

            $info = $connection->fetchAll(
                "SELECT COUNT(1) AS `cnt`, `sku`
                FROM `{$entityTable}`
                GROUP BY `sku` HAVING `cnt` > 1 ORDER BY `cnt` DESC, `entity_id`"
            );
            foreach ($info as $row) {
                $entities = $connection->fetchAll(
                    "SELECT `e`.`entity_id`, `n`.`value` as `name`, `e`.`sku`
                        FROM `{$entityTable}` e
                        LEFT JOIN `{$varCharTable}` n
                            ON `e`.`entity_id` = `n`.`entity_id` AND `n`.attribute_id = {$nameAttributeId}
                        WHERE " . $connection->quoteInto('`e`.`sku` = ?', $row['sku'])
                );
                foreach ($entities as $entity) {
                    $data[] = array(
                        $entity['entity_id'],
                        $row['sku'],
                        $entity['name'],
                    );
                }
            }

            $systemReport['Duplicate Products By SKU'] = array(
                'header' => array('ID', 'SKU', 'Name',),
                'data' => $data,
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Generate order duplicates by Increment ID
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generateOrderDuplicates(array $arguments = array())
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Order duplicates data can\'t be retrieved.');
        }
        $connection = $this->_readConnection;
        $data = $systemReport = array();

        try {
            $entityTable  = $this->_getTableName('sales/order');

            $info = $connection->fetchAll(
                "SELECT COUNT(1) AS `cnt`, `increment_id`
                FROM `{$entityTable}`
                GROUP BY `increment_id` HAVING `cnt` > 1 ORDER BY `cnt` DESC, `entity_id`"
            );
            foreach ($info as $row) {
                $entities = $connection->fetchAll(
                    "SELECT `e`.`entity_id`, `e`.`store_id` , `e`.`customer_id`, `e`.`increment_id`, `e`.`created_at`,
                            `s`.`name` as `store_name`
                    FROM `{$entityTable}` e
                    LEFT JOIN `{$this->_getTableName('core/store')}` s USING(store_id)
                    WHERE " . $connection->quoteInto('`e`.`increment_id` = ?', $row['increment_id'])
                );

                foreach ($entities as $entity) {
                    $data[] = array(
                        $entity['entity_id'],
                        $row['increment_id'],
                        $entity['store_name'] . ' {ID:' . $entity['store_id'] . '}',
                        $entity['created_at'],
                        $entity['customer_id'],
                    );
                }
            }

            $systemReport['Duplicate Orders By Increment ID'] = array(
                'header' => array('ID', 'Increment ID', 'Store', 'Created At', 'Customer ID'),
                'data' => $data,
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Generate user duplicates by email
     *
     * @param array $arguments
     *
     * @throws Exception
     * @return array
     */
    protected function _generateUserDuplicates(array $arguments = array())
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. User duplicates data can\'t be retrieved.');
        }
        $connection = $this->_readConnection;
        $data = $systemReport = array();

        try {
            $entityTable  = $this->_getTableName('customer/entity');

            $info = $connection->fetchAll(
                "SELECT COUNT(1) AS `cnt`, `email`
                FROM `{$entityTable}`
                GROUP BY `email` HAVING `cnt` > 1 ORDER BY `cnt` DESC, `entity_id`"
            );
            foreach ($info as $row) {
                $entities = $connection->fetchAll(
                    "SELECT `e`.`entity_id`, `e`.`email`, `e`.`website_id`, `e`.`created_at`,
                            `w`.`name` as `website_name`
                    FROM `{$entityTable}` e
                    LEFT JOIN `{$this->_getTableName('core/website')}` w USING(website_id)
                    WHERE " . $connection->quoteInto('`e`.`email` = ?', $row['email'])
                );

                foreach ($entities as $entity) {
                    $data[] = array(
                        $entity['entity_id'],
                        $row['email'],
                        $entity['website_name'] . ' {ID:' . $entity['website_id'] . '}',
                        $entity['created_at'],
                    );
                }
            }

            $systemReport['Duplicate Users By Email'] = array(
                'header' => array('ID', 'Email', 'Website', 'Created At'),
                'data' => $data,
            );
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Collect duplicate URL keys for specified entity type
     *
     * @param string $entityType
     *
     * @return array
     *
     * @throws Exception
     */
    protected function _getDuplicateUrlKeys($entityType)
    {
        if (!$this->_readConnection) {
            throw new Exception('Cant\'t connect to DB. Duplicate URL keys data can\'t be retrieved.');
        }
        $connection = $this->_readConnection;
        $data = array();

        switch ($entityType) {
            case 'product':
                $entityTypeCode = Mage_Catalog_Model_Product::ENTITY;
                $table = 'catalog/product';
                break;
            case 'category':
                $entityTypeCode = Mage_Catalog_Model_Category::ENTITY;
                $table = 'catalog/category';
                break;
            default:
                throw new Exception('Unsupported entity type: "' . (string)$entityType . '"');
        }

        try {
            $entityTypeId = (int)Mage::getSingleton('eav/config')->getEntityType($entityTypeCode)->getId();
            $nameAttributeId = (int)$connection->fetchOne(
                "SELECT `attribute_id`
                FROM `{$this->_getTableName('eav/attribute')}`
                WHERE `attribute_code` = 'name' AND `entity_type_id` = {$entityTypeId}"
            );
            $urlKeyAttributeId = (int)$connection->fetchOne(
                "SELECT `attribute_id`
                FROM `{$this->_getTableName('eav/attribute')}`
                WHERE `attribute_code` = 'url_key' AND `entity_type_id` = {$entityTypeId}"
            );

            $urlKeyTable = $varCharTable = $this->_getTableName(array($table, 'varchar'));
            if ((version_compare($this->_magentoVersion, '1.13.0.0', '>=') && $this->_magentoEdition == 'EE')) {
                $urlKeyTable  = $this->_getTableName(array($table, 'url_key'));
            }

            $info = $connection->fetchAll(
                "SELECT COUNT(1) AS `cnt`, `value`
                FROM `{$urlKeyTable}`
                WHERE `attribute_id` = {$urlKeyAttributeId}
                GROUP BY `value` HAVING `cnt` > 1 ORDER BY `cnt` DESC, `entity_id`"
            );
            foreach ($info as $row) {
                $entities = $connection->fetchAll(
                    "SELECT `u`.`entity_id`, `n`.`value` as `name`, `u`.`store_id`, `s`.`name` as `store_name`
                    FROM `{$urlKeyTable}` u
                    LEFT JOIN `{$this->_getTableName('core/store')}` s ON `u`.`store_id` = `s`.`store_id`
                    LEFT JOIN `{$varCharTable}` n
                        ON `u`.`entity_id` = `n`.`entity_id` AND
                           `u`.`store_id` = `n`.`store_id` AND
                           `n`.attribute_id = {$nameAttributeId}
                    WHERE `u`.`attribute_id` = {$urlKeyAttributeId}
                        AND " . $connection->quoteInto('`u`.`value` = ?', $row['value'])
                );
                foreach ($entities as $entity) {
                    $data[] = array(
                        $entity['entity_id'],
                        $row['value'],
                        $entity['name'],
                        $entity['store_name'] . ' {ID:' . $entity['store_id'] . '}'
                    );
                }
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $data;
    }

    /**
     * Generate websites tree
     * Collect detailed and most useful information about all websites, stores and store views
     *
     * @return array
     */
    protected function _generateWebsitesTreeData()
    {
        $data = array();

        try {
            $websites = Mage::app()->getWebsites();
            $categories = $this->_getRootCategories();

            /** @var Mage_Core_Model_Website $website */
            foreach ($websites as $websiteId => $website) {
                $name = $website->getName() . ($website->getIsDefault()  ? ' [*] ' : '');
                $data[] = array(
                    $websiteId,
                    $name,
                    $website->getCode(),
                    'website',
                    ''
                );
                $defaultStoreId = $website->getDefaultGroupId();
                $stores = $website->getGroups();
                /** @var Mage_Core_Model_Store_Group $store */
                foreach ($stores as $storeId => $store) {
                    $name = '    ' . $store->getName() . ($defaultStoreId == $storeId  ? ' [*]' : '');
                    $data[] = array(
                        $storeId,
                        $name,
                        '',
                        'store',
                        isset($categories[$store->getRootCategoryId()])
                            ? $categories[$store->getRootCategoryId()]
                            : 'n/a'
                    );
                    $defaultStoreViewId = $store->getDefaultStoreId();
                    $storeViews = $store->getStores();
                    /** @var Mage_Core_Model_Store $storeView */
                    foreach ($storeViews as $storeViewId => $storeView) {
                        $name = '        '
                            . (!$storeView->getIsActive() ? '-disabled- ' : '')
                            . $storeView->getName()
                            . ($defaultStoreViewId == $storeViewId  ? ' [*]' : '');
                        $data[] = array(
                            $storeId,
                            $name,
                            $storeView->getCode(),
                            'store view',
                            ''
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Websites Tree'] = array(
            'header' => array('ID', 'Name', 'Code', 'Type', 'Root Category'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate websites list with default store and default store view
     *
     * @return array
     */
    protected function _generateWebsitesData()
    {
        $data = array();

        try {
            $websites = Mage::app()->getWebsites();
            /** @var Mage_Core_Model_Website $website */
            foreach ($websites as $id => $website) {
                $defaultStore = $website->getDefaultGroup();
                $defaultStoreView = $website->getDefaultStore();
                $data[] = array(
                    $id,
                    $website->getName(),
                    $website->getCode(),
                    $website->getIsDefault() ? 'Yes' : 'No',
                    $defaultStore
                        ? $defaultStore->getName() . ' {ID:' . $defaultStore->getId() . '}'
                        : 'n/a',
                    $defaultStoreView
                        ? $defaultStoreView->getName() . ' {ID:' . $defaultStoreView->getId() . '}'
                        : 'n/a'
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Websites List'] = array(
            'header' => array('ID', 'Name', 'Code', 'Is Default', 'Default Store', 'Default Store View'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate stores list with root category and default store view
     *
     * @return array
     */
    protected function _generateStoresData()
    {
        $data = array();

        try {
            $stores = Mage::app()->getGroups();
            $categories = $this->_getRootCategories();

            /** @var Mage_Core_Model_Store_Group $store */
            foreach ($stores as $id => $store) {
                $defaultStoreView = $store->getDefaultStore();
                $data[] = array(
                    $id,
                    $store->getName(),
                    (
                    isset($categories[$store->getRootCategoryId()]) ? $categories[$store->getRootCategoryId()] : 'n/a'
                    )
                    . ' {ID:' . $store->getRootCategoryId() . '}',
                    $defaultStoreView
                        ? $defaultStoreView->getName() . ' {ID:' . $defaultStoreView->getId() . '}'
                        : 'n/a'
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Stores List'] = array(
            'header' => array('ID', 'Name', 'Root Category', 'Default Store View'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Collect store root categories
     *
     * @return array
     */
    protected function _getRootCategories()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $categoryCollection->addAttributeToSelect('name')
            ->addFieldToFilter('path', array('neq' => '1'))
            ->addFieldToFilter('level', array('lteq' => '1'))
            ->load();

        $categories = array();
        foreach ($categoryCollection as $category) {
            $categories[$category->getId()] = $category->getName();
        }

        return $categories;
    }

    /**
     * Generate store views list with sore
     *
     * @return array
     */
    protected function _generateStoreViewsData()
    {
        $data = array();

        try {
            $storeViews = Mage::app()->getStores();
            /** @var Mage_Core_Model_Store $storeView */
            foreach ($storeViews as $id => $storeView) {
                $defaultStore = $storeView->getGroup();
                $data[] = array(
                    $id,
                    $storeView->getName(),
                    $storeView->getCode(),
                    $storeView->getIsActive() ? 'Yes' : 'No',
                    $defaultStore
                        ? $defaultStore->getName() . ' {ID:' . $defaultStore->getId() . '}'
                        : 'n/a'
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Store Views List'] = array(
            'header' => array('ID', 'Name', 'Code', 'Enabled', 'Store'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate design themes config data
     *
     * @return array
     */
    protected function _generateDesignThemeConfigData()
    {
        $configPaths = array(
            // text, STORE VIEW
            array('path' => 'design/package/name', 'name' => 'Current Package Name'),
            // text, STORE VIEW
            array('path' => 'design/theme/default', 'name' => 'Default Theme'),
            // text, STORE VIEW
            array('path' => 'design/theme/locale', 'name' => 'Translations Theme'),
            // text, STORE VIEW
            array('path' => 'design/theme/layout', 'name' => 'Layouts Theme'),
            // text, STORE VIEW
            array('path' => 'design/theme/template', 'name' => 'Templates Theme'),
            // text, STORE VIEW
            array('path' => 'design/theme/skin', 'name' => 'Skin (Images / CSS)'),
        );
        $data = array();
        try {
            $data = array();
            $configData = $this->_getConfigValues($configPaths);
            foreach ($configData as $info) {
                $data[] = array(
                    $info['name'],
                    $info['value'],
                    $info['scope']
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        $systemReport = array();
        $systemReport['Design Themes Config'] = array(
            'header' => array('Name', 'Value', 'Scope'),
            'data' => $data
        );

        return $systemReport;
    }

    /**
     * Generate design themes list
     *
     * @return array
     */
    protected function _generateDesignThemeListData()
    {
        $systemReport = array();
        try {
            $reports = $this->_getDesignList();
            foreach ($reports as $reportName => $data) {
                $reportName = ucwords($reportName);
                $systemReport[$reportName . ' Themes List'] = array(
                    'header' => array('Name', 'Type'),
                    'data' => $data
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Generate skins list
     *
     * @return array
     */
    protected function _generateDesignSkinsListData()
    {
        $systemReport = array();
        try {
            $reports = $this->_getDesignList('skin');
            foreach ($reports as $reportName => $data) {
                if (empty($data)) {
                    continue;
                }
                $reportName = ucwords($reportName);
                $systemReport[$reportName . ' Skins List'] = array(
                    'header' => array('Name', 'Type'),
                    'data' => $data
                );
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $systemReport;
    }

    /**
     * Collect themes or skins list with default mark for frontend area
     *
     * @param string $type
     *
     * @return array
     */
    protected function _getDesignList($type = 'design')
    {
        $type = $type == 'design' || $type == 'skin' ? $type : 'design';
        $configInfo = array('path' => 'design/package/name');
        $defaultPackage = Mage::getStoreConfig($configInfo['path'], Mage_Core_Model_App::DISTRO_STORE_ID);
        $defaultPackage = $this->_prepareConfigValue($configInfo, $defaultPackage);

        if ($type == 'design') {
            $configInfo = array('path' => 'design/theme/default');
        } else {
            $configInfo = array('path' => 'design/theme/skin');
        }
        $defaultDesign = Mage::getStoreConfig($configInfo['path'], Mage_Core_Model_App::DISTRO_STORE_ID);
        $defaultDesign = $this->_prepareConfigValue($configInfo, $defaultDesign);

        $designDirectory = Mage::getBaseDir($type) . DIRECTORY_SEPARATOR;
        $entries = $this->_getFilesList($designDirectory, 2, self::REPORT_FILE_LIST_DIRS);
        $results = array();
        foreach ($entries as $entry) {
            $entry = substr($entry, strlen($designDirectory));
            if (preg_match('~\..+~', $entry)) {
                continue;
            }
            $parts = explode(DIRECTORY_SEPARATOR, $entry);
            $partsSize = sizeof($parts);
            if ($partsSize == 1) {
                $results[$parts[0]] = array();
            } else if ($partsSize == 2) {
                $name = $parts[1];
                if ($parts[0] == 'frontend') {
                    $name = $defaultPackage == $name ? $name . ' [*]' : $name;
                }
                $results[$parts[0]][] = array($name, 'package');
            } else if ($partsSize == 3) {
                $name = $parts[2];
                if ($parts[0] == 'frontend' && $defaultPackage == $parts[1]) {
                    $name = $defaultDesign == $name ? $name . ' [*]' : $name;
                }
                $results[$parts[0]][] = array(
                    '    ' . $name,
                    $type == 'design' ? 'theme' : 'skin'
                );
            }
        }
        ksort($results);

        return $results;
    }

    /**
     * Generate all entity types list
     *
     * @param array $arguments
     *
     * @return array
     * @throws Exception
     */
    protected function _generateAllEntityTypesData(array $arguments = array())
    {
        $data = array();
        $types = Mage::getModel('eav/entity_type')
            ->getResourceCollection()
            ->load();
        $types = !$types ? array() : $types;
        /** @var $type Mage_Eav_Model_Entity_Type */
        foreach ($types as $type) {
            $entityTable = $type->getEntityTable();
            try {
                $entityTable = $this->_getTableName($entityTable);
            } catch (Exception $e) {
                //
            }

            $additionalAttrTable = $type->getAdditionalAttributeTable();
            try {
                $additionalAttrTable = $this->_getTableName($additionalAttrTable);
            } catch (Exception $e) {
                //
            }

            try {
                $data[] = array(
                    $type->getId(),
                    $type->getEntityTypeCode(),
                    $this->_generateModelClassValueByModelFactoryName($type->getEntityModel()),
                    $this->_generateModelClassValueByModelFactoryName($type->getAttributeModel()),
                    $this->_generateModelClassValueByModelFactoryName($type->getIncrementModel()),
                    $entityTable,
                    $additionalAttrTable,
                );
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        $systemReport = array();
        $systemReport['Entity Types'] = array(
            'header' => array(
                'ID', 'Code', 'Model', 'Attribute Model', 'Increment Model', 'Main Table', 'Additional Attribute Table',
            ),
            'data'   => $data
        );

        return $systemReport;
    }

    /**
     * Generate all EAV attributes list
     *
     * @param array $arguments
     *
     * @return array
     * @throws Exception
     */
    protected function _generateAllEavAttributesData(array $arguments = array())
    {
        $data = $attributes = $systemReport = array();
        $attributes = $this->_getEavAttributes('all');
        foreach ($attributes as $attrId => $attrData) {
            $data[] = array(
                $attrId,
                $attrData['code'] . "\n" .
                '{frontend: ' . $attrData['frontend_type'] . ', backend: ' . $attrData['backend_type'] . '}',
                $attrData['is_user_defined'] ? 'Yes' : 'No',
                $attrData['entity_type_code'] ? $attrData['entity_type_code'] : 'n/a',
                $this->_generateModelClassValueByModelFactoryName($attrData['source_model']),
                $this->_generateModelClassValueByModelFactoryName($attrData['backend_model']),
                $this->_generateModelClassValueByModelFactoryName($attrData['frontend_model']),
            );
        }

        $systemReport['All Eav Attributes'] = array(
            'header' => array(
                'ID', 'Code', 'User Defined', 'Entity Type Code', 'Source Model', 'Backend Model', 'Frontend Model'
            ),
            'data'   => $data
        );

        return $systemReport;
    }

    /**
     * Generate new EAV attributes list
     *
     * @return array
     * @throws Exception
     */
    protected function _generateNewEavAttributesData()
    {
        $systemReport   = $data = array();

        $systemReport['New Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('new')
        );

        return $systemReport;
    }

    /**
     * Generate user defined EAV attributes list
     *
     * @return array
     */
    protected function _generateUserDefinedEavAttributesData()
    {
        $systemReport = array();

        $systemReport['User Defined Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'Entity Type Code', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('user_defined')
        );

        return $systemReport;
    }

    /**
     * Generate category EAV attributes list
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCategoryEavAttributesData()
    {
        $systemReport = array();
        $systemReport['Category Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('category'),
        );

        return $systemReport;
    }

    /**
     * Generate product EAV attributes list
     *
     * @return array
     */
    protected function _generateProductEavAttributesData()
    {
        $systemReport = array();
        $systemReport['Product Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('product'),
        );

        return $systemReport;
    }

    /**
     * Generate customer EAV attributes list
     *
     * @return array
     * @throws Exception
     */
    protected function _generateCustomerEavAttributesData()
    {
        $systemReport = array();
        $systemReport['Customer Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('customer'),
        );

        return $systemReport;
    }

    /**
     * Generate customer address EAV attributes list
     *
     * @return array
     */
    protected function _generateCustomerAddressEavAttributesData()
    {
        $systemReport = array();
        $systemReport['Customer Address Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('customer_address'),
        );

        return $systemReport;
    }

    /**
     * Generate customer EAV attributes list
     *
     * @return array
     */
    protected function _generateRmaItemEavAttributesData()
    {
        $systemReport = array();
        if ($this->_magentoEdition != 'EE'
            || ($this->_magentoEdition == 'EE' && version_compare($this->_magentoVersion, '1.11.0.0', '<'))
        ) {
            return $systemReport;
        }

        $systemReport['Rma Item Eav Attributes'] = array(
            'header' => array('ID', 'Code', 'User Defined', 'Source Model', 'Backend Model', 'Frontend Model'),
            'data'   => $this->_prepareAttributesListAsReportData('rma_item'),
        );

        return $systemReport;
    }

    /**
     * Decorate attributes list data and prepare as report format
     *
     * @param string $type
     * @return array
     */
    protected function _prepareAttributesListAsReportData($type)
    {
        $data = $attributes = array();
        $attributes = $this->_getEavAttributes($type);
        foreach ($attributes as $attrId => $attrData) {
            $_data = array(
                $attrId,
                $attrData['code'] . "\n" .
                '{frontend: ' . $attrData['frontend_type'] . ', backend: ' . $attrData['backend_type'] . '}',
            );
            if ($type == 'user_defined') {
                $_data[] = $attrData['entity_type_code'] ? $attrData['entity_type_code'] : 'n/a';
            } else {
                $_data[] = $attrData['is_user_defined'] ? 'Yes' : 'No';
            }
            $_data[] = $this->_generateModelClassValueByModelFactoryName($attrData['source_model']);
            $_data[] = $this->_generateModelClassValueByModelFactoryName($attrData['backend_model']);
            $_data[] = $this->_generateModelClassValueByModelFactoryName($attrData['frontend_model']);
            $data[]  = $_data;
        }

        return $data;
    }

    /**
     * Get eav attributes list by specified attributes type (group)
     * Available types: all, new, user_defined, rma_item, category, product, customer, customer_address
     *
     * Return format:
     * Array (
     *     attr_id => Array (
     *          'id' => attr_id,
     *          'code' => attr_code,
     *          'entity_type_code' => entity_type_code,
     *          'is_user_defined' => is_user_defined,
     *          'frontend_type' => frontend_type,
     *          'backend_type' => backend_type,
     *          'source_model' => source_model,
     *          'backend_model' => backend_model,
     *          'frontend_model' => frontend_model
     *      )
     * )
     *
     * @param string $type
     *
     * @return array
     */
    protected function _getEavAttributes($type = 'all')
    {
        $data = array();
        if ($type == 'rma_item' && ($this->_magentoEdition != 'EE'
                || $this->_magentoEdition == 'EE' && version_compare($this->_magentoVersion, '1.11.0.0', '<'))
        ) {
            return $data;
        }

        if ($type == 'new') {
            $structure = $this->_getDbStructureData('eav_attributes', isset($arguments['f']));

            return array_diff_key($structure['local_data'], $structure['reference_data']);
        }

        /** @var Mage_Eav_Model_Resource_Attribute_Collection $attributes */
        $attributes = Mage::getModel('eav/entity_attribute')
            ->getResourceCollection()
            ->setOrder('attribute_code', Varien_Data_Collection::SORT_ORDER_ASC);

        switch ($type) {
            case 'user_defined':
                $attributes->addFieldToFilter('is_user_defined', 1);
                break;
            case 'category':
            case 'product':
            case 'rma_item':
            case 'customer':
            case 'customer_address':
                $entityCode = $type;
                if ($type == 'product') {
                    $entityCode = Mage_Catalog_Model_Product::ENTITY;
                }
                if ($type == 'category') {
                    $entityCode = Mage_Catalog_Model_Category::ENTITY;
                }
                if ($type == 'rma_item') {
                    $entityCode = Enterprise_Rma_Model_Item::ENTITY;
                }
                /** @var null|Mage_Eav_Model_Entity_Type $entityType */
                $entityType = null;
                try {
                    $entityType = Mage::getSingleton('eav/config')->getEntityType($entityCode);
                } catch (Exception $e) {
                    //
                }
                if ($entityType) {
                    $attributes->addFieldToFilter('entity_type_id', (int)$entityType->getId());
                }
                break;
            case 'all':
            default:
                break;
        }

        $attributes->load();
        $attributes = !$attributes ? array() : $attributes;

        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        foreach ($attributes as $attribute) {
            /** @var null|Mage_Eav_Model_Entity_Type $entityType */
            $entityType = null;
            try {
                $entityType = $attribute->getEntityType();
            } catch (Exception $e) {
                //
            }

            try {
                $data[$attribute->getId()] = array(
                    'id'                => $attribute->getId(),
                    'code'              => $attribute->getAttributeCode(),
                    'entity_type_code'  => $entityType ? $entityType->getEntityTypeCode() : null,
                    'is_user_defined'   => (bool)$attribute->getIsUserDefined(),
                    'frontend_type'     => $attribute->getFrontendInput(),
                    'backend_type'      => $attribute->getBackendType(),
                    'source_model'      => $attribute->getSourceModel(),
                    'backend_model'     => $attribute->getBackendModel(),
                    'frontend_model'    => $attribute->getFrontendModel(),
                );
            } catch (Exception $e) {
                $this->_log($e);
            }
        }

        return $data;
    }

    /**
     * Generate class name and class file path by specified factory model name
     *
     * @param string $model
     * @return string
     */
    protected function _generateModelClassValueByModelFactoryName($model)
    {
        $model = (string)$model;
        if (empty($model)) {
            return '';
        }
        $className = Mage::getConfig()->getModelClassName($model);
        $classPath = $this->_getClassPath(
            $className,
            $this->_getModuleCodePoolByClassName($className)
        );

        return $className . "\n" . '{' . $classPath . '}';
    }

    /**
     * Generate relative path to class file by its name and code pool
     *
     * @param string $className
     * @param string $codePool
     *
     * @return string
     */
    protected function _getClassPath($className, $codePool)
    {
        if (empty($className) || $className == 'n/a') {
            return '';
        }
        return 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . $codePool . DIRECTORY_SEPARATOR
        . implode(DIRECTORY_SEPARATOR, explode('_', $className)) . '.php';
    }

    /**
     * Get module code pool by specified class name
     *
     * @param string $className
     *
     * @return string
     */
    protected function _getModuleCodePoolByClassName($className)
    {
        $moduleConfig = $this->_getModuleConfigByClassName($className);
        if (!empty($moduleConfig)) {
            return $moduleConfig['code_pool'];
        }

        return 'n/a';
    }

    /**
     * Get module code pool by specified class name
     *
     * @param string $className
     *
     * @return bool
     */
    protected function _isModuleActiveByClassName($className)
    {
        $moduleConfig = $this->_getModuleConfigByClassName($className);
        if (!empty($moduleConfig)) {
            return (bool)$moduleConfig['is_active'];
        }

        return false;
    }

    /**
     * Get module config (active, codePool, version) by specified class name
     *
     * @param string $className
     *
     * @return string
     */
    protected function _getModuleConfigByClassName($className)
    {
        static $config = array();
        $result = array();
        $_classParts = explode('_', $className);
        if (is_array($_classParts) && isset($_classParts[0]) && isset($_classParts[1])) {
            $module = $_classParts[0] . '_' . $_classParts[1];
            if (array_key_exists($module, $config)) {
                $result = $config[$module];
            } else {
                $moduleConfig = Mage::app()->getConfig()->getNode('modules')->$module;
                if ($moduleConfig) {
                    $config[$module] = array(
                        'is_active' => strtolower((string) $moduleConfig->active) == 'true',
                        'code_pool' => (string) $moduleConfig->codePool,
                        'version'   => (string) $moduleConfig->version,
                    );
                    $result = $config[$module];
                }
            }
        }

        return $result;
    }

    /**
     * Prepare, filter, validate and set input commands to run
     *
     * @param array $commandsList
     *
     * @return array
     */
    protected function _setInputCommands($commandsList)
    {
        if (!is_array($commandsList)) {
            $commandsList = array_map('trim', explode(',', $commandsList));
        }
        $this->_inputCommands = array_intersect(array_keys($this->_supportedCommands), $commandsList);
        $this->_inputCommands = array_unique($this->_inputCommands);
        if (sizeof($this->_inputCommands) == 0) {
            Mage::throwException(Mage::helper('enterprise_support')->__('Input command to run list is empty'));
        }

        return $this->_inputCommands;
    }

    /**
     * Retrieve DB table name
     *
     * @param string $factoryName
     *
     * @return string
     */
    protected function _getTableName($factoryName)
    {
        return $this->_resourceModel->getTable($factoryName);
    }

    /**
     * Get Magento Root path (with directory separator in the end)
     *
     * @return string
     */
    protected function _getRootPath()
    {
        if (is_null($this->_rootPath)) {
            $this->_rootPath = Mage::getBaseDir();
            if (substr($this->_rootPath, -1, 1) != DS) {
                $this->_rootPath .= DS;
            }
        }
        return $this->_rootPath;
    }

    /**
     * Log exception or regular message into self::REPORT_LOG_FILE file or/and output it into STDOUT
     *
     * @param null|Exception $exception
     * @param null|mixed $message
     */
    protected function _log($exception = null, $message = null)
    {
        if ($this->_debug) {
            if ($exception instanceof Exception) {
                Mage::log($exception->__toString(), Zend_Log::ERR, self::REPORT_LOG_FILE, true);
            } else {
                Mage::log($message, null, self::REPORT_LOG_FILE, true);
            }
        }
    }

    /**
     * Collect files list in specified directory recursively according to specified nesting level
     *
     * @param string $directory
     * @param int $nestLevel
     * @param int $listMode
     * @param array $openOnlyDirs array of directory paths to browse in only
     * @param string|null $fileMask REGEXP
     * @param bool $resetStaticData
     *
     * @return array
     */
    protected function _getFilesList($directory, $nestLevel = 1, $listMode = self::REPORT_FILE_LIST_FILES,
                                     $openOnlyDirs = array(), $fileMask = null, $resetStaticData = true
    ) {
        if (substr($directory, -1, 1) != DS) {
            $directory .= DS;
        }
        $directoryHandler = opendir($directory);
        $data = array();
        static $currentLevel = 0;
        if ($resetStaticData) {
            $currentLevel = 0;
        }

        if ($directoryHandler) {
            while (($entry = readdir($directoryHandler)) !== false) {
                $file = $directory . $entry;
                if (
                    ($listMode == self::REPORT_FILE_LIST_ALL
                        || ($listMode == self::REPORT_FILE_LIST_FILES && is_file($file))
                        || ($listMode == self::REPORT_FILE_LIST_DIRS && is_dir($file))
                    )
                    && (empty($fileMask) || preg_match('~' . $fileMask . '~', $entry))
                    && $entry != '.' && $entry != '..'
                ) {
                    $data[] = $file;
                }

                if ($entry != '.' && $entry != '..' && is_dir($file)) {
                    if ((!empty($openOnlyDirs) && is_array($openOnlyDirs) && in_array($file, $openOnlyDirs))
                        || empty($openOnlyDirs)
                    ) {
                        if ($currentLevel < $nestLevel) {
                            $currentLevel++;
                            $data = array_merge(
                                $data,
                                $this->_getFilesList(
                                    $file, $nestLevel, $listMode, $openOnlyDirs, $fileMask, false
                                )
                            );
                        }
                    }
                }
            }
            $currentLevel--;
            closedir($directoryHandler);
        }

        sort($data);
        return $data;
    }

    /**
     * Determine size of specified file
     * Applicable for all files, also those files what have size > 4 GB at Windows
     *
     * @param $file
     * @link http://www.php.net/manual/en/function.filesize.php#104101
     *
     * @return float
     */
    protected function _getFileSize($file)
    {
        if (class_exists('COM', false) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $filesystem = new COM('Scripting.FileSystemObject');
                $file = $filesystem->GetFile(realpath($file));
                $size = $file->Size();
                if (!ctype_digit($size)) {
                    return null;
                }
            } catch (Exception $e) {
                $size = null;
            }
        } else {
            $size = filesize($file);
        }
        if ($size < 0 || $size === false) {
            return null;
        }

        return $size;
    }

    /**
     * Format specified bits|bytes to human readable string
     *
     * @param int $val
     * @param int $digits how match digits must be used when round result
     * @param string $mode SI'|'IEC': if SI, then division factor will be 1000, other way - 1024
     * @param string $bB 'b'|'B': if b, then result will be in bits, other way in bytes
     *
     * @return string
     */
    protected function _formatBytes($val, $digits = 3, $mode = 'SI', $bB = 'B')
    {
        $iec = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
        $si = array('', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi');
        $nums = 9;
        $mode = strtoupper((string)$mode);
        $mode = $mode != 'SI' && $mode != 'IEC' ? 'SI' : $mode;
        if ($mode == 'SI') {
            $factor  = 1000;
            $symbols = $si;
        }
        else {
            $factor  = 1024;
            $symbols = $iec;
        }
        if ($bB == 'b'){
            $val *= 8;
        }
        else {
            $bB = 'B';
        }
        for ($i=0; $i < $nums - 1 && $val >= $factor; $i++) {
            $val /= $factor;
        }
        $p = strpos($val, '.');
        if ($p !== false && $p > $digits) {
            $val = round($val);
        } else if($p !== false) {
            $val = round($val, $digits - $p);
        }

        return round($val, $digits) . ' ' . $symbols[$i] . $bB;
    }

    /**
     * Retrieve directory size recursively
     *
     * @param string $directory
     *
     * @throws Exception
     *
     * @return int
     */
    protected function _getDirSize($directory)
    {
        $size = 0;
        try {
            /** @var $iterator RecursiveIteratorIterator */
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            /** @var $file SplFileInfo */
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        } catch (Exception $e) {
            $this->_log($e);
        }

        return $size;
    }

    /**
     * Determine if given file is link. It is "Windows OS" compatible.
     *
     * @param string $filename
     *
     * @return bool
     */
    protected function _isLink($filename)
    {
        if (is_link($filename)) {
            return true;
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (strtolower($ext) == 'lnk') {
            return ($this->_readlink($filename) ? true : false);
        }

        return false;
    }

    /**
     * Read link. It is "Windows OS" compatible.
     *
     * @param string $filename
     *
     * @return array|string
     */
    protected function _readlink($filename)
    {
        if (file_exists($filename)) {
            if (is_link($filename)) {
                return readlink($filename);
            }
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                return false;
            }
            if (!is_readable($filename)) {
                return false;
            }
            // Get file content
            $handle = fopen($filename, "rb");
            $buffer = array();
            while (!feof($handle)) {
                $buffer[] = fread($handle, 1);
            }
            fclose($handle);

            // Test magic value and GUID
            if (count($buffer) < 20) {
                return false;
            }
            if ($buffer[0] != 'L') {
                return false;
            }

            if ((ord($buffer[4]) != 0x01) ||
                (ord($buffer[5]) != 0x14) ||
                (ord($buffer[6]) != 0x02) ||
                (ord($buffer[7]) != 0x00) ||
                (ord($buffer[8]) != 0x00) ||
                (ord($buffer[9]) != 0x00) ||
                (ord($buffer[10]) != 0x00) ||
                (ord($buffer[11]) != 0x00) ||
                (ord($buffer[12]) != 0xC0) ||
                (ord($buffer[13]) != 0x00) ||
                (ord($buffer[14]) != 0x00) ||
                (ord($buffer[15]) != 0x00) ||
                (ord($buffer[16]) != 0x00) ||
                (ord($buffer[17]) != 0x00) ||
                (ord($buffer[18]) != 0x00) ||
                (ord($buffer[19]) != 0x46)
            ) {
                return false;
            }

            $i = 20;
            if (count($buffer) < ($i + 4)) {
                return false;
            }

            $flags = ord($buffer[$i]);
            $flags = $flags | (ord($buffer[++$i]) << 8);
            $flags = $flags | (ord($buffer[++$i]) << 16);
            $flags = $flags | (ord($buffer[++$i]) << 24);

            $hasShellItemIdList = ($flags & 0x00000001) ? true : false;
            $pointsToFileOrDir  = ($flags & 0x00000002) ? true : false;

            if (!$pointsToFileOrDir) {
                return false;
            }

            $a = 0;
            if ($hasShellItemIdList) {
                $i = 76;
                if (count($buffer) < ($i + 2)) {
                    return false;
                }
                $a = ord($buffer[$i]);
                $a = $a | (ord($buffer[++$i]) << 8);

            }

            $i = 78 + 4 + $a;
            if (count($buffer) < ($i + 4)) {
                return false;
            }

            $b = ord($buffer[$i]);
            $b = $b | (ord($buffer[++$i]) << 8);
            $b = $b | (ord($buffer[++$i]) << 16);
            $b = $b | (ord($buffer[++$i]) << 24);

            $i = 78 + $a + $b;
            if (count($buffer) < ($i + 4)) {
                return false;
            }

            $c = ord($buffer[$i]);
            $c = $c | (ord($buffer[++$i]) << 8);
            $c = $c | (ord($buffer[++$i]) << 16);
            $c = $c | (ord($buffer[++$i]) << 24);

            $i = 78 + $a + $b + $c;
            if (count($buffer) < ($i +1)) {
                return false;
            }

            $linkedTarget = "";
            $bufSize = sizeof($buffer);
            for (;$i < $bufSize; ++$i) {
                if (!ord($buffer[$i])) {
                    break;
                }
                $linkedTarget .= $buffer[$i];
            }

            if (empty($linkedTarget)) {
                return false;
            }

            return $linkedTarget;
        }

        return false;
    }

    /**
     * Get file owner
     *
     * @param string $filename
     *
     * @return string
     */
    protected function _getFileOwner($filename)
    {
        if (!function_exists('posix_getpwuid')) {
            return 'unknown';
        }

        $owner     = posix_getpwuid(fileowner($filename));
        $groupinfo = posix_getgrnam(filegroup($filename));
        $groupinfo = $groupinfo ? $groupinfo : filegroup($filename);

        return $owner['name'] . ' / ' . $groupinfo;
    }

    /**
     * Convert integer permissions format into human readable one
     *
     * @param integer $mode
     *
     * @return string
     */
    protected function _parsePermissions($mode)
    {
        /* FIFO pipe */
        if ($mode & 0x1000) {
            $type = 'p';
        }
        /* Character special */
        else if ($mode & 0x2000) {
            $type ='c';
        }
        /* Directory */
        else if ($mode & 0x4000) {
            $type ='d';
        }
        /* Block special */
        else if ($mode & 0x6000) {
            $type ='b';
        }
        /* Regular */
        else if ($mode & 0x8000) {
            $type ='-';
        }
        /* Symbolic Link */
        else if ($mode & 0xA000) {
            $type ='l';
        }
        /* Socket */
        else if ($mode & 0xC000) {
            $type ='s';
        }
        /* Unknown */
        else {
            $type ='u';
        }

        /* Determine permissions */
        $owner['read']      = ($mode & 00400) ? 'r' : '-';
        $owner['write']     = ($mode & 00200) ? 'w' : '-';
        $owner['execute']   = ($mode & 00100) ? 'x' : '-';
        $group['read']      = ($mode & 00040) ? 'r' : '-';
        $group['write']     = ($mode & 00020) ? 'w' : '-';
        $group['execute']   = ($mode & 00010) ? 'x' : '-';
        $world['read']      = ($mode & 00004) ? 'r' : '-';
        $world['write']     = ($mode & 00002) ? 'w' : '-';
        $world['execute']   = ($mode & 00001) ? 'x' : '-';

        /* Adjust for SUID, SGID and sticky bit */
        if ($mode & 0x800) {
            $owner['execute'] = ($owner['execute']=='x') ? 's' : 'S';
        }
        if ($mode & 0x400) {
            $group['execute'] = ($group['execute']=='x') ? 's' : 'S';
        }
        if ($mode & 0x200) {
            $world['execute'] = ($world['execute']=='x') ? 't' : 'T';
        }

        $s = sprintf('%1s', $type);
        $s .= sprintf('%1s%1s%1s', $owner['read'], $owner['write'], $owner['execute']);
        $s .= sprintf('%1s%1s%1s', $group['read'], $group['write'], $group['execute']);
        $s .= sprintf('%1s%1s%1s', $world['read'], $world['write'], $world['execute']);

        return trim($s);
    }

    /**
     * Parse and validate FTP, RSYNC, FILE, HTTP or HTTPS url and return its components
     * More convenient then parse_url()
     *
     * Potential keys within this array are:
     *     scheme - e.g. http
     *     host
     *     tld
     *     port
     *     user
     *     pass
     *     path
     *     query - after the question mark ?
     *     fragment - after the hashmark #
     *
     * @param  string $url
     *
     * @return array|bool
     */
    protected function _parseUrl($url)
    {
        $url   = (string)$url;
        $parts = $result = array();

        if(preg_match('/\A
                #scheme
                (?:(rsync|ftp|file|https?):\/\/)?
                #userinfo
                (?:
                    ([-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF.,\'@$*^=%:&amp;~+?#"()\[\]]+)@
                )?
                #host or ip
                (?:
                    #ip
                    ((?:(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]))
                    |
                    #host
                    (
                        (?:[-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF]+\.)+
                        #tld
                        ([\x41-\x5A\x61-\x7A]{2,8})?
                        |
                        [-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF]+
                    )
                )
                #port
                (?:
                    :(0|[1-9][0-9]?[0-9]?[0-9]?|[1-5][0-9][0-9][0-9][0-9]|6[0-4][0-9][0-9][0-9]|65[0-4][0-9][0-9]|655[0-2][0-9]|6553[0-5])
                )?
                #path
                (?:
                    \/([-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF.,\'@$*^=%:;\/~+"()\[\]]+)?
                )?
                #query
                (?:
                    \?([-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF.,\'@$*^=%:&amp;\/~+?"()\[\]]+)+
                )?
                #fragment
                (?:
                    \#([-0-9_\x41-\x5A\x61-\x7A\xA5\xA8\xAA\xAF\xB2\xB3\xB4\xB8\xBA\xBF-\xFF.,\'@$*^=%:&amp;\/~+?#"()\[\]]+)
                )?\Z/x', $url, $parts)
        ){
            $result['url'] = $parts[0];
            if (!empty($parts[1])){
                $result['scheme'] = $parts[1];
            }
            if (!empty($parts[2])){
                $userinfo = explode(':', $parts[2], 2);
                $result['user'] = $userinfo[0];
                if (!empty($userinfo[1])) {
                    $result['pass'] = $userinfo[1];
                }
            }
            if (!empty($parts[3])){
                $result['ip'] = $parts[3];
            }
            if (!empty($parts[4])){
                $result['host'] = $parts[4];
            }
            if (!empty($parts[5])){
                $result['tld'] = $parts[5];
            }
            if (!empty($parts[6])){
                $result['port'] = $parts[6];
            }
            if (!empty($parts[7])){
                $result['path'] = $parts[7];
            }
            if (!empty($parts[8])){
                $result['query'] = $parts[8];
            }
            if (!empty($parts[9])){
                $result['fragment'] = $parts[9];
            }

            return $result;
        }
        return false;
    }
}
