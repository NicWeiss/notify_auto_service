import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';
import { action, set } from '@ember/object';
import { inject as service } from '@ember/service';


export default class SelectListComponent extends Component {
  @service store;

  @tracked model = null;
  @tracked modelName = null;
  @tracked isDisabled = false;
  @tracked label = null;
  @tracked value = null;


  init() {
    super.init(...arguments);
    if (!this.isDisabled) {
      this.loadData()
    }
  }

  async loadData() {
    try {
      this.model = await this.store.findAll(this.modelName);
    } catch (error) {
      console.log(error);
    }
  }

}
