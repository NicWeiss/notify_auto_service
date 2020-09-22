import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';



export default class LoginComponent extends Component {
  @service notify;
  @service session;

  @tracked userSelected = false;
  @tracked passwordSelected = false;
  @tracked user = null;
  @tracked password = null;

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
  async onSubmit() {
    if (!this.user || !this.password) {
      this.notify.error('Пользователь или пароль неверны');
      return;
    }
    // запрос к серверу, если успех - сохраняем логин и сессию, делаем редирект на список нотификаций
  
    const identification = "wew43r34g#$G3$Y#$@R@F@#";
    const password = "44959";
    let request = null;
    try {
      request = await this.session.authenticate('authenticator:custom', identification, password);
    } catch(error) {
      this.errorMessage = error.error || error;
    }

    this.args.onLoginDone();
  }
}
