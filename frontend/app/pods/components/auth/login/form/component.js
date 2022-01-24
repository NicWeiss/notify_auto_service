import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';



export default class LoginComponent extends Component {
  @service notify;
  @service api;
  @service session;

  @tracked emailSelected = false;
  @tracked passwordSelected = false;

  @tracked email = null;
  @tracked password = null;

  @action
  onStartEdit(field) {
    this.deselect()
    if (field == 'password') { this.passwordSelected = true; }
    if (field == 'email') { this.emailSelected = true; }
  }

  @action
  deselect() {
    this.passwordSelected = false;
    this.emailSelected = false;
  }


  @action
  async onSubmit() {
    if (!this.email || !this.password) {
      this.notify.error('Email или пароль не заполнены');
      return;
    }
    let responce = null

    try {
      responce = await this.api.post({ 'url': 'auth/login', 'data': { 'email': this.email, 'password': this.password } })
    } catch (errorCode) {
      console.log(errorCode);
      if (errorCode === 403) this.notify.error('Неправильный email или пароль');
      if (errorCode === 500) this.notify.error('Ошибка на сервере');
      return;
    }

    try {
      request = await this.session.authenticate('authenticator:custom', responce.session, this.password);
    } catch (error) {
      this.errorMessage = error.error || error;
    }

    this.args.onLoginDone();
  }
}
