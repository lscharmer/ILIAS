import il from 'il';
import bus from './bus.js';

const Void = () => {};

const load = key => new Promise(r => bus.onArrived(key, ([n, open, close]) => {
  let next = Void;
  n.querySelector('form').addEventListener('submit', e => {
    e.preventDefault();
    next(true);
    next = Void;
    close();
    return false;
  });
  r(() => {
    open();
    return new Promise(r => {
      next = r;
    });
  });
}));

const cached = proc => {
  const cache = {};
  return key => {
    if(!cache[key])
    {
      cache[key] = proc(key);
    }

    return cache[key];
  };
};

const cachedLoad = cached(load);

/**
 * @param {Function(string): string} label
 * @return {Function(string, string, undefined|string): Promise<bool>}
 */
export default translate => key => {
  return cachedLoad(key).then(f => f());
  // return new Promise(function(resolve){
  //   const body = document.createElement('div');
  //   body.textContent = message;
  //   body.className = 'alert alert-warning';
  //   const header = document.createElement('div');
  //   header.classList.add('modal-title');
  //   header.textContent = label;

  //   const modal = il.Modal.dialogue({
  //     body: body,
  //     header: header,
  //     buttons: [
  //       {
  //         type: 'button',
  //         label: buttonLabel || label,
  //         callback: function(){
  //           resolve(true);
  //           resolve = function(){};
  //           modal.hide();
  //         }
  //       },
  //       {type: 'button', label: translate('cancel'), callback: function(){modal.hide();}},
  //     ],
  //     onHide: function(){
  //       resolve(false);
  //     }
  //   });
  // });
};
