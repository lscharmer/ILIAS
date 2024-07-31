export default (lang, fallback = k => '#'+k+'#') => (key, obj, ...rest) => {
    let ret = lang[key];
    if(!ret) {
      return fallback(key, obj, ...rest);
    }
    for (const key in (obj || {})) {
      ret = ret.split('#' + key + '#').join(obj[key]);
    }
    return ret;
  };
