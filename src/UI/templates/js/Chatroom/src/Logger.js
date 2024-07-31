export default class Logger {
  logServerResponse (message) {
    this.#log('Server-Response', message);
  };

  logServerRequest (message) {
    this.#log('Server-Request', message);
  }

  logILIASResponse (message) {
    this.#log('ILIAS-Response', message);
  }

  logILIASRequest (message) {
    this.#log('ILIAS-Request', message);
  }

  #log (type, message) {
    console.log(type, message);
  }
}
