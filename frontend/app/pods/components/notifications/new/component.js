import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class NewComponent extends Component {
  @service store;

  @tracked periodic = true;
  @tracked notifyNew = null;

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
    this.notifyNew = this.store.createRecord('notify');
    this.notifyNew.status = '1';
  }

  @action
  onPeriodic(){ this.periodic = true; }

  @action
  onSingle(){ this.periodic = false; }

  @action
  cancel(){
    this.notifyNew.destroyRecord();
    this.args.onCancel();
  }

}
