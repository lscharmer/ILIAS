export function createBus()
{
  const sent = {};
  const waiting = {};

  return {
    send (name, value) {
      if (sent[name]) {
        throw new Error('Name already provided.');
      }
      sent[name] = value;
      const callMe = (waiting[name] || []);
      delete waiting[name];
      callMe.forEach(proc => proc(value));
    },
    onArrived (name, proc) {
      if (sent[name]) {
        proc(sent[name]);
        return;
      }
      waiting[name] = waiting[name] || [];
      waiting[name].push(proc);
    },
  };
}

export default createBus();
