import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class NewComponent extends Component {
  @tracked periodic = true;
  @tracked date = null;
  @tracked flatpickrDateRef = null;
  @tracked flatpickrTimeRef = null;

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
  }

  @action
  onPeriodic(){ this.periodic = true; }

  @action
  onSingle(){ this.periodic = false; }

}
