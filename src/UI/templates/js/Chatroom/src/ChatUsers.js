/**
 * This class renders all available action for a user in a chat room.
 */
export default class ChatUsers {
  #anchor;
  #template;
  #profileImageLoader;
  #emptyMessage;
  #users;
  #visibleUsers;
  #userActions;

  /**
   * @param {NodeElement} selector
   * @param {string} template
   * @param {ProfileImageLoader} profileImageLoader
   */
  constructor (anchor, template, profileImageLoader, userActions) {
    this.#anchor = anchor;
    this.#template = template;
    this.#profileImageLoader = profileImageLoader;
    this.#emptyMessage = anchor && anchor.querySelector('.no_users');
    this.#users = {};
    this.#visibleUsers = [];
    this.#userActions = userActions;
  }

  userListChanged (diff) {
    diff.removed.forEach(({key}) => this.remove(key));
    diff.added.forEach(({value}) => this.add(value));
  }

  add (user) {
    if (this.#users[user.id]) {
      return false;
    }

    const node = this.#buildUserEntry(user);
    if (!user.hide) {
      this.#visibleUsers.push(String(user.id));
    }
    this.#anchor.appendChild(node);
    this.#users[user.id] = node;
    this.#preventEmpty();

    return true;
  }

  remove (id) {
    const node = this.#users[id];
    if (!node) {
      return false;
    }

    node.remove();
    this.#visibleUsers = this.#visibleUsers.filter(x => x !== id);
    delete this.#users[id];
    this.#preventEmpty();

    return true;
  }

  setUsers (users) {
    const ids = users.map(u => String(u.id));
    Object.keys(this.#users)
      .filter(id => !ids.includes(id))
      .forEach(this.remove.bind(this));
    users.forEach(this.add.bind(this));
  }

  static actionList (txt, connector, confirmModal, startConversation) {
    return [
      {
        name: 'kick',
        label: txt('kick'),
        callback: function (userId) {
          confirmModal('kick-modal').then(function(confirmed){ // txt('kick'), txt('kick_question')
	    if (confirmed) {
	      connector.kick(userId);
	    }
          });
        },
      },
      {
        name: 'ban',
        label: txt('ban'),
        callback: function (userId) {
          confirmModal('ban-modal').then(function(confirmed){ // txt('ban'), txt('ban_question')
	    if (confirmed) {
              connector.ban(userId);
	    }
          });
        },
      },
      {
        name: 'chat',
        label: txt('start_private_chat'),
        callback: startConversation,
      }
    ];
  }

  #buildUserEntry (user) {
    // const html = this.#template
    //       .replace(/\[\[USERNAME\]\]/g, user.label)
    //       .replace(/\[\[INDEX\]\]/g, `${user.type}_${user.id}`);

    const node = new DOMParser().parseFromString(this.#template, 'text/html').body.firstChild;
    const username = node.querySelector('[data-placeholder=USERNAME]');
    const img = node.querySelector('img');
    username.parentNode.replaceChild(document.createTextNode(user.label), username);

    img.setAttribute('src', this.#profileImageLoader.defaultImage());
    this.#profileImageLoader.imageOfUser(user.id).then(img.setAttribute.bind(img, 'src'));

    // node.classList.add(`${user.type}_${user.id}`);
    // node.classList.add('online_user');
    if (user.hide) {
      node.classList.add('ilNoDisplay');
    }

    addUserActionMenu(this.#userActions, user.id, node);

    return node;
  }

  #preventEmpty () {
    this.#emptyMessage.classList[this.#visibleUsers.length ? 'add' : 'remove']('ilNoDisplay');
  }
}

const addUserActionMenu = (actions, userId, node) => {
  const button = node.querySelector('.chatroom-user-action-dropdown');
  button.classList.remove('ilNoDisplay');

  const item = action => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    const span = document.createElement('span');

    li.appendChild(a);
    a.appendChild(span);
    span.textContent = action.label;

    a.addEventListener('click', () => action.callback(userId));

    return li;
  };

  const ul = node.querySelector('.ilChatroomDropdownOptions');
  actions.forEach(a => ul.appendChild(item(a)));
};
