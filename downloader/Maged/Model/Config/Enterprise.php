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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
* Class config
*
* @category   Mage
* @package    Mage_Connect
* @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Maged_Model_Config_Enterprise extends Maged_Model_Config_Abstract implements Maged_Model_Config_Interface
{

    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->load();
    }

    /**
     * Get Auth data from config
     * @param mixed $session Session object
     * @return array auth data
     */
    private function _getAuth($session)
    {
        $auth = $session->get('auth');
        return array_values($auth);
    }

    /**
     * Set data for Settings View
     *
     * @param Mage_Connect_Config $config
     * @param Maged_View $view
     * @return null
     */
    public function setInstallView($config, $view)
    {
        $root_channel = $this->get('root_channel');
        $view->set('channel_logo', $root_channel);
        $view->set('channel_steps', $view->template($root_channel . '/install_steps.phtml'));
        $view->set('channel_notice', $view->template($root_channel . '/install_notice.phtml'));
        $view->set('channel_protocol_fields', $view->template($root_channel . '/auth.phtml'));
    }

    /**
     * Set data for Settings View
     * @param mixed $session Session object
     * @param Maged_View $view
     * @return null
     */
    public function setSettingsView($session, $view)
    {
        $auth = $this->_getAuth($session);
        if ($auth) {
            $view->set('auth_username', isset($auth[0]) ? $auth[0] : '');
            $view->set('auth_password', isset($auth[1]) ? $auth[1] : '');
        }
        $view->set('channel_protocol_fields', $view->template($this->get('root_channel') . '/auth.phtml'));
    }

    /**
     * Set session data for Settings
     * @param array $post post data
     * @param mixed $session Session object
     * @return null
     */
    public function setSettingsSession($post, $session)
    {
        if (isset($post['auth_username']) && isset($post['auth_password'])) {
             $session->set('auth', array(
                 'username' => $post['auth_username'],
                 'password' => $post['auth_password']
            ));
        } else {
            $session->set('auth', array());
        }
    }

    /**
     * Get root channel URI
     *
     * @return string Root channel URI
     */
    public function getRootChannelUri(){
        if (!$this->get('root_channel_uri')) {
            $this->set('root_channel_uri', 'connect20.magentocommerce.com/enterprise');
        }
        return $this->get('root_channel_uri');
    }

    /**
     * Set config data from POST
     *
     * @param Mage_Connect_Config $config Config object
     * @param array $post post data
     * @return null
     */
    public function setPostData($config, &$post)
    {
        if (!empty($post['auth_username']) and isset($post['auth_password'])) {
            $post['auth'] = $post['auth_username'] .'@'. $post['auth_password'];
        } else {
            $post['auth'] = '';
        }
        if(!is_null($config)){
            $config->auth = $post['auth'];
            $config->root_channel_uri = $this->getRootChannelUri();
            $config->root_channel = $this->get('root_channel');
        }
    }

    /**
     * Set additional command options
     *
     * @param mixed $session Session object
     * @param array $options
     * @return null
     */
    public function setCommandOptions($session, &$options)
    {
        $auth = $this->_getAuth($session);
        $options['auth'] = array(
                'username' => $auth[0],
                'password' => $auth[1],
        );
    }

    /**
     * Return channel label for channel name
     *
     * @param string $channel
     * @return string
     */
    public function getChannelLabel($channel)
    {
        $channelLabel = '';
        switch($channel)
        {
            case 'community':
                $channelLabel = 'Magento Community Edition';
                break;
            case 'enterprise':
                $channelLabel = 'Magento Enterprise Edition';
                break;
            default:
                $channelLabel = $channel;
                break;
        }
        return $channelLabel;
    }
}
?>
