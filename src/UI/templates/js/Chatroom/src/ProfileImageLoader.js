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
    if (this.#loaded[userId]) {
      return Promise.resolve(this.#loaded[userId]);
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
      this.#queue[userId] = this.#queue[userId] || [];
      this.#queue[userId].push({resolve, reject});
      this.#reset();
      this.#reset = clearTimeout.bind(null, setTimeout(this.#request.bind(this), DEBOUNCE_TIMOUT));
    });
  }

  #request () {
    fetch(this.#url + '&usr_ids=' + Object.keys(this.#queue).join(',')).then(r => r.json()).then(response => {
      const queue = this.#queue;
      this.#queue = {};
      Object.entries(response).map(([id, item]) => {
        this.#loaded[id] = item.profile_image;
        return id;
      }).forEach(id => {
        queue[id].forEach(({resolve}) => resolve(this.#loaded[id]));
      });
    }).catch(error => {
      const queue = this.#queue;
      this.#queue = {};
      Object.values(queue).flatMap(v => v).forEach(({reject}) => reject(error));
    });
  }
}
