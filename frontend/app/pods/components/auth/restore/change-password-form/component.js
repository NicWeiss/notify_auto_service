import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class RestoreComponent extends Component {
  @service notify;
  @service session;
  @service request;

  @tracked firstPassword = null;
  @tracked secondPassword = null;

  @tracked firstPasswordSelected = false;
  @tracked secondPasswordSelected = false;

  @tracked isCanChangePassword = true;

  constructor(owner, args) {
    super(owner, args);
    this.checkRestoreId();
  }

  async checkRestoreId() {
    try {
      await this.request.toApi('auth/restore/verify-restore-code', { 'code': this.args.restoreId })
    } catch (errorCode) {
      this.isCanChangePassword = false;
    }
  }

  @action
  onStartEdit(field) {
    this.deselect()
    if (field == 'password1') { this.firstPasswordSelected = true; }
    if (field == 'password2') { this.secondPasswordSelected = true; }
  }

  @action
  deselect() {
    this.firstPasswordSelected = false;
    this.secondPasswordSelected = false;
  }

  @action
  async onSubmit() {
    if (!this.firstPassword || !this.secondPassword) {
      this.notify.error('Не все данные заполнены ');
      return;
    }


    if (this.secondPassword !== this.firstPassword) {
      this.notify.error('Пароли не совпадают');
      return;
    }

    try {
      await this.request.toApi('auth/restore/change-password', {
        'password': this.firstPassword,
        'code': this.args.restoreId
      })
    } catch (errorCode) {
      if (errorCode === 422) {
        this.notify.error('Ссылка косстановления истекла. Запросите новую ссылку!');
      }
      if (errorCode === 500) this.notify.error('Ошибка на сервере');
      return
    }
    this.notify.success('Пароль успешно изменён');
    this.args.toLogin();
  }
}
