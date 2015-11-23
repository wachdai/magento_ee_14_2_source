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
 * Invitation status history model
 *
 * @method Enterprise_Invitation_Model_Resource_Invitation_History _getResource()
 * @method Enterprise_Invitation_Model_Resource_Invitation_History getResource()
 * @method int getInvitationId()
 * @method Enterprise_Invitation_Model_Invitation_History setInvitationId(int $value)
 * @method string getDate()
 * @method Enterprise_Invitation_Model_Invitation_History setDate(string $value)
 * @method string getStatus()
 * @method Enterprise_Invitation_Model_Invitation_History setStatus(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Invitation_Model_Invitation_History extends Mage_Core_Model_Abstract
{
    /**
     * Mapping old names
     * @var array
     */
    protected $_oldFieldsMap = array('invitation_date' => 'date');

    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_invitation/invitation_history');
    }

    /**
     * Return status text
     *
     * @return string
     */
    public function getStatusText()
    {
        return Mage::getSingleton('enterprise_invitation/source_invitation_status')->getOptionText(
            $this->getStatus()
        );
    }

    /**
     * Set additional data before saving
     *
     * @return Enterprise_Invitation_Model_Invitation_History
     */
    protected function _beforeSave()
    {
        $this->setDate($this->getResource()->formatDate(time()));
        return parent::_beforeSave();
    }
}
