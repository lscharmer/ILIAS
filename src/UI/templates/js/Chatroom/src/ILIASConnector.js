/**
 * This class connects the client to the related ILIAS environment. Communication is handled by sending
 * JSON requests. The response of each request is handled through callbacks delivered by the ILIASResponseHandler
 * which is passed through the constructor
 */
export default class ILIASConnector
{
  #send;
  #logger;

  /**
   * @param {string} postUrl
   * @param {Logger} logger
   */
  constructor(send, logger){
    this.#send = send;
    this.#logger = logger;
  }

  /**
   * Sends a heartbeat to ILIAS in a delivered interval. It is used to keep the session for an ILIAS user open.
   *
   * @param {number} interval
   */
  heartbeatInterval (interval) {
    const ignore = () => {};
    window.setInterval(() => this.#sendRequest('poll', {}, ignore), interval);
  }

  /**
   * Sends a request to ILIAS to leave a private room.
   */
  leavePrivateRoom () {
    this.#logger.logILIASRequest('leavePrivateRoom');
    this.#sendRequest('privateRoom-leave');
  }

  /**
   * Sends a request to ILIAS to invite a specific user to a private room.
   * The invitation can be done by two types
   *    1. byId
   *    2. byLogin
   *
   * @param {string} userValue
   * @param {string} invitationType
   */
  inviteToPrivateRoom (userValue, invitationType) {
    this.#sendRequest('inviteUsersToPrivateRoom-' + invitationType, {
      user: userValue
    });
  }

  /**
   * Sends a request to ILIAS to clear the chat history
   */
  clear () {
    this.#sendRequest('clear');
  }

  /**
   * Sends a request to ILIAS to kick a user from a specific room. The room can either be a private or the main room.
   *
   * @param {number} userId
   */
  kick (userId) {
    this.#sendRequest('kick', {user: userId});
  };

  /**
   * Sends a request to ILIAS to ban a user from a specific room. The room can either be a private or the main room.
   *
   * @param {number} userId
   */
  ban (userId) {
    this.#sendRequest('ban-active', {user: userId});
  };

  /**
   * Sends a asynchronously JSON request to ILIAS.
   *
   * @param {string} action
   * @param {{}} params
   * @param {function} responseCallback
   */
  #sendRequest (action, params = {}, responseCallback = r => this.#gotResponse(r)) {
    this.#send(action, params).then(r => r.json()).then(responseCallback);
  }

  #gotResponse (response) {
    this.#logger.logILIASResponse('default');
    if (!response.success) {
      alert(response.reason);
      return false;
    }
    return true;
  }
}

/**
 * Generates request parameter string for an asynchronous request.
 *
 * @param {Array} params
 * @returns {string}
 */
function generateParamsString(params) {
  let string = '';
  for (let key in params) {
    string += '&' + key + '=' + encodeURIComponent(params[key]);
  }
  return string;
}

function getAsObject(data) {
  if (typeof data == 'object') {
    return data;
  }
  try {
    return JSON.parse(data);
  } catch (e) {
    if (typeof console != 'undefined') {
      console.log(e);
    }
    return {success: false};
  }
};
