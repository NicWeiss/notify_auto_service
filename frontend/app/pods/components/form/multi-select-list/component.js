import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';
import { action, set } from '@ember/object';
import { inject as service } from '@ember/service';


export default class MultiSelectListComponent extends Component {
  @service store;

  @tracked model = null;
  @tracked modelName = null;
  @tracked isDisabled = false;
  @tracked label = null;
  @tracked value = null;
  @tracked show =false;
  @tracked localValue = null;

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
    this.updateLocalValue();
  }

  updateLocalValue(){
    this.localValue = this.value;
  }


  @action
  onSelect(record) {
    this.value.pushObject(record);
    this.onShow();
    this.updateLocalValue();
  }

  @action
  onDeselect(record) {
    this.value.removeObject(record)
    this.updateLocalValue();
  }

  @action
  onShow() {
    this.show = this.show ? false : true;
  }

}
