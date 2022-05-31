import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class CategoryEditComponent extends Component {
  @service store;
  @service errors;

  @tracked name = '';

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onKeyPress(event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      this.onSave()
    }
  }

  @action
  onCancel() {
    this.args.onCancel();
  }

  @action
  async onSave() {
    try {
      await this.args.model.save()
    } catch (error) {
      this.errors.handle(error);
      this.notify.error('Ошибка при обновлении категории');
    }

    this.args.onComplete();
  }
}
