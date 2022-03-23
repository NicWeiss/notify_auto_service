import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class CategoryEditComponent extends Component {
  @service store;

  @tracked name = '';

  constructor(owner, args) {
    super(owner, args);
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
      console.log(error);
      this.notify.error('Ошибка при обновлении категории');
    }

    this.args.onComplete();
  }
}
