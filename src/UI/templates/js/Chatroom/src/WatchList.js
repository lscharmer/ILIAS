
const diffLeft = (left, right) => Object.keys(left)
      .filter(key => !Reflect.has(right, key))
      .map(key => ({key, value: left[key]}));

export default class WatchList {
  #list;
  #onChangeList;

  constructor()
  {
    this.#list = {};
    this.#onChangeList = [];
  }

  find (key) {
    return this.#list[key];
  }

  has (key) {
    return Reflect.has(this.#list, String(key));
  }

  onChange (callback) {
    this.#onChangeList.push(callback);
  }

  add (key, value) {
    key = String(key);
    this.#list[key] = value;
    this.#changed({added: [{key, value}], removed: []});
  }

  remove (key) {
    key = String(key);
    if (!Reflect.has(this.#list, key)) {
      return;
    }
    const value = this.#list[key];
    delete this.#list[key];
    this.#changed({added: [], removed: [{key, value}]});
  }

  setAll (list) {
    const diff = {
      added: diffLeft(list, this.#list),
      removed: diffLeft(this.#list, list),
    };
    this.#list = list;
    this.#changed(diff);
  }

  all () {
    return this.#list;
  }

  #changed (diff) {
    this.#onChangeList.forEach(f => f(diff));
  }
}
