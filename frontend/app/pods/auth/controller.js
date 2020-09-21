import Controller from '@ember/controller';
import { action } from '@ember/object';

export default class AuthController extends Controller {

  @action
  onLoginDone() {
    console.log('to manage');
    this.transitionToRoute('manage');
  }
}