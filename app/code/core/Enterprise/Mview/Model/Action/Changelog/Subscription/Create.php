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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Changelog subscribe action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Changelog_Subscription_Create
    extends Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Target column name
     *
     * @var string
     */
    protected $_targetColumn;

    /**
     * Constructor
     * Arguments:
     *  target_column - string.
     *
     * @param array $args
     * @throws InvalidArgumentException
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        if (empty($args['target_column'])) {
            throw new InvalidArgumentException('Target column is missing');
        }

        $this->_targetColumn = $args['target_column'];
    }

    /**
     * Subscribe changelog table
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Subscription_Create
     */
    public function execute()
    {
        $this->_getSubscriber()->save();

        $this->_createTriggers();
        return $this;
    }

    /**
     * Initialize and return subscriber model
     *
     * @return Enterprise_Mview_Model_Subscriber
     */
    protected function _getSubscriber()
    {
        $subscriber = $this->_factory->getModel('enterprise_mview/subscriber');
        $subscriber->setData('metadata_id', $this->_metadata->getId())
            ->setData('target_table', $this->_targetTable)
            ->setData('target_column', $this->_targetColumn);
        return $subscriber;
    }
}
