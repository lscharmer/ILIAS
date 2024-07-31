import { expandableTextareaFromNodes } from './expandableTextarea.js';

export default (anchor, sendMessage, typing) => {
  const textarea = anchor.querySelector('#submit_message_text');
  const button = anchor.querySelector('#submit_message');
  button.addEventListener('click', e => {
    e.preventDefault();
    e.stopPropagation();
    send();
  });

  textarea.addEventListener(
    'input',
    expandableTextareaFromNodes(anchor.querySelector('#chat-shadow'), textarea, 3)
  );

  textarea.addEventListener('keydown', e => {
    const keycode = e.keyCode || e.which;

    if (keycode === 13 && !e.shiftKey) {
      e.preventDefault();
      e.stopPropagation();

      textarea.blur();
      send();
    }
  });

  textarea.addEventListener('keyup', e => {
    typing[(e.keyCode || e.which) === 13 ? 'release' : 'heartbeat']();
  });

  function send () {
    const content = textarea.value;

    if (content.trim() !== '') {
      const message = {
        content,
        format: {}
      };

      textarea.value = '';
      typing.release();
      sendMessage(message);
      textarea.focus();
    }
  }
};
