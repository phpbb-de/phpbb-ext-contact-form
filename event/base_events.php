<?php
/**
 *
 * @package phpBB.de contactform
 * @copyright (c) 2015 phpBB.de, gn#36
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbde\contactform\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class base_events implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'				=> 'page_header_after',
			'core.viewonline_overwrite_location' 	=> 'viewonline_page',
			'core.ucp_pm_compose_modify_data' 		=> 'pm_warning',
		);
	}

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth			$auth		Auth object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\controller\helper	$helper 	Helper
	 * @param string			$phpbb_root_path		phpBB root path (community/)
	 * @param string			$php_ext				php file extension (php)
	 * @param string			$root_path				php file extension (...phpbb.de/)
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->helper = $helper;
		$this->php_ext = $php_ext;
		$this->user = $user;
		$this->config = $config;
		$this->request = $request;
		$this->db = $db;
	}

	public function page_header_after($event)
	{
		if(!empty($this->config['phpbbde_contactform_forum_id']))
		{
			$this->user->add_lang_ext('phpbbde/contactform', 'global');

			$this->template->assign_vars(array(
					// Main Menu
					'U_CONTACTFORM' => $this->helper->route('phpbbde_contactform_main_controller'),
			));
		}
	}

	public function viewonline_page($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/contact') === 0)
			{
				$event['location'] = $this->user->lang('CONTACTFORM_VIEWONLINE');
				$event['location_url'] = $this->helper->route('phpbbde_contactform_main_controller');
			}
		}
	}

	public function pm_warning($event)
	{
		// There is no suitable event - read the data from the submit:
		$address_list = $this->request->variable('address_list', array('' => array(0 => '')));

		// This has the drawback of not knowing about new users currently being added
		// We therefore will have to check the added recipients list for known usernames as well :/

		// This also has the drawback of still showing the warning even if the user is currently deleted.
		// So let's remove the deleted user from the list as well:
		$delete = $this->request->variable('remove_u', array(0 => ''));
		foreach($delete as $key => $value)
		{
			if(isset($address_list['u'][$key]))
			{
				unset($address_list['u'][$key]);
			}
		}

		//Wenn PN an Teammitglied gesendet werden soll, Hinweismeldung anzeigen
		//TODO: Etwas besseres als Referenz nehmen als "phpBB.de-Team" im Gruppennamen.
		if(!empty($address_list['u'])) {
			$sql = "SELECT u.user_id, ut.username
				FROM " . USER_GROUP_TABLE . " u
				LEFT JOIN " . GROUPS_TABLE . " g ON g.group_id = u.group_id
				LEFT JOIN " . USERS_TABLE . " ut ON u.user_id = ut.user_id
				WHERE g.group_name = 'phpBB.de-Team' OR g.group_name = 'phpBB Deutschland e. V.' ";
			$result = $this->db->sql_query($sql, 3600);
			$team_user_ids = array();
			$team_user_names = array();
			while ( $row = $this->db->sql_fetchrow($result) )
			{
				$team_user_ids[] = $row['user_id'];
				$team_user_names[$row['user_id']] = $row['username'];
			}

			if(count(array_intersect(array_keys($address_list['u']), $team_user_ids)) > 0)
			{
				$this->template->assign_var('S_PN_TO_TEAM_MEMBER', true);
				return;
			}

			// This is only necessary, if we didn't find a member yet.
			$new_recipients = explode("\n", $this->request->variable('username_list', '', true));

			if(sizeof($team_user_names) < sizeof($new_recipients))
			{
				$new_recipients = array_map('trim', $new_recipients);
				foreach($team_user_names as $username)
				{
					if(in_array($username, $new_recipients))
					{
						$this->template->assign_var('S_PN_TO_TEAM_MEMBER', true);
						return;
					}
				}
			}
			else
			{
				foreach($new_recipients as $username)
				{
					if(in_array(trim($username), $team_user_names))
					{
						$this->template->assign_var('S_PN_TO_TEAM_MEMBER', true);
						return;
					}
				}
			}
		}
	}
}
