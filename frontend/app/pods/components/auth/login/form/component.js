import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';
import { detectClientData } from 'frontend/helpers/detectClientData';



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
    const client = detectClientData();

    try {
      responce = await this.api.post({
        'url': 'auth/login', 'data': {
          'email': this.email,
          'password': this.password,
          'client': client,
        }
      })
    } catch (error) {
      if (error.status === 403) this.notify.error('Неправильный email или пароль');
      if (error.status === 500) this.notify.error('Ошибка на сервере');
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
