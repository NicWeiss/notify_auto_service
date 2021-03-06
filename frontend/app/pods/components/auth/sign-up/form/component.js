import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';



export default class LoginComponent extends Component {
  @service notify;
  @service session;
  @service request;

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
    if (field == 'user') { this.userSelected = true; }
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
      await this.request.toApi('auth/get_code',{'email': this.email})
    } catch (errorCode) {
      if (errorCode === 403) this.notify.error('Код уже был выслан');
      if (errorCode === 422) this.notify.error('Email уже занят или не корректен или');
      if (errorCode === 500) this.notify.error('Ошибка на сервере');
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
    
    let session = null;

    try {
      session = await this.request.toApi('auth/sign_up', {'user': this.user,
                                                   'email': this.email,
                                                   'password': this.password,
                                                   'code': this.code})
    } catch (errorCode) {
      if (errorCode === 403) {
        this.notify.error('Неверный код');
        this.code = null;
        this.isCanSignUp = false;
      }
      if (errorCode === 422) this.notify.error('Пользователь с таким email уже существует');
      if (errorCode === 500) this.notify.error('Ошибка на сервере');
      return
    }

    try {
      request = await this.session.authenticate('authenticator:custom', session, this.password);
    } catch (error) {
      this.errorMessage = error.error || error;
    }

    this.args.onSignUpDone();
  }
}
