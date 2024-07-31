const HEARTBEAT_TIMEOUT = 5000;

export class TypeSelf {
  #serverConnector;
  #isTyping;
  #reset;

  constructor (serverConnector) {
    this.#serverConnector = serverConnector;
    this.#isTyping = false;
    this.#reset = () => {};
    window.addEventListener('beforeunload',this.release.bind(this));
  }

  release () {
    this.#reset();
    if (this.#isTyping) {
      this.#serverConnector.userStoppedTyping();
      this.#isTyping = false;
    }
  };

  heartbeat () {
    this.#reset();
    if (!this.#isTyping) {
      this.#serverConnector.userStartedTyping();
      this.#isTyping = true;
    }
    this.#reset = clearTimeout.bind(null, setTimeout(this.release.bind(this), HEARTBEAT_TIMEOUT));
  };
}

export class TypeNothing {
  release () {}
  heartbeat () {}
}
