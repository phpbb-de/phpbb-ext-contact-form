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
			array('permission.add', array('m_kontakt_form', true, 'm_edit')),
			array('custom', array(
				array(&$this, 'find_correct_forum_id'),
			)),
		);
	}

	public function find_correct_forum_id()
	{
		$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . " WHERE forum_name = 'Kontaktformular'";
		$result = $this->db->sql_query($sql);
		$forum_id = $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);
		if($forum_id)
		{
			$this->config->set('phpbbde_contactform_forum_id', $forum_id);
		}
	}

}
