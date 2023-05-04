import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';


export default class UserComponent extends Component {
  @service api;
  @service errors;
  @service notify;
  @service store;

  @tracked user = null;
  @tracked sessions = [];
  @tracked password = {};
  @tracked isShowDeleteModal = false;

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
      this.errors.handle(error);
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
      this.errors.handle(error);
    }
  }


  @action
  async changePassword() {
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
        'url': 'user/change-password',
        'data': {
          'current_password': this.password.currentPass,
          'new_password': this.password.newPass
        }
      })
    } catch (error) {
      this.errors.handle(error);
      this.notify.error('Неверный пароль. Проверьте введённые данные!');
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
      this.errors.handle(error);
      this.notify.error('Ошибка при сохранении');
      return
    }

    this.notify.success('Личные данные успешно изменены');
  }

  @action
  deleteAccount() {
    this.isShowDeleteModal = true;
  }

  @action
  onCancel() {
    this.isShowDeleteModal = false;
  }

  @action
  async onCompleteDeleAccount(password) {
    this.onCancel();

    try {
      await this.api.delete({
        'url': 'user/delete',
        'data': { 'password': password }
      })
    } catch (error) {
      this.errors.handle(error);
      this.notify.error('Неверный пароль. Проверьте введённые данные!');
      return
    }

    this.notify.success('Аккаунт удаляется!');
    document.location.reload();
  }
}
