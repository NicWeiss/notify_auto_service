import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class RestoreComponent extends Component {
  @service notify;
  @service session;
  @service request;

  @tracked emailSelected = false;

  @tracked email = null;

  @action
  onStartEdit(field) {
    this.deselect()
    if (field == 'email') { this.emailSelected = true; }
  }

  @action
  deselect() {
    this.emailSelected = false;
  }

  @action
  async onSubmit() {
    if (!this.email) {
      this.notify.error('Не все данные заполнены ');
      return;
    }

    try {
      await this.request.toApi('auth/restore', { 'email': this.email })
    } catch (errorCode) {
      if (errorCode === 403) {
        this.notify.error('Код уже выслан. Повторно запросить код можно будет через 5 минут');
      }
      if (errorCode === 422) this.notify.error('Пользователь с таким email не существует');
      if (errorCode === 500) this.notify.error('Ошибка на сервере');
      return
    }
    this.notify.success('Ссылка для восстановления выслана на почту');
  }
}
