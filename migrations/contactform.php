<?php

/**
 *
 * @package phpBB.de contactform
 * @copyright (c) 2014 phpBB.de, gn#36
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbde\contactform\migrations;

class contactform extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array();
	}

	public function effectively_installed()
	{
		return !empty($this->config['phpbbde_contactform_forum_id']);
	}

	public function update_data()
	{
		return array(
				array('config.add', array('phpbbde_contactform_forum_id', 0)),
		);
	}

}
