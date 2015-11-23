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
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

 /**
 * Enterprise search model indexer
 *
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Indexer_Indexer
{
    /**
     * Indexation mode that provide commit after all documents are added to index.
     * Products are not visible at front before indexation is not completed.
     */
    const SEARCH_ENGINE_INDEXATION_COMMIT_MODE_FINAL   = 0;

    /**
     * Indexation mode that provide commit after defined amount of products.
     * Products become visible after products bunch is indexed.
     * This is not auto commit using search engine feature.
     *
     * @see Mage_CatalogSearch_Model_Resource_Fulltext::_getSearchableProducts() limitation
     */
    const SEARCH_ENGINE_INDEXATION_COMMIT_MODE_PARTIAL = 1;

    /**
     * Indexation mode when commit is not provided by Magento at all.
     * Changes will be applied after third party search engine autocommit will be called.
     *
     * @see e.g. /lib/Apache/Solr/conf/solrconfig.xml : <luceneAutoCommit/>, <autoCommit/>
     */
    const SEARCH_ENGINE_INDEXATION_COMMIT_MODE_ENGINE  = 2;

    /**
     * Xml path for indexation mode configuration
     */
    const SEARCH_ENGINE_INDEXATION_COMMIT_MODE_XML_PATH = 'catalog/search/engine_commit_mode';






    /**
     * Reindex of catalog search fulltext index using search engine
     *
     * @return Enterprise_Search_Model_Indexer_Indexer
     */
    public function reindexAll()
    {
        $helper = Mage::helper('enterprise_search');
        if ($helper->isThirdPartyEngineAvailable()) {
            /* Change index status to running */
            $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
            if ($indexProcess) {
                $indexProcess->reindexAll();
            }
        }

        return $this;
    }





    /**
     * Change indexes status to defined
     *
     * @deprecated after 1.11.0.0
     *
     * @param   string|array $indexList
     * @param   string $status
     * @return  Enterprise_Search_Model_Indexer_Indexer
     */
    protected function _changeIndexesStatus($indexList, $status)
    {
        $indexer = Mage::getSingleton('index/indexer');

        if (!is_array($indexList)) {
            $indexList = array($indexList);
        }

        foreach ($indexList as $index) {
            $indexer->getProcessByCode($index)->changeStatus($status);
        }

        return $this;
    }
}
