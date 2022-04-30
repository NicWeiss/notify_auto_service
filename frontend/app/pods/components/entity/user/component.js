import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';


export default class UserComponent extends Component {
  @service api;
  @service notify;
  @service store;

  @tracked user = null;
  @tracked sessions = [];
  @tracked password = {};

  constructor(owner, args) {
    super(owner, args);

    this.getUser();
    this.getSessions();
  }

  async getUser() {
    let response = null;

    try {
      response = await this.api.get({ 'url': 'user' })
    } catch (error) {
      console.log(error);
      this.notify.error('Ошибка на сервере');
      return;
    }

    this.user = this.store.push({
      data: [{
        id: response.user.id,
        type: 'user',
        attributes: response.user,
        relationships: {}
      }]
    })[0];
  }

  async getSessions() {
    try {
      this.sessions = await this.store.findAll('session');
    } catch (error) {
      console.log(error);
    }
  }


  @action
  async changePassword() {
    console.log(this.password);
    if (this.password.newPass !== this.password.newPassRepeate) {
      this.notify.error('Пароли не совпадают!');
      return;
    }
    if (!this.password.newPass) {
      this.notify.error('Новый пароль не может быть пустым!');
      return;
    }

    try {
      await this.api.post({
        'url': 'auth/restore/change-password',
        'data': this.password
      })
    } catch (errorCode) {
      this.notify.error('Не верный пароль. Проверьте введённые данные!');
      return
    }

    this.password = {};
    this.notify.success('Пароль успешно изменён');
  }

  @action
  async onSavePersonalData() {
    if (!this.user.email || !this.user.name) {
      this.notify.error('Имя или эмейл не погут быть пустыми!');
      return;
    }

    try {
      await this.user.save();
    } catch (error) {
      this.notify.error('Ошибка при сохранении');
      return
    }

    this.notify.success('Личные данные успешно изменены');
  }
}
