import $ from 'jquery';
import il from 'il';

export default ({closeModal, showModal, node}, translation, iliasConnector, userList, send) => {
  const input = node.querySelector('input[type=text]');
  let value = null;
  showModal();
  node.querySelector('form').addEventListener('submit', e => {
    e.preventDefault();
    if(value !== null)
    {
      iliasConnector.inviteToPrivateRoom(value, 'byId');
      closeModal();
    }
    return false;
  });
  // const input = document.querySelector('#invite_user_text');
  const userResults = createUserResults(
    document.querySelector('#invite_users_loading'),
    document.querySelector('#invite_users_no_user')
  );
  const body = document.querySelector('#invite_users_container');
  input.value = '';
  // const modal = il.Modal.dialogue({
  //   header: translation('invite_users'),
  //   show: true,
  //   body: body,
  //   onShown: function(){
  //     body.classList.remove('ilNoDisplayChat');
  //   }
  // });

  /* Please note that an empty input will not trigger a search so the last message will not be removed on empty input. */
  $(input).autocomplete({
    appendTo: input.parentNode,
    source: (request, response) => searchForUsers(userList, send, request.term).then(userResults.loaded(response)),
    search: function () {
      value = null;
      if (this.value.length > 2) {
        userResults.loading();
        return true;
      }
      userResults.noRequest();
      return false;
    },
    select: (event, ui) => {
      value = ui.item.id;
      // modal.hide();
      // closeModal();
    },
  });
}

function createUserResults(loading, noUser){
  let current = loading;
  loading.classList.add('ilNoDisplayChat');
  noUser.classList.add('ilNoDisplayChat');

  const hideCurrent = function(){
    current.classList.add('ilNoDisplayChat');
  };

  const change = function(newOne){
    hideCurrent();
    current = newOne;
    current.classList.remove('ilNoDisplayChat');
  };

  return {
    loaded: function(then){
      return function(items){
        items.length ? hideCurrent() : change(noUser);
        return then(items);
      };
    },
    loading: function(){
      change(loading);
      current = loading;
    },
    noRequest: hideCurrent,
  };
}

function searchForUsers(userList, send, search){
  const call = m => o => o[m]();
  return send('inviteUsersToPrivateRoom-getUserList', {q: search}).then(call('json')).then(response => {
    return response.items.filter(function(x){
      return !userList.has(x.id);
    });
  });
}
