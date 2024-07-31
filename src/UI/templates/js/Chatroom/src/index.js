import il from 'il';
import ChatMessageArea from './ChatMessageArea';
import ChatUsers from './ChatUsers';
import ProfileImageLoader from './ProfileImageLoader';
import bindSendMessageBox from './bindSendMessageBox';
import ServerConnector from './ServerConnector';
import WatchList from './WatchList';
import { TypeSelf, TypeNothing } from './Type';
import bus, { createBus } from './bus';
import { expandableTextarea } from './expandableTextarea';
import createConfirmation from './createConfirmation';
import ILIASConnector from './ILIASConnector';
import Logger from './Logger';
import inviteUserToRoom from './inviteUserToRoom';
import sendFromURL from './sendFromURL';
import run, { runReadOnly } from './run';

il.Chatroom = {
  run,
  runReadOnly,
  bus,
  expandableTextarea,
};
