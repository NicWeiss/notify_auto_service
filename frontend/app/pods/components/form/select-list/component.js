import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';
import { action, set } from '@ember/object';
import { inject as service } from '@ember/service';


export default class SelectListComponent extends Component {
  @service store;
  @service errors;

  @tracked model = null;
  @tracked modelName = null;
  @tracked isDisabled = false;
  @tracked selected = null;
  @tracked label = null;
  @tracked value = null;


  init() {
    super.init(...arguments);
    if (!this.isDisabled) {
      this.loadData()
    }
  }

  @action
  select(id) {
    let record = this.store.peekRecord(this.modelName, id);
    this.onSelect(record);
  }

  async loadData() {
    try {
      this.model = await this.store.findAll(this.modelName);
    } catch (error) {
      this.errors.handle(error);
    }
  }

}
