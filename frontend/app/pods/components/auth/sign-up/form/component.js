import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';
import { detectClientData } from 'frontend/helpers/detectClientData';


export default class LoginComponent extends Component {
  @service notify;
  @service session;
  @service api;

  @tracked isCanSignUp = false

  @tracked userSelected = false;
  @tracked passwordSelected = false;
  @tracked emailSelected = false;
  @tracked codedSelected = false;


  @tracked user = null;
  @tracked password = null;
  @tracked email = null;
  @tracked code = null;

  @action
  onStartEdit(field) {
    this.deselect()
    if (field == 'name') { this.userSelected = true; }
    if (field == 'password') { this.passwordSelected = true; }
    if (field == 'email') { this.emailSelected = true; }
    if (field == 'code') { this.codedSelected = true; }
  }

  @action
  deselect() {
    this.userSelected = false;
    this.passwordSelected = false;
    this.emailSelected = false;
    this.codedSelected = false;
  }

  @action
  codeCange(value) {
    this.isCanSignUp = value ? true : false;
  }

  @action
  async onGetCode() {
    if (!this.user || !this.password || !this.email) {
      this.notify.error('Не все данные заполнены ');
      return;
    }
    try {
      await this.api.post({ 'url': 'auth/get_code', 'data': { 'email': this.email } })
    } catch (error) {
      this.notify.error(error.data.detail);

      return;
    }
    this.notify.info('Код подтверждения отправлен');
  }


  @action
  async onSubmit() {
    if (!this.user || !this.password || !this.email) {
      this.notify.error('Не все данные заполнены ');
      return;
    }

    let response = null;
    const client = detectClientData();

    try {
      response = await this.api.post({
        'url': 'auth/sign_up',
        'data': {
          'name': this.user,
          'email': this.email,
          'password': this.password,
          'code': this.code,
          'client': client
        }
      })
    } catch (error) {
      if (error.status === 500) {
        this.notify.error('Ошибка на сервере');
        return
      }

      this.notify.error(error.data.detail);
      return
    }

    try {
      request = await this.session.authenticate('authenticator:custom', response.session, this.password);
    } catch (error) {
      this.errorMessage = error.error || error;
    }

    this.args.onSignUpDone();
  }
}
