<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';
include_once './Services/Mail/classes/class.ilMailOptions.php';

include_once 'Services/Mail/classes/class.ilMimeMail.php';
include_once 'Services/Mail/classes/class.ilMail.php';

/**
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id:$
 *
 * @ingroup ServicesMail
 */
class ilMailSummaryNotification extends ilMailNotification
{
    /**
     * @var \ilLanguage
     */
    protected $lng;
    
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * {@inheritdoc}
     */
    public function __construct($a_is_personal_workspace = false)
    {
        global $DIC;

        $this->db       = $DIC->database();
        $this->lng      = $DIC->language();
        $this->settings = $DIC->settings();

        parent::__construct($a_is_personal_workspace);
    }

    public function send()
    {
        $is_message_enabled = $this->settings->get("mail_notification_message");

        $res = $this->db->queryF(
            'SELECT mail.* FROM mail_options
						INNER JOIN mail ON mail.user_id = mail_options.user_id
						INNER JOIN mail_obj_data ON mail_obj_data.obj_id = mail.folder_id
						INNER JOIN usr_data ud ON ud.usr_id = mail.user_id
						WHERE mail_options.cronjob_notification = %s
						AND mail.send_time >= %s
						AND mail.m_status = %s
						AND ud.active = %s',
            array('integer', 'timestamp', 'text', 'integer'),
            array(1, date('Y-m-d H:i:s', time() - 60 * 60 * 24), 'unread', 1)
        );
        
        $users = array();
        $user_id = 0;

        while ($row = $this->db->fetchAssoc($res)) {
            if ($user_id == 0 || $row['user_id'] != $user_id) {
                $user_id = $row['user_id'];
            }
            $users[$user_id][] = $row;
        }

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];
        $sender        = $senderFactory->system();

        foreach ($users as $user_id => $mail_data) {
            $this->initLanguage($user_id);
            $user_lang = $this->getLanguage() ? $this->getLanguage() : $this->lng;

            $this->initMail();

            $this->setRecipients(array($user_id));
            $this->setSubject($this->getLanguageText('mail_notification_subject'));

            $this->setBody(ilMail::getSalutation($user_id, $user_lang));
            $this->appendBody("\n\n");
            if (count($mail_data) == 1) {
                $this->appendBody(sprintf($user_lang->txt('mail_at_the_ilias_installation'), count($mail_data), ilUtil::_getHttpPath()));
            } else {
                $this->appendBody(sprintf($user_lang->txt('mails_at_the_ilias_installation'), count($mail_data), ilUtil::_getHttpPath()));
            }
            $this->appendBody("\n\n");
            
            $counter = 1;
            foreach ($mail_data as $mail) {
                $this->appendBody("----------------------------------------------------------------------------------------------");
                $this->appendBody("\n\n");
                $this->appendBody('#' . $counter . "\n\n");
                $this->appendBody($user_lang->txt('date') . ": " . $mail['send_time']);
                $this->appendBody("\n");
                if ($mail['sender_id'] == ANONYMOUS_USER_ID) {
                    $senderName = ilMail::_getIliasMailerName();
                } else {
                    $senderName = ilObjUser::_lookupLogin($mail['sender_id']);
                }
                $this->appendBody($user_lang->txt('sender') . ": " . $senderName);
                $this->appendBody("\n");
                $this->appendBody($user_lang->txt('subject') . ": " . $mail['m_subject']);
                $this->appendBody("\n\n");

                if ($is_message_enabled == true) {
                    $this->appendBody($user_lang->txt('message') . ": " . $mail['m_message']);
                    $this->appendBody("\n\n");
                }
                ++$counter;
            }
            $this->appendBody("----------------------------------------------------------------------------------------------");
            $this->appendBody("\n\n");
            $this->appendBody($user_lang->txt('follow_link_to_read_mails') . " ");
            $this->appendBody("\n");
            $mailbox_link = ilUtil::_getHttpPath();
            $mailbox_link .= "/goto.php?target=mail&client_id=" . CLIENT_ID;

            $this->appendBody($mailbox_link);
            $this->appendBody("\n\n");
            $this->appendBody(ilMail::_getAutoGeneratedMessageString($this->getLanguage()));
            $this->appendBody(ilMail::_getInstallationSignature());

            $mmail = new ilMimeMail();
            $mmail->From($sender);

            $mailOptions = new \ilMailOptions($user_id);
            $mmail->To($mailOptions->getExternalEmailAddresses());

            $mmail->Subject($this->getSubject());
            $mmail->Body($this->getBody());
            $mmail->Send();
        }
    }
}
