<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\UI\Component\Component;
use ILIAS\Chatroom\BuildChat;

/**
 * Class ilChatroomViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewGUI extends ilChatroomGUIHandler
{
    public function joinWithCustomName(): void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $failure = true;
        $username = '';
        $custom_username = false;

        if ($this->hasRequestValue('custom_username_radio')) {
            if (
                $this->hasRequestValue('custom_username_text') &&
                $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string()) === 'custom_username'
            ) {
                $custom_username = true;
                $username = $this->getRequestValue('custom_username_text', $this->refinery->kindlyTo()->string());
                $failure = false;
            } elseif (
                method_exists(
                    $chat_user,
                    'build' . $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string())
                )
            ) {
                $username = $chat_user->{
                    'build' . $this->getRequestValue('custom_username_radio', $this->refinery->kindlyTo()->string())
                }();
                $failure = false;
            }
        }

        if (!$failure && trim($username) !== '') {
            if (!$room->isSubscribed($chat_user->getUserId())) {
                $chat_user->setUsername($chat_user->buildUniqueUsername($username));
                $chat_user->setProfilePictureVisible(!$custom_username);
            }

            $this->showRoom($room, $chat_user);
        } else {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('no_username_given'));
            $this->showNameSelection($chat_user);
        }
    }

    /**
     * Adds CSS and JavaScript files that should be included in the header.
     */
    private function setupTemplate(): void
    {
        $this->mainTpl->addJavaScript('Modules/Chatroom/chat/node_modules/socket.io-client/dist/socket.io.js');
        $this->mainTpl->addJavaScript('./src/UI/templates/js/Chatroom/dist/Chatroom.min.js');
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/chat.js');
        $this->mainTpl->addJavaScript('Modules/Chatroom/js/iliaschat.jquery.js');
        $this->mainTpl->addJavaScript('node_modules/jquery-outside-events/jquery.ba-outside-events.js');
        $this->mainTpl->addJavaScript('./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js');

        $this->mainTpl->addCss('Modules/Chatroom/templates/default/style.css');

        $this->mainTpl->setPermanentLink($this->gui->getObject()->getType(), $this->gui->getObject()->getRefId());
    }

    /**
     * Prepares and displays chatroom and connects user to it.
     */
    private function showRoom(ilChatroom $room, ilChatroomUser $chat_user): void
    {
        $this->redirectIfNoPermission('read');

        $user_id = $chat_user->getUserId();

        $ref_id = $this->getRequestValue('ref_id', $this->refinery->kindlyTo()->int());
        $this->navigationHistory->addItem(
            $ref_id,
            $this->ilCtrl->getLinkTargetByClass(ilRepositoryGUI::class, 'view'),
            'chtr'
        );

        if ($room->isUserBanned($user_id)) {
            $this->cancelJoin($this->ilLng->txt('banned'));
            return;
        }

        $scope = $room->getRoomId();
        $connector = $this->gui->getConnector();
        $response = $connector->connect($scope, $user_id);

        if (!$response) {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'info');
        }

        if (!$room->isSubscribed($chat_user->getUserId())) {
            $room->connectUser($chat_user);
        }

        $response = $connector->sendEnterPrivateRoom($scope, $user_id);
        if (!$response) {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('unable_to_connect'), true);
            $this->ilCtrl->redirectByClass('ilinfoscreengui', 'info');
        }

        $messages = $room->getSetting('display_past_msgs') ? array_reverse(array_filter(
            $room->getLastMessages($room->getSetting('display_past_msgs'), $chat_user),
            fn($entry) => $entry->type !== 'notice'
        )) : [];

        $is_moderator = ilChatroom::checkUserPermissions('moderate', $ref_id, false);
        $show_auto_messages = !$this->ilUser->getPref('chat_hide_automsg_' . $room->getRoomId());

        $build = $this->buildChat($room, $connector->getSettings());

        $room_tpl = $build->template(false, $build->initialData(
            $room->getConnectedUsers(),
            $show_auto_messages,
            $this->ilCtrl->getLinkTarget($this->gui, 'view-lostConnection', '', false),
            [
                'moderator' => $is_moderator,
                'id' => $chat_user->getUserId(),
                'login' => $chat_user->getUsername(),
                'broadcast_typing' => $chat_user->enabledBroadcastTyping(),
                'profile_picture_visible' => $chat_user->isProfilePictureVisible(),
            ],
            $messages
        ), $this->panel($this->ilLng->txt('write_message'), $this->sendMessageForm()), $this->panel($this->ilLng->txt('messages'), $this->legacy('<div id="chat_messages"></div>')));

        // ilModalGUI::initJS();

        $this->mainTpl->setContent($room_tpl->get());
        $this->mainTpl->setRightContent($this->userList() . $this->chatFunctions($show_auto_messages, $is_moderator));
    }

    public function readOnlyChatWindow(ilChatroom $room, array $messages): ilTemplate
    {
        $build = $this->buildChat($room, $this->gui->getConnector()->getSettings());

        return $build->template(true, $build->initialData([], true, null, [
            'moderator' => false,
            'id' => -1,
            'login' => null,
            'broadcast_typing' => false,
        ], $messages), $this->panel($this->ilLng->txt('messages'), $this->legacy('<div id="chat_messages"></div>')), '');
    }

    private function sendMessageForm(): Component
    {
        $template = new ilTemplate('tpl.chatroom_send_message_form.html', true, true, 'Modules/Chatroom');
        $this->renderSendMessageBox($template);

        return $this->legacy($template->get());
    }

    private function userList(): string
    {
        $roomRightTpl = new ilTemplate('tpl.chatroom_right.html', true, true, 'Modules/Chatroom');
        $this->renderRightUsersBlock($roomRightTpl);

        return $this->panel($this->ilLng->txt('users'), $this->legacy($roomRightTpl->get()));
    }

    private function chatFunctions(bool $show_auto_messages, bool $is_moderator): string
    {
        $txt = $this->ilLng->txt(...);
        $js_escape = json_encode(...);
        $format = fn($format, ...$args) => sprintf($format, ...array_map($js_escape, $args));
        $register = fn($name, $c) => $c->withOnLoadCode(fn($id) => $format(
            'il.Chatroom.bus.send(%s, document.getElementById(%s));',
            $name,
            $id
        ));

        $b = $this->uiFactory->button();
        $toggle = fn($label, $enabled) => $b->toggle($label, '#', '#', $enabled)->withAriaLabel($label);

        $bind = fn($key, $m) => $m->withAdditionalOnLoadCode(fn(string $id) => $format(
            '$(() => il.Chatroom.bus.send(%s, [document.getElementById(%s), () => $(document).trigger(%s, {}), () => $(document).trigger(%s, {})]));',
            $key,
            $id,
            $m->getShowSignal()->getId(),
            $m->getCloseSignal()->getId()
        ));

        $interrupt = fn($key, $label, $text, $button = null) => $bind($key, $this->uiFactory->modal()->interruptive(
            $label,
            $text,
            ''
        ))->withActionButtonLabel($button ?? $label);

        $auto_scroll = $register('auto-scroll-toggle', $toggle($txt('auto_scroll'), true));
        $messages = $register('system-messages-toggle', $toggle($txt('chat_show_auto_messages'), $show_auto_messages));

        $invite = $bind('invite-modal', $this->uiFactory->modal()->roundtrip($txt('chat_invite'), $this->legacy($txt('invite_to_private_room')), [
            $this->uiFactory->input()->field()->text($txt('chat_invite')),
        ])->withSubmitLabel($txt('chat_invite')));

        $buttons = [];
        $buttons[] = $register('invite-button', $b->shy($txt('invite_to_private_room'), ''));
        if ($is_moderator) {
            $buttons[] = $register('clear-history-button', $b->shy($txt('clear_room_history'), ''));
        }

        return $this->panel($txt('chat_functions'), [
            $this->legacy('<div id="chat_function_list">'),
            ...$buttons,
            $invite,
            $interrupt('kick-modal', $txt('chat_kick'), $txt('kick_question')),
            $interrupt('ban-modal', $txt('chat_ban'), $txt('ban_question')),
            $interrupt('clear-history-modal', $txt('clear_room_history'), $txt('clear_room_history_question')),
            $this->legacy('</div>'),
            $this->legacy(sprintf('<div>%s%s</div>', $this->checkbox($auto_scroll), $this->checkbox($messages))),
        ]);
    }

    private function checkbox(Component $component): string
    {
        return sprintf('<div class="chatroom-centered-checkboxes">%s</div>', $this->uiRenderer->render($component));
    }

    private function legacy(string $html): Component
    {
        return $this->uiFactory->legacy($html);
    }

    private function panel(string $title, $body): string
    {
        if (is_array($body)) {
            $body = $this->uiFactory->legacy(join('', array_map($this->uiRenderer->render(...), $body)));
        }
        // $panel = $this->uiFactory->panel()->standard($title, $body);
        $panel = $this->uiFactory->panel()->secondary()->legacy($title, $body);

        return $this->uiRenderer->render($panel);
    }

    public function toggleAutoMessageDisplayState(): void
    {
        $this->redirectIfNoPermission('read');

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        $state = 0;
        if ($this->http->wrapper()->post()->has('state')) {
            $state = $this->http->wrapper()->post()->retrieve('state', $this->refinery->kindlyTo()->int());
        }

        ilObjUser::_writePref(
            $this->ilUser->getId(),
            'chat_hide_automsg_' . $room->getRoomId(),
            (string) ((int) (!(bool) $state))
        );

        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                ->withBody(Streams::ofString(json_encode(['success' => true], JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Calls ilUtil::sendFailure method using given $message as parameter.
     */
    private function cancelJoin(string $message): void
    {
        $this->mainTpl->setOnScreenMessage('failure', $message);
    }

    protected function renderSendMessageBox(ilTemplate $roomTpl): void
    {
        $roomTpl->setVariable('PLACEHOLDER', $this->ilLng->txt('chat_osc_write_a_msg'));
        $roomTpl->setVariable('LBL_SEND', $this->ilLng->txt('send'));
    }

    protected function renderRightUsersBlock(ilTemplate $roomTpl): void
    {
        $roomTpl->setVariable('LBL_NO_FURTHER_USERS', $this->ilLng->txt('no_further_users'));
    }

    private function showNameSelection(ilChatroomUser $chat_user): void
    {
        $name_options = $chat_user->getChatNameSuggestions();
        $formFactory = new ilChatroomFormFactory();
        $selectionForm = $formFactory->getUserChatNameSelectionForm($name_options);

        $this->ilCtrl->saveParameter($this->gui, 'sub');

        $selectionForm->addCommandButton('view-joinWithCustomName', $this->ilLng->txt('enter'));
        $selectionForm->setFormAction(
            $this->ilCtrl->getFormAction($this->gui, 'view-joinWithCustomName')
        );

        $this->mainTpl->setVariable('ADM_CONTENT', $selectionForm->getHTML());
    }

    /**
     * Chatroom and Chatuser get prepared before $this->showRoom method
     * is called. If custom usernames are allowed, $this->showNameSelection
     * method is called if user isn't already registered in the Chatroom.
     * @inheritDoc
     */
    public function executeDefault(string $requestedMethod): void
    {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();
        $this->setupTemplate();

        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled', '0')) {
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        if (!$room->getSetting('allow_anonymous') && $this->ilUser->isAnonymous()) {
            $this->cancelJoin($this->ilLng->txt('chat_anonymous_not_allowed'));
            return;
        }

        $chat_user = new ilChatroomUser($this->ilUser, $room);

        if ($room->getSetting('allow_custom_usernames')) {
            if ($room->isSubscribed($chat_user->getUserId())) {
                $chat_user->setUsername($chat_user->getUsername());
                $this->showRoom($room, $chat_user);
            } else {
                $this->showNameSelection($chat_user);
            }
        } else {
            $chat_user->setUsername($this->ilUser->getLogin());
            $this->showRoom($room, $chat_user);
        }
    }

    public function logout(): void
    {
        $pid = $this->tree->getParentId($this->gui->getRefId());
        $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $pid);
        $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
    }

    public function lostConnection(): void
    {
        if ($this->http->wrapper()->query()->has('msg')) {
            match ($this->http->wrapper()->query()->retrieve('msg', $this->refinery->kindlyTo()->string())) {
                'kicked' => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('kicked'), true),
                'banned' => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('banned'), true),
                default => $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true),
            };
        } else {
            $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('lost_connection'), true);
        }

        $this->ilCtrl->redirectByClass(ilInfoScreenGUI::class, 'info');
    }

    public function getUserProfileImages(): void
    {
        global $DIC;

        $response = [];

        $request = json_decode($DIC->http()->request()->getBody()->getContents(), true);

        ilWACSignedPath::setTokenMaxLifetimeInSeconds(30);

        $profiles = $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->byTrying([
            $DIC->refinery()->kindlyTo()->recordOf([
                'id' => $DIC->refinery()->kindlyTo()->int(),
                'username' => $DIC->refinery()->kindlyTo()->string(),
                'profile_picture_visible' => $DIC->refinery()->kindlyTo()->bool(),
            ]),
            $DIC->refinery()->kindlyTo()->recordOf([
                'id' => $DIC->refinery()->kindlyTo()->int(),
                'username' => $DIC->refinery()->kindlyTo()->string(),
            ]),
        ]))->transform($request['profiles'] ?? []);

        $user_ids = array_column($profiles, 'id');

        $public_data = ilUserUtil::getNamePresentation($user_ids, true, false, '', false, true, false, true);

        foreach ($profiles as $profile) {
            if ($profile['profile_picture_visible'] ?? false) {
                $public_image = $public_data[$profile['id']]['img'] ?? '';
            } else {
                /** @var ilUserAvatar $avatar */
                $avatar = $DIC["user.avatar.factory"]->avatar('xsmall');
                $avatar->setUsrId(ANONYMOUS_USER_ID);
                $avatar->setName(ilStr::subStr($profile['username'], 0, 2));
                $public_image = $avatar->getUrl();
            }

            $response[json_encode($profile)] = $public_image;
        }

        $this->sendResponse($response);
    }

    public function test()
    {
        $m = $this->uiFactory->modal()->roundtrip('Somsosmsomsom', null, [
            'som' => $this->uiFactory->input()->field()->text('aja'),
        ]);

        $m = $m->withAdditionalOnLoadCode(function($id){
            return '';
        });

        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html')
                ->withBody(Streams::ofString($this->uiRenderer->renderAsync($m)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    private function buildChat(ilChatroom $room, ilChatroomServerSettings $settings): BuildChat
    {
        return new BuildChat($this->ilCtrl, $this->ilLng, $this->gui, $room, $settings);
    }
}
