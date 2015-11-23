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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Staging edit block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/staging/staging/edit.phtml');
        $this->setId('enterprise_staging_edit');

        $this->setEditFormJsObject('enterpriseStagingForm');
    }

    /**
     * Retrieve currently edited staging object
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }

    /**
     * Prepare layout
     */
    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('enterprise_staging')->__('Back'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store', 0))).'\')',
                    'class' => 'back'
                ))
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('enterprise_staging')->__('Reset'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
                ))
        );

        if ($this->getStaging()->canMerge()) {
            $this->setChild('merge_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Merge...'),
                        'onclick'   => 'setLocation(\''.$this->getMergeUrl().'\')',
                        'class'     => 'add'
                    ))
            );
        } elseif ($this->getStaging()->getId()) {
            $this->setChild('merge_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Merge'),
                        'class'     => 'disabled'
                    ))
            );
        }

        if ($this->getStaging()->canSave()) {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Save'),
                        'onclick'   => $this->getEditFormJsObject().'.submit()',
                        'class' => 'save'
                    ))
            );
        } else {
            if ($this->getRequest()->getParam('type')) {
                $this->setChild('create_button',
                    $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label'     => Mage::helper('enterprise_staging')->__('Create'),
                            'onclick'   => $this->getEditFormJsObject().'.runCreate()',
                            'class'  => 'add'
                        ))
                );
            }
        }

        if ($this->getStaging()->canResetStatus()) {
            $this->setChild('reset_status_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Reset Status'),
                        'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/resetStatus', array('_current'=>true)) . '\')',
                        'class' => 'reset'
                ))
            );
        }

        $stagingId = $this->getStagingId();
        if ($stagingId && $this->getStaging()->isScheduled()) {
            $this->setChild('unschedule_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Unschedule Merge'),
                        'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/unschedule', array('id' => $stagingId)) . '\')',
                        'class' => 'reset'
                ))
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Return Back button as html
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Return Cansel button as html
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Return Save button as html
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Return Save button as html
     */
    public function getResetStatusButtonHtml()
    {
        return $this->getChildHtml('reset_status_button');
    }

    /**
     * Return SaveandEdit button as html
     */
    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    /**
     * Return Merge button as html
     */
    public function getMergeButtonHtml()
    {
        return $this->getChildHtml('merge_button');
    }

    /**
     * Return validation url
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    /**
     * Return save url
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }

    /**
     * REturn SaveandEdit Url
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
            'tab'       => '{{tab_id}}'
        ));
    }

    /**
     * Return staging id
     */
    public function getStagingId()
    {
        return $this->getStaging()->getId();
    }

    /**
     * Return delete url
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current'=>true));
    }

    /**
     * Return merge url
     */
    public function getMergeUrl()
    {
        return $this->getUrl('*/*/merge', array('_current'=>true));
    }

    /**
     * Return sync url
     */
    public function getSyncUrl()
    {
        return $this->getUrl('*/*/sync', array('_current'=>true));
    }

    /**
     * Return rollback url
     */
    public function getRollbackUrl()
    {
        return $this->getUrl('*/*/rollback', array('_current'=>true));
    }

    /**
     * Return header
     */
    public function getHeader()
    {
        $header = '';
        if ($this->getStaging()->getId()) {
            $header = $this->escapeHtml($this->getStaging()->getName());
        } else {
            $header = Mage::helper('enterprise_staging')->__('Create New Staging Website');
        }
        $setName = $this->getStagingEntitySetName();
        if ($setName) {
            $header.= ' (' . $setName . ')';
        }
        return $header;
    }

    /**
     * return selected table id
     *
     * @return string
     */
    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }

    /**
     * Retrieve master website id
     * if master website is not available return 0
     *
     * @return mixed
     */
    public function getMasterWebsiteId()
    {
        $website = $this->getStaging()->getMasterWebsite();
        if ($website) {
            return $website->getId();
        }

        return 0;
    }
}
