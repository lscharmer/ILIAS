import il from 'il';
import io from 'io';
import sendFromURL from './sendFromURL';
import ILIASConnector from './ILIASConnector';
import createConfirmation from './createConfirmation';
import ProfileImageLoader from './ProfileImageLoader';
import ChatUsers from './ChatUsers';
import WatchList from './WatchList';
import ChatMessageArea from './ChatMessageArea';
import ServerConnector from './ServerConnector';
import { TypeSelf, TypeNothing } from './Type';
import bindSendMessageBox from './bindSendMessageBox';
import bus from './bus';
import inviteUserToRoom from './inviteUserToRoom';
import Logger from './Logger';
import willTranslate from './willTranslate';
import { formatISODate, formatISOTime } from './formatTime';

const setup = options => {
  const userList = new WatchList();
  const typingList = new WatchList();
  const logger = new Logger();
  const send = sendFromURL(options.apiEndpointTemplate);
  const txt = willTranslate(options.lang, il.Language.txt.bind(il.Language));
  const confirmModal = createConfirmation(txt);
  const profileLoader = new ProfileImageLoader(options.initial.profile_image_url, options.initial.no_profile_image_url);
  const iliasConnector = new ILIASConnector(send, logger);
  const chatUsers = new ChatUsers(
    nodeById('chat_users'),
    (nodeById('ilChatUserRowTemplate') || {}).innerHTML,
    id => id === options.initial.userinfo.id,
    profileLoader,
    filterAllowedActions(
      ChatUsers.actionList(txt, iliasConnector, confirmModal, userId => startConversation(userList, userId)),
      options.initial
    )
  );
  const chatArea = new ChatMessageArea(nodeById('chat_messages'), options.initial.userinfo.id, options.initial.state, profileLoader, typingList, txt);
  const serverConnector = new ServerConnector(
    options.initial.userinfo,
    userList,
    chatArea,
    options.scope,
    typingList,
    options.initial.redirect_url,
    logger
  );

  // nodeById('submit_message_text').focus();

  return {
    bindEvents,
    processInitialData,
    connectToServer,
  };

  function bindEvents() {
    userList.onChange(chatUsers.userListChanged.bind(chatUsers));
    typingList.onChange(chatArea.typingListChanged.bind(chatArea));
    toggle('auto-scroll-toggle', on => chatArea.enableAutoScroll(on));
    toggle('system-messages-toggle', on => chatArea.enableSystemMessages(on));
    toggle('system-messages-toggle', on => saveShowSystemMessageState(on, options.initial));
    // click('invite-button', () => inviteUserToRoom(txt, iliasConnector, userList, send));
    bus.onArrived('invite-modal', ([node, showModal, closeModal]) => click('invite-button', () => {
      // bus.onArrived('kick-modal', ([node, showModal, closeModal]) => showModal());
      inviteUserToRoom({node, showModal, closeModal}, txt, iliasConnector, userList, send);
    }));
    ;

    click('clear-history-button', () => clearHistory(confirmModal, iliasConnector, txt));
    bindSendMessageBox(
      nodeById('send-message-group'),
      message => serverConnector.sendMessage(message),
      options.initial.userinfo.broadcast_typing ? new TypeSelf(serverConnector) : new TypeNothing()
    );
  }

  function processInitialData () {
    popuplateInitialUserList(userList, options.initial);
    populateInitialMessages(chatArea, options.initial);
  }

  function connectToServer () {
    iliasConnector.heartbeatInterval(120 * 1000);
    serverConnector.init(io.connect(options.baseUrl + '/' + options.instance, {path: options.initial.subdirectory}));
    serverConnector.onLoggedIn(function () {
      serverConnector.enterRoom(options.scope, 0);
    });
  }
};

export const runReadOnly = options => {
  const {processInitialData} = setup(options);
  processInitialData();
};

export default options => {
  const {bindEvents, processInitialData, connectToServer} = setup(options);

  bindEvents();
  processInitialData();
  connectToServer();
  nodeById('submit_message_text').focus();
};

const filterAllowedActions = (allActions, initial) => {
  const allowedActions = initial.userinfo.moderator ? ['kick', 'ban', 'chat'] : ['chat'];
  return allActions.filter(option => allowedActions.includes(option.name));
};

const startConversation = (userList, userId) => il.Chat.getConversation([il.OnScreenChat.user, userList.find(userId)]);

const saveShowSystemMessageState = (on, initial) => {
  fetch(initial.system_message_update_url, {method: 'POST', body: new URLSearchParams({state: Number(on)})});
};

const clearHistory = (confirmModal, iliasConnector, txt) => confirmModal('clear-history-modal').then(x => {
  if (x) {
    iliasConnector.clear();
  }
});

// txt('clear_room_history_question'),
// txt('delete')
// txt('clear_room_history')

const popuplateInitialUserList = (userList, initial) => userList.setAll(Object.fromEntries(initial.users.map(user => {
  const tmp = {
    id: user.id,
    username: user.login,
    profile_picture_visible: user.profile_picture_visible,
  };
  return [tmp.id, tmp];
})));

const populateInitialMessages = (chatArea, initial) => Object.values(initial.messages).forEach(message => {
  message.timestamp = message.timestamp * 1000;

  // if (message.type == 'notice') {
  //   if (message.content == 'connect' && message.data.id == personalUserInfo.id) {
  //     message.content = 'welcome_to_chat';
  //   }
  //   message.content = translation(message.content, message.data);
  // }
  chatArea.addMessage(message);
});

const click = (name, onClick) => {
  bus.onArrived(name, n => n.addEventListener('click', onClick));
};

const toggle = (name, onChange) => {
  click(name, function(e){
    onChange(this.classList.contains('on'));
  });
};

const nodeById = id => document.getElementById(id);
