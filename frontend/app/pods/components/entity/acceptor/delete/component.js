import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class AcceptorDeleteComponent extends Component {
  @service api;
  @service store;
  @service errors;

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onCancel() {
    this.args.onCancel();
  }

  @action
  async onDelete() {
    try {
      await this.args.model.destroyRecord()
    } catch (error) {
      this.errors.handle(error);
      this.notify.error('Ошибка при удалении получателя');
    }

    this.args.onComplete();
  }
}
