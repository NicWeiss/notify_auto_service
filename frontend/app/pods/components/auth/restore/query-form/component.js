import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class RestoreComponent extends Component {
  @service notify;
  @service session;
  @service api;

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
      await this.api.post({ 'url': 'auth/restore', 'data': { 'email': this.email } })
    } catch (error) {
      if (error.data && error.data.detail){
        this.notify.error(error.data.detail);
       } else {
        this.notify.error('Server error');
       }

      return;
    }

    this.notify.success('Ссылка для восстановления выслана на почту');
  }
}
