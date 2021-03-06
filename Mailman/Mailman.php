<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 * Copyright (C) 2010-2013 by i-MSCP Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @category    iMSCP
 * @package     iMSCP_Plugin
 * @subpackage  Mailman
 * @copyright   2010-2013 by i-MSCP Team
 * @author      Laurent Declercq <l.declercq@nuxwin.com>
 * @link        http://www.i-mscp.net i-MSCP Home Site
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL v2
 */

/**
 * Mailman Plugin.
 *
 * @category    iMSCP
 * @package     iMSCP_Plugin
 * @subpackage  Mailman
 * @author      Laurent Declercq <l.declercq@nuxwin.com>
 */
class iMSCP_Plugin_Mailman extends iMSCP_Plugin_Action
{
	/**
	 * @var array Map mailman URI endpoint to mailman action script
	 */
	protected $routes = array();

	/**
	 * Process plugin installation
	 *
	 * @throws iMSCP_Plugin_Exception in case installation fail
	 */
	public function install()
	{
		try {
			$this->createDbTable();
		} catch(iMSCP_Exception_Database $e) {
			throw new iMSCP_Plugin_Exception($e->getMessage());
		}
	}

	/**
	 * Register a callback for the given event(s).
	 *
	 * @param iMSCP_Events_Manager_Interface $controller
	 */
	public function register(iMSCP_Events_Manager_Interface $controller)
	{
		$controller->registerListener(
			array(
				iMSCP_Events::onClientScriptStart,
				iMSCP_Events::onBeforePluginsRoute,
				iMSCP_Events::onAfterDeleteCustomer
			),
			$this
		);
	}

	/**
	 * Implements the onBeforePluginsRoute event
	 *
	 * @return void
	 */
	public function onBeforePluginsRoute()
	{
		$this->routes = array(
			'/client/mailman.php' => PLUGINS_PATH . '/' . $this->getName() . '/frontend/mailman.php'
		);
	}

	/**
	 * Implements the onClientScriptStart event
	 *
	 * @return void
	 */
	public function onClientScriptStart()
	{
		$this->setupNavigation();
	}

	/**
	 * Implements the onAfterDeleteCustomer event
	 *
	 * This event is called when a customer account is being deleted.
	 * If triggered,  we remove any E-Mail lists for this customer
	 *
	 * @param iMSCP_Events_Event $event
	 * @return void
	 */
	public function onAfterDeleteCustomer($event)
	{
		/** @var iMSCP_Config_Handler_File $cfg */
		$cfg = iMSCP_Registry::get('config');

		exec_query(
			'UPDATE `mailman` SET `mailman_status` = ? WHERE `mailman_admin_id` = ?',
			array($cfg->ITEM_DELETE_STATUS, $event->getParam('customerId'))
		);
	}

	/**
	 * Get routes
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Inject Mailman links into the navigation object
	 */
	protected function setupNavigation()
	{
		if (iMSCP_Registry::isRegistered('navigation')) {
			/** @var Zend_Navigation $navigation */
			$navigation = iMSCP_Registry::get('navigation');

			if (($page = $navigation->findOneBy('uri', '/client/mail_accounts.php'))) {
				$page->addPage(
					array(
						'label' => tohtml(tr('E-Mail Lists')),
						'uri' => '/client/mailman.php',
						'title_class' => 'email',
						'order' => 3
					)
				);
			}
		}
	}

	/**
	 * Create mailman database table
	 *
	 * @return void
	 */
	protected function createDbTable()
	{
		execute_query(
			'
				CREATE TABLE IF NOT EXISTS `mailman` (
					`mailman_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`mailman_admin_id` int(11) unsigned NOT NULL,
					`mailman_admin_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`mailman_admin_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`mailman_list_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`mailman_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					PRIMARY KEY (`mailman_id`),
					UNIQUE KEY `mailman_list_name` (`mailman_list_name`),
					KEY `mailman_admin_id` (`mailman_admin_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			'
		);
	}
}
