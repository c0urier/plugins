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
 * @subpackage  JailKit
 * @copyright   2010-2013 by i-MSCP Team
 * @author      Sascha Bay <info@space2place.de>
 * @link        http://www.i-mscp.net i-MSCP Home Site
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL v2
 */

return array(
	'jailfolder' => '/imscp-jails', # Jail folder for all ssh jails. The complete path "must" owned by root:root! Never change this folder while updating the plugin to a new version!
	'default_shell' => '/bin/bash', # If you change this, i hope you know what you are doing
	'default_jail_apps' => array(
		'basicshell',
		'editors',
		'sftp'
	),
	'jail_sockettd_base' => '512',
	'jail_sockettd_peak' => '2048',
	'jail_sockettd_interval' => '10',
	
	'max_allowed_ssh-user' => '1'
);
