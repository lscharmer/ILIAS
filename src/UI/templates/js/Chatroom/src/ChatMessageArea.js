import il from 'il';
import { formatISODate, formatISOTime } from './formatTime';

export default class ChatMessageArea {
  #anchor;
  #currentUserid;
  #config;
  #profileImageLoader;
  #typingList;
  #txt;
  #lastUser;
  #lastDate;
  #pane;
  #typingInfo;
  #touch;

  constructor (anchor, currentUserid, config, profileImageLoader, typingList, txt) {
    this.#anchor = anchor;
    this.#currentUserid = currentUserid;
    this.#config = config; // {scrolling: bool, show_auto_msg: bool}
    this.#profileImageLoader = profileImageLoader;
    this.#typingList = typingList;
    this.#txt = txt;
    this.#pane = createDiv(['messageContainer']);
    this.#pane.setAttribute('aria-live', 'polite');
    this.#typingInfo = createDiv(['typing-info']);
    this.#typingInfo.setAttribute('aria-live', 'polite');
    this.#touch = () => {};

    this.#syncConfig();
    this.clearMessages();
    this.#show();
  }

  addMessage (message) {
    this.#touch();
    const line = createDiv(['messageLine', 'chat', !message.target || message.target.public ? 'public' : 'private']);

    const fallback = () => {console.log(message.type);};
    let lastUser = null;
    const setUser = x => {lastUser = x;};

    const cases = {
      message: () => {
        const m = msg(timeInfo(message, formatISOTime(this.#txt)), actualMessage(message));
        if (this.#lastDate(new Date(message.timestamp))) {
          this.#pane.appendChild(separate(message, formatISODate(this.#txt)));
          setUser(null);
        }

        if (message.from.id === this.#currentUserid) {
          line.classList.add('myself');
        }

        if (this.#lastUser && this.#lastUser.id === message.from.id && this.#lastUser.username === message.from.username) {
          this.#lastUser.node.appendChild(m);
          setUser(this.#lastUser);
        } else {
          // const body = createDiv(['message-body']); // cont
          line.appendChild(messageHeader(message, this.#profileImageLoader, formatISOTime(this.#txt)));
          line.appendChild(m);
          // line.appendChild(body);
          this.#pane.appendChild(line);
          setUser({...message.from, node: line});
        }
      },
      connected: fallback,
      disconnected: fallback,
      private_room_entered: fallback,
      private_room_left: fallback,
      notice: () => {
        // return;
        // console.log(message.content);
        // const s = createDiv(['chat'], 'span');
        // s.innerHTML = message.content;
        // line.classList.add('notice');
        // line.appendChild(s);
        // this.#pane.appendChild(line);

        const n = createDiv(['separator', 'system-message']);
        const o = createDiv([], 'p');
        o.innerHTML = this.#txt(message.content, message.data);
        n.appendChild(o);
        this.#pane.appendChild(n);
      },
      error: fallback,
      userjustkicked: fallback,
    };

    (cases[message.type] || fallback)();

    this.#lastUser = lastUser;
    if (this.#config.scrolling) {
      this.#anchor.scrollTop = this.#pane.getBoundingClientRect().height;
    }
  }

  clearMessages () {
    this.#pane.innerHTML = '';
    this.#lastUser = null;
    this.#lastDate = remeberLastDate();

    const n = createDiv(['separator']);
    const o = createDiv([], 'p');
    o.innerHTML = this.#txt('welcome_to_chat');
    n.appendChild(o);
    this.#pane.appendChild(n);
    this.#touch = n.remove.bind(n);
  }

  typingListChanged () {
    const names = Object.values(this.#typingList.all());
    if (names.length === 0) {
      this.#typingInfo.textContent = '';
    } else if (names.length === 1) {
      this.#typingInfo.textContent = this.#txt("chat_user_x_is_typing", names[0]);
    } else {
      this.#typingInfo.textContent = this.#txt("chat_users_are_typing");
    }
  }

  enableAutoScroll (enable) {
    this.#config.scrolling = Boolean(enable);
    this.#syncConfig();
  }

  enableSystemMessages (enable) {
    this.#config.show_auto_msg = Boolean(enable);
    this.#syncConfig();
  }

  #show () {
    this.#anchor.appendChild(this.#pane);
    const fader = createDiv(['fader']);
    this.#anchor.appendChild(fader);
    fader.appendChild(this.#typingInfo);
  }

  #syncConfig () {
    this.#anchor.classList[this.#config.show_auto_msg ? 'remove' : 'add']('hide-system-messages');
  }
}

const remeberLastDate = () => {
  let last = null;
  return date => {
    const showMessage = !last
          || last.getDate() != date.getDate()
          || last.getMonth() != date.getMonth()
          || last.getFullYear() != date.getFullYear();
    last = date;
    return showMessage;
  };
};

function separate(message, formatTime)
{
  const n = createDiv(['separator']); // 'messageLine', 'chat', 'dateline'
  const o = createDiv([], 'p'); // span ['chat', 'content', 'date']
  o.textContent = formatTime(message.timestamp);
  n.appendChild(o);

  return n;
}

function messageHeader(message, profileImageLoader, formatTime)
{
  const dateFlag = createDiv(['user'], 'span');
  const userFlag = createDiv(['user'], 'span');
  const img = createDiv([], 'img');
  const header = createDiv(['message-header']); // oa

  dateFlag.textContent = formatTime(message.timestamp);
  userFlag.textContent = message.from.username;
  img.src = profileImageLoader.defaultImage();
  profileImageLoader.imageOfUser(message.from).then(Reflect.set.bind(null, img, 'src'));

  header.appendChild(img);
  header.appendChild(userFlag);
  header.appendChild(dateFlag);

  return header;
};

function actualMessage(message)
{
  const messageSpan = createDiv([], 'p');
  // const messageSpan = createDiv(['chat', 'content', 'message'], 'span');
  messageSpan.textContent = message.content;
  il.ExtLink.autolink(messageSpan);

  return messageSpan;
}

function msg(timeInfo, actualMessage)
{
  const node = createDiv(['message-body']);
  node.appendChild(timeInfo);
  node.appendChild(actualMessage);

  return node;
}

function timeInfo(message, formatTime)
{
  const info = createDiv(['time-info']);
  info.textContent = formatTime(message.timestamp);

  return info;
}

const createDiv = (classes, nodeType) => {
  const div = document.createElement(nodeType || 'div');
  (classes || []).forEach(name => div.classList.add(name));
  return div;
};
