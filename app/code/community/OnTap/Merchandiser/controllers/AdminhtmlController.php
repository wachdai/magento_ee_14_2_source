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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_AdminhtmlController extends Mage_Adminhtml_Controller_Action
{
    /**
     * initAction
     */
    public function initAction()
    {
        $session = Mage::getSingleton('core/session', array('name' => 'frontend'));
        if (!$session->getData('user')) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $session->setData('user', $user);
        }
    }

    /**
     * openAction
     */
    public function openAction()
    {
        $catId = $this->getRequest()->getParam('category_id');
        $this->_redirect("merchandiser/adminhtml/index", array(
            'category_id' => $catId,
        ));
    }

    /**
     * saveOrderingAction
     */
    public function saveOrderingAction()
    {
        $catId = $this->getRequest()->getPost('category_id');
        $params = $this->getRequest()->getPost();
        $insertData = array();

        if (is_numeric($catId)) {
            $_category = Mage::getModel('catalog/category')->load($catId);
            $products = $this->getRequest()->getPost('product');
            $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');

            $heroProductsIds = array();
            $heroProductSkus = '';
            $iCounter = 0;
            if (isset($params['heroproducts'])) {
                $heroProductsIds = $params['heroproducts'];
                foreach ($heroProductsIds as $heroProductId) {
                    $heroProductId = (int)$heroProductId;
                    if ($heroProductId && $heroProductId > 0 && is_int($heroProductId)) {
                        $heroProductModelObject = Mage::getModel('catalog/product')->load($heroProductId);
                        $heroProductSkus .= $heroProductModelObject->getSku() . ",";
                        $iCounter++;
                        $insertData[] = array(
                            'category_id'=>$catId,
                            'product_id'=>$heroProductId,
                            'position' => $iCounter);
                    }
                }
            }

            $merchandiserResourceModel->clearCategoryProducts($catId);

            if (is_array($products)) {
                unset($products[Mage::helper('merchandiser')->getEmptyId()]);

                if (sizeof($products) > 0) {
                    foreach ($products as $productId => $productPos) {
                        $productPos = $iCounter + $productPos;
                        if (in_array($productId, $heroProductsIds)) {
                            continue;
                        }
                        if (is_int($productId) && is_int($productPos)) {
                            $insertData[] = array(
                                'category_id'=>$catId,
                                'product_id'=>$productId,
                                'position' => $productPos);
                        }
                    }
                    if (sizeof($insertData) > 0) {
                        $merchandiserResourceModel->insertMultipleProductsToCategory($insertData);
                    }
                }
            }

            $heroProductSkus = (strlen($heroProductSkus) > 0) ?
                substr($heroProductSkus, 0, strlen($heroProductSkus) -1) : $heroProductSkus;
            $categoryValueInsertData = array('heroproducts' => $heroProductSkus);
            $merchandiserResourceModel->updateCategoryProducts($catId, $categoryValueInsertData);

            Mage::helper('merchandiser')->afterSaveCategory($_category);

        }
        $this->_redirect("merchandiser/adminhtml", array('category_id' => $catId, 'up'=>1));
    }

    /**
     * processActionAction
     */
    public function processActionAction()
    {
        $catId = $this->getRequest()->getPost('category_id');
        $columnCount = $this->getRequest()->getParam('column_count') ?
            $this->getRequest()->getParam('column_count') :
            intval(Mage::getStoreConfig('catalog/merchandiser/column_count'));

        if (is_numeric($catId)) {
            $params = array();
            $params['catId'] = $catId;
            $params['column_count'] = $columnCount;

            $helper = Mage::helper('merchandiser');
            $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');

            $actions = $helper->getConfigAction();
            $actionIndex = $this->getRequest()->getParam('action_index');
            $string = explode('::', $actions[$actionIndex]['sorting_function']);
            $controllerName = $string[0];
            $functionName = $string[1];

            if (is_array($string)) {
                $controllerObject = new $controllerName;
                call_user_func(array($controllerObject,$functionName), $params);
                $_category = Mage::getModel('catalog/category')->load($catId);

                $categoryList = new OnTap_Merchandiser_Block_Category_List;
                $categoryProducts = $categoryList->getProductCollection();

                $heroProducts = Mage::getModel('merchandiser/merchandiser')->getCategoryValues($catId, 'heroproducts');
                $iCounter = 0;
                $finalProductsArray = array();
                $productObject = Mage::getModel('catalog/product');
                $insertData = array();

                foreach (explode(",", $heroProducts) as $heroSKU) {
                    if ($heroSKU != '' && $productId = $productObject->getIdBySku(trim($heroSKU))) {
                        if ($productId > 0) {
                            $iCounter++;
                            $finalProductsArray[] = $productId;
                            $insertData[] = array(
                                'category_id' => $catId,
                                'product_id' => $productId,
                                'position' => $iCounter);
                        }
                    }
                }

                if ($heroProducts != '' && sizeof($finalProductsArray) > 0) {
                    foreach ($categoryProducts as $product) {
                        $productId = $product->getId();
                        if (!in_array($productId, $finalProductsArray)) {
                            $insertData[] = array(
                                'category_id' => $catId,
                                'product_id' => $productId,
                                'position' => $iCounter);
                            $iCounter++;
                        }
                    }
                    if (sizeof($insertData) > 0) {
                        $merchandiserResourceModel->clearCategoryProducts($catId);
                        $merchandiserResourceModel->insertMultipleProductsToCategory($insertData);
                    }
                }

                Mage::helper('merchandiser')->afterSaveCategory($_category);
            }
        }
        $this->_redirect("merchandiser/adminhtml", array('category_id' => $catId));
    }

    /**
     * attachActionAction
     */
    public function attachActionAction()
    {
        $output = "";
        try {
            $catId = $this->getRequest()->getParam('category_id');
            $actionIndex = (int)$this->getRequest()->getParam('action_index');
            $category = Mage::getModel("catalog/category")->load($catId);
            $category->setCatalogAction($actionIndex);
            $category->save();
            $output = 'success';
        } catch(Exception $e) {
            $output = 'fail';
        }
        $this->loadLayout(false);
        $this->renderLayout();
        $this->getResponse()->setBody($output);
    }

    /**
     * indexAction
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * loadajaxAction
     */
    public function loadajaxAction()
    {
        $block =  $this->getLayout()
            ->createBlock('merchandiser/category_list')
            ->setTemplate('merchandiser/new/category/ajax.phtml');

        $this->loadLayout(false);
        $this->renderLayout();
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * searchAction
     */
    public function searchAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * getproductinfoAction
     */
    public function getproductinfoAction()
    {
        $res = "";
        $sku = $this->getRequest()->getParam('sku', false);
        if ($sku) {
            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if ($_product) {
                $productBox =  $this->getLayout()
                    ->createBlock('merchandiser/adminhtml_catalog_product_list')
                    ->setTemplate('merchandiser/new/category/productbox.phtml');
                $productBox->setPid($_product->getId());
                $res = $productBox->toHtml();
            }
        }

        $this->loadLayout(false);
        $this->renderLayout();
        $this->getResponse()->setBody($res);
    }

    /**
     * updatePositionAction
     */
    public function updatePositionAction()
    {
        $params = $this->getRequest()->getPost();
        $categoryId = (int)isset($params['cat'])?$params['cat'] : 0;

        if ($categoryId > 0 && $_category = Mage::getModel('catalog/category')->load($categoryId)) {
            $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');

            $productIdsArray = explode(",", $params['prods']);
            $productIdsArray = array_filter($productIdsArray);

            $maxPosition = $merchandiserResourceModel->getMaxPositionFromCategory($categoryId);
            $maxPosition += 1;

            $merchandiserResourceModel->deleteSpecificProducts($categoryId, implode(",", $productIdsArray));

            $insertData = array();
            foreach ($productIdsArray as $productId) {
                $productId = (int)$productId;
                if ($productId != '' && $productId > 0 && is_int($productId)) {
                    $insertData[] = array(
                        'category_id' => $categoryId,
                        'product_id' => $productId,
                        'position' => $maxPosition);
                    $maxPosition++;
                }
            }

            if (sizeof($insertData) > 0) {
                $merchandiserResourceModel->insertMultipleProductsToCategory($insertData);
            }

            Mage::helper('merchandiser')->afterSaveCategory($_category);

            $this->_redirect("merchandiser/adminhtml", array('category_id' => $categoryId));
        }
    }

    /**
     * rebuildcategoriesAction
     */
    public function rebuildcategoriesAction()
    {
        $params = $this->getRequest()->getParams();
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $categoryValues = $merchandiserResourceModel->fetchCategoriesValues();

        try {
            foreach ($categoryValues as $categoryVal) {
                Mage::getModel('merchandiser/merchandiser')->affectCategoryBySmartRule($categoryVal['category_id']);
                $merchandiserResourceModel->applySortAction($categoryVal['category_id']);
                $category = Mage::getModel('catalog/category')->load($categoryVal['category_id']);

                Mage::dispatchEvent('catalog_category_save_commit_after', array(
                    'category' => $category,
                ));
                Mage::helper('merchandiser')->clearCategoryCache($categoryVal['category_id']);
            }

            $this->_getSession()->addSuccess(
                Mage::helper('merchandiser')->__("Visual Merchandiser categories rebuilt successfully.")
            );

        } catch(Exception $e) {
            Mage::log("REBUILD SMART CATEGORIES ERROR : " . $e->getMessage());
            $this->_getSession()->addError($e->getMessage());
        }

        if (isset($params['toconfig']) && $params['toconfig'] == 1) {
            $this->_redirect("adminhtml/system_config/edit", array('section'=>'catalog'));
        } else {
            $this->_redirect("adminhtml/cache");
        }
    }

    /**
     * downloadskusAction
     */
    public function downloadskusAction()
    {
        $fileName = 'skus.csv';
        $params = $this->getRequest()->getParams();

        $productIDs = $params['product_ids'];
        $productIDArray = explode(',', $productIDs);

        $skuArray = array_unique($productIDArray);

        $skuArray = implode("\n", $skuArray);
        $content = $skuArray;
        $contentType = 'application/octet-stream';

        $this->loadLayout(false);
        $this->renderLayout();

        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
    }

    /**
     * storefrontAction
     */
    public function storefrontAction()
    {
        $storeFront = $this->getRequest()->getParam('store_front');
        Mage::getSingleton('adminhtml/session')->setStoreFront($storeFront);
    }
}
