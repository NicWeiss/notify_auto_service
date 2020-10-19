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
  }

  updateLocalValue(){
    this.localValue = this.value.get(this.modelName);
  }


  @action
  onSelect(record) {
    this.value.get(this.modelName).pushObject(record);
    this.onShow();
    this.updateLocalValue();    
  }

  @action
  onDeselect(record) {
    this.value.get(this.modelName).removeObject(record)
    this.updateLocalValue();    
  }

  @action
  onShow() {
    this.show = this.show ? false : true;
  }

}
