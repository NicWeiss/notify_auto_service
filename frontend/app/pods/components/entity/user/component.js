import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';


export default class UserComponent extends Component {
  @service api;
  @service notify;
  @service store;

  @tracked user = null;

  constructor(owner, args) {
    super(owner, args);

    this.getUser();
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


  @action
  onSave() {
  }
}
