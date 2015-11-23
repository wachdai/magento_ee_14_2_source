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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reports invitation order report collection
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Invitation_Model_Resource_Report_Invitation_Order_Collection
    extends Enterprise_Invitation_Model_Resource_Report_Invitation_Collection
{
    /**
     * Join custom fields
     *
     * @return Enterprise_Invitation_Model_Resource_Report_Invitation_Order_Collection
     */
    protected function _joinFields()
    {
;
        $acceptedExpr = 'SUM('
            . $this->getConnection()->getCheckSql(
                'main_table.status = '
                    . $this->getConnection()->quote(Enterprise_Invitation_Model_Invitation::STATUS_ACCEPTED)
                    . 'AND main_table.referral_id IS NOT NULL',
                '1',
                '0'
            )
            . ')';
        $canceledExpr = 'SUM('
            . $this->getConnection()->getCheckSql(
                'main_table.status = '
                    . $this->getConnection()->quote(Enterprise_Invitation_Model_Invitation::STATUS_CANCELED),
                '1',
                '0'
            )
            . ')';

        $this->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('sent' => new Zend_Db_Expr('COUNT(main_table.invitation_id)')))
            ->columns(array('accepted' => new Zend_Db_Expr($acceptedExpr)))
            ->columns(array('canceled' => new Zend_Db_Expr($canceledExpr)));

        return $this;
    }

    /**
     * Additional data manipulation after collection was loaded
     *
     * @return Enterprise_Invitation_Model_Resource_Report_Invitation_Order_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->getItems() as $item) {
            if ($item->getSent()) {
                $item->setCanceledRate($item->getCanceled() / $item->getSent() * 100);
                $item->setAcceptedRate($item->getAccepted() / $item->getSent() * 100);
            } else {
                $item->setCanceledRate(0);
                $item->setAcceptedRate(0);
            }

            $item->setPurchased($this->_getPurchaseNumber(clone $this->getSelect()));

            if ($item->getAccepted()) {
                $item->setPurchasedRate($item->getPurchased() / $item->getAccepted() * 100);
            } else {
                $item->setPurchasedRate(0);
            }
        }

        return $this;
    }

    /**
     * Calculate number of purchase from invited customers
     *
     * @param Zend_Db_Select $select
     * @return bool|int
     */
    protected function _getPurchaseNumber($select)
    {
        /* var $select Zend_Db_Select */
        $select->reset(Zend_Db_Select::COLUMNS)
            ->joinRight(array('o' => $this->getTable('sales/order')),
                'o.customer_id = main_table.referral_id AND o.store_id = main_table.store_id',
                array('cnt' => 'COUNT(main_table.invitation_id)'));
       return $this->getConnection()->fetchOne($select);
    }
}
