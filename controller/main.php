<?php

/**
 *
 * @package phpBB.de contactform
 * @copyright (c) 2015 phpBB.de, gn#36
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbde\contactform\controller;

use Gn36\OoPostingApi\post;
use Gn36\OoPostingApi\topic;
use Gn36\OoPostingApi\privmsg;

class main
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Construct
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\cache\service $cache
	 * @param \phpbb\request\request $request
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbbde\pastebin\functions\pastebin $pastebin
	 * @param \phpbbde\pastebin\functions\utility $util
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\captcha\factory $captcha_factory, $root_path, $php_ext)
	{
		include __DIR__ . '/../vendor/autoload.php';

		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->request = $request;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$this->captcha_factory = $captcha_factory;

		$user->add_lang_ext('phpbbde/contactform', 'kontakt');
	}

	public function process($mode)
	{
		$REPLIED_PREFIX = '[Beantwortet]';
		$user = $this->user;
		$auth = $this->auth;
		$db = $this->db;
		$template = $this->template;
		$request = $this->request;
		$phpEx = $this->php_ext;
		$phpbb_root_path = $this->root_path;

		// User has to be logged in to use the kontaktformular
		if (!$user->data['is_registered'])
		{
			login_box('', $user->lang['KONTAKT_USE_LOGIN_REQ']);
		}

		// Check for disabled form:
		if (!$this->config['phpbbde_contactform_forum_id'])
		{
			trigger_error('KONTAKT_DISABLED');
		}

		$user->add_lang('posting');

		if ($mode == 'reply')
		{
			// Check Auth:
			if (!$auth->acl_get('m_kontakt_form'))
			{
				trigger_error('NOT_AUTH_MOD_KONTAKT');
			}
			else
			{
				$post_id = intval($request->variable('p',0));
				$post = post::get($post_id);

				if (!$post)
				{
					trigger_error('NO_POST');
				}

				// First check if the post has already been approved or denied:
				if (strpos($post->post_subject, $REPLIED_PREFIX) !== false)
				{
					trigger_error('CANNOT_HANDLE_TWICE');
				}

				$author_anonymous = ($post->poster_id == ANONYMOUS) ? true : false;

				$template->assign_vars(array(
					#'S_STATIC'          => true,
					#'S_PHPBBDE'         => true,
					#'S_KONTAKT_FORM'    => true,
					'S_BBCODE_ALLOWED'  => true,
					'S_BBCODE_IMG'      => true,
					'S_LINKS_ALLOWED'   => true,
					'S_BBCODE_QUOTE'	=> true,
					'S_BBCODE_FLASH'    => false,
					'S_AUTHOR_ANONYMOUS'=> $author_anonymous,
				));

				if (confirm_box(true))
				{
					$reply_message = $request->variable('message', '', true);

					if (strlen($reply_message) < 5)
					{
						trigger_error('TOO_FEW_CHARS');
					}

					// Now edit the post and post a reply:

					$reply_link = ($this->helper->route('phpbbde_contactform_controller', array('mode' => 'reply', 'p' => $post->post_id), true, '', \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
					$search = sprintf($user->lang['KONTAKT_REPLY_LINK'], $reply_link);
					$post->post_text = html_entity_decode($post->post_text, ENT_NOQUOTES, 'utf-8'); // weil wir den Text nicht in ein Formular packen sondern intern verwenden muss noch der Entitymist weg
					$post->post_text = str_replace($search, '', $post->post_text);
					$subject = $post->post_subject;
					$post->post_subject = $REPLIED_PREFIX . $post->post_subject;
					$post->submit();

					// Generate the reply post
					$reply = new post();
					$reply->topic_id = $post->topic_id;
					$reply->forum_id = $post->forum_id;
					$reply->post_text = sprintf($author_anonymous ? $user->lang['KONTAKT_REPLIED_ANONYMOUS_POST_TEXT'] : $user->lang['KONTAKT_REPLIED_POST_TEXT'], $reply_message, $post->post_id);
					$reply->post_subject = $author_anonymous ? $user->lang['KONTAKT_REPLIED_ANONYMOUS_POST_SUBJECT'] : $user->lang['KONTAKT_REPLIED_POST_SUBJECT'];
					$reply->submit();

					if (!$author_anonymous)
					{
						// Inform the user:
						$rueckfrage_link = ($this->helper->route('phpbbde_contactform_main_controller', array('ref' => $post->topic_id), true, '', \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
						$pm = new privmsg();
						$pm->message_subject = $user->lang['KONTAKT_PM_REPLY_SUBJECT'];
						$pm->message_text = sprintf($user->lang['KONTAKT_PM_REPLY_TEXT'], $subject, $post->post_text, $reply_message, $rueckfrage_link);
						$pm->to($post->poster_id);
						$pm->submit();
					}

					// Inform moderator:
					$message = ($author_anonymous ? $user->lang['KONTAKT_REPLIED_ANONYMOUS'] : $user->lang['KONTAKT_REPLIED']) . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid($phpbb_root_path . "viewtopic.$phpEx", 'p=' . $post_id) . '">', '</a>');
					trigger_error($message);
				}
				else
				{
					confirm_box(false, 'KONTAKT_REPLY', '', 'kontakt_reply.html');
				}

				// Nothing done: return to topic
				meta_refresh(3, append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_id));
				trigger_error($user->lang['KONTAKT_NOT_REPLIED'] . "<br /><br />" . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid($phpbb_root_path . "viewtopic.$phpEx", 'p=' . $post_id) . '">', '</a>'));
			}
		}

		$submit = $request->is_set('submit', \phpbb\request\request_interface::POST) ? true : false;
		$ref = $request->variable('ref', 0);
		$reklamation_post = $request->variable('reklamation_post', 0);
		$reklamation_titel = $request->variable('reklamation_titel', '', true);
		//$reklamation_pn = $request->variable('reklamation_pn', 0);

		$ref_topic = null;
		if ($ref)
		{
			$ref_topic = topic::get($ref);

			//Da wir nur mit den Standard phpBB Tabellen+Spalten arbeiten, müssen wir prüfen,
			//ob das Thema im Kontaktformular-Forum ist, damit der Benutzer über dieses Formular
			//nicht einfach auf beliebige Themen antworten kann
			if(!$ref_topic || $ref_topic->forum_id != $this->config['phpbbde_contactform_forum_id']) {
				trigger_error('INVALID_REF_TOPIC');
			}
		}


		// Check sent data
		if ($submit)
		{
			$subject = trim(utf8_normalize_nfc($request->variable('subject', '', true)));
			$message = trim(utf8_normalize_nfc($request->variable('message', '', true)));

			$error = array();

			if (!$ref_topic && (!$subject || !utf8_clean_string($subject)))
			{
				$error[] = $user->lang['NO_SUBJECT'];
			}

			if (!$message || !utf8_clean_string($message))
			{
				$error[] = $user->lang['TOO_FEW_CHARS'];
			}

			if (!empty($error))
			{
				// Fill the fields again and Output the Error-Message
				$template->assign_vars(array(
					'ERROR'   => implode('<br /><br />', $error),
					'SUBJECT' => $subject,
					'MESSAGE' => $message,
				));
			}
			else
			{
				// All Data available
				$post = new post();
				$post->forum_id = $this->config['phpbbde_contactform_forum_id'];
				$post->post_text = $message;
				if ($ref_topic)
				{
					$ref_title_no_prefix = $ref_topic->topic_title;
					if (strpos($ref_title_no_prefix, $REPLIED_PREFIX) === 0) {
						$ref_title_no_prefix = substr($ref_title_no_prefix, strlen($REPLIED_PREFIX));
					}
					$post->post_subject = 'Re: ' . $ref_title_no_prefix;
					$post->topic_id = $ref_topic->topic_id;
				}
				else
				{
					$post->post_subject =  $subject;
				}

				//Beitragszähler nicht erhöhen, damit die Anzahl der öffentlich sichtbaren Beiträge mit der Anzahl im Profil übereinstimmt
				$post->post_postcount = 0;
				$post->submit();

				// Now we have to edit the post to add the necessary links for approving or denying topics
				$reply_link = ($this->helper->route('phpbbde_contactform_controller', array('mode' => 'reply', 'p' => $post->post_id), true, '', \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
				$post->post_text .= "\n\n" . sprintf($user->lang['KONTAKT_REPLY_LINK'], $reply_link);
				$post->submit();

				if ($ref_topic && strpos($ref_topic->topic_title, $REPLIED_PREFIX) === 0)
				{
					//Bei Antwort ggf. den [Beantwortet] Prefix entfernen
					$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ';
					$new_topic_title = substr($ref_topic->topic_title, strlen($REPLIED_PREFIX));
					$sql .= " topic_title='" . $db->sql_escape($new_topic_title) . "'";
					$sql .= ' WHERE topic_id=' . $post->topic_id;
					$db->sql_query($sql);
				}

				$message = $user->lang['KONTAKT_STORED'];
				$message .= '<br /><br />' . sprintf($user->lang['RETURN_UEBER_UNS'], '<a href="' . append_sid("{$phpbb_root_path}../phpbbde/") . '">', '</a>');

				meta_refresh(3, append_sid("{$phpbb_root_path}../phpbbde/"));
				trigger_error($message);
			}
		}
		elseif ($reklamation_post)
		{
			$template->assign_var('SUBJECT', substr($reklamation_titel, 0, 46));
			$template->assign_var('MESSAGE', sprintf($user->lang['REKLAMATION_POST_MESSAGE'], generate_board_url() . "/viewtopic.$phpEx?p=" . $reklamation_post . '#p' . $reklamation_post, $reklamation_titel));
		}

		//Benutzer soll [Beantwortet] nicht sehen
		$ref_topic_title_no_prefix = false;
		if ($ref_topic)
		{
			$ref_topic_title_no_prefix = $ref_topic->topic_title;
			if (strpos($ref_topic_title_no_prefix, $REPLIED_PREFIX) === 0)
			{
				$ref_topic_title_no_prefix = substr($ref_topic_title_no_prefix, strlen($REPLIED_PREFIX));
			}
		}

		$template->assign_vars(array(
			'S_STATIC'          => true,
			'S_PHPBBDE'         => true,
			'S_KONTAKT_FORM'    => true,
			'S_POST_ACTION'		=> $this->helper->route('phpbbde_contactform_main_controller'),
			'S_BBCODE_ALLOWED'  => true,
			'S_BBCODE_IMG'      => true,
			'S_LINKS_ALLOWED'   => true,
			'S_BBCODE_QUOTE'	=> true,
			'S_BBCODE_FLASH'    => false,
			'SUBTITLE'          => $ref_topic ? $user->lang['SUBTITLE_REPLY'] : $user->lang['SUBTITLE_NEW'],
			'REF_TOPIC'         => $ref_topic_title_no_prefix,
			'S_HIDDEN_FIELDS'   => $ref_topic ? '<input type="hidden" name="ref" value="' . $ref_topic->topic_id . '"/>' : '',
		));

		return $this->helper->render('kontakt_body.html', $user->lang['KONTAKT_TITLE']);
	}


}
