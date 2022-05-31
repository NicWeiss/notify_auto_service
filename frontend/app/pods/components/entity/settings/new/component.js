import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class CategoryNewComponent extends Component {
  @service store;
  @service errors;

  @tracked name = '';

  constructor(owner, args) {
    super(owner, args);
  }


  @action
  onCancel() {
    this.args.onCancel();
  }

  @action
  onSave() {
    let model = this.store.createRecord('category', { name: this.name });

    try {
      model.save()
    } catch (error) {
      this.errors.handle(error);
      this.notify.error('Ошибка при добавлении категории');
    }

    this.args.onComplete();
  }
}
