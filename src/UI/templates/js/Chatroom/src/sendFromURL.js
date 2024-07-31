export default url => (action, getParameters = {}) => {
  const target = new URL(url.replace(/postMessage/, action));
  Object.entries(getParameters).forEach(kv => target.searchParams.set(...kv));

  return fetch(target);
}
