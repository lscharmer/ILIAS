const DEBOUNCE_TIMOUT = 20;

export default class ProfileImageLoader {
  #url;
  #fallbackImage;
  #loaded;
  #queue;
  #reset;

  constructor (url, fallbackImage) {
    this.#url = url;
    this.#fallbackImage = fallbackImage;
    this.#loaded = {};
    this.#queue = {};
    this.#reset = () => {};
  }

  imageOfUser (userId) {
    if (this.#loaded[this.#key(userId)]) {
      return Promise.resolve(this.#loaded[this.#key(userId)]);
    }
    return this.#debounceLoad(userId);
  }

  imagesOfUsers (userIds) {
    return Promise.all(userIds.map(this.imageOfUser.bind(this)));
  }

  defaultImage () {
    return this.#fallbackImage;
  }

  #debounceLoad (userId) {
    return new Promise((resolve, reject) => {
      const key = this.#key(userId);
      this.#queue[key] = this.#queue[key] || {value: userId, waiting: []};
      this.#queue[key].waiting.push({resolve, reject});
      this.#reset();
      this.#reset = clearTimeout.bind(null, setTimeout(this.#request.bind(this), DEBOUNCE_TIMOUT));
    });
  }

  #request () {
    const profiles = Object.values(this.#queue).map(({value}) => value);
    const response = fetch(this.#url, {method: 'POST', body: JSON.stringify({profiles}), headers: {'Content-Type': 'application/json'}}).then(r => r.json());

    response.then(response => Object.entries(this.#flushQueue()).forEach(([key, {waiting}]) => waiting.forEach(
      ({resolve, reject}) => {
        if (response[key]) {
          this.#loaded[key] = response[key];
          resolve(response[key]);
        } else {
          reject('Image not returned from server.');
        }
      }
    )));

    response.catch(error => Object.values(this.#flushQueue()).flatMap(v => v.waiting).forEach(p => p.reject(error)));
  }

  #key(userId) {
    return JSON.stringify(userId);
  }

  #userIdAsString(userId) {
    return typeof userId === 'object' ? JSON.stringify(userId) : userId;
  }

  #flushQueue() {
    const queue = this.#queue;
    this.#queue = {};
    return queue;
  }
}
