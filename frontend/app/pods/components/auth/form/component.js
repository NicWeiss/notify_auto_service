import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';



export default class AuthComponent extends Component {
  @service notify;
  @service auth;

  @tracked userSelected = false;
  @tracked passwordSelected = false;
  @tracked user = null;
  @tracked password = null;
  @tracked isRemember = false;

  @action
  onStartEdit(field) {
    if (field == 'user') {
      this.userSelected = true;
      this.passwordSelected = false;
    } else {
      this.userSelected = false;
      this.passwordSelected = true;
    }
  }

  @action
  onEndEdit(field) {
    if (field == 'user') {
      this.userSelected = false;
    } else {
      this.passwordSelected = false;
    }
  }

  @action
  onChaangeRemember() {
    if (this.isRemember) {
      this.isRemember = false;
    } else {
      this.isRemember = true;
    }
  }

  @action
  onSubmit() {
    this.auth.logout();
    if (!this.user || !this.password) {
      this.notify.error('Пользователь или пароль неверны');
      return;
    }
    // запрос к серверу, если успех - сохраняем логин и сессию, делаем редирект на список нотификаций
  
    this.auth.setSesion(this.user , '@#%@GF$HY%$&U$%H%$U');
    this.args.onLoginDone();
  }
}
