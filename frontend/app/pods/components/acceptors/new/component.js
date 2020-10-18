import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class AcceptorsNewComponent extends Component {
  @service store;

  @tracked selectedSystem = null;
  @tracked systemHelp = null;

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onSelectSystem(value) {
    this.args.model.system = value;
    let system = this.store.peekRecord('system', value)
    this.systemHelp = system.help;
  }

  @action
  onSaveAcceptor() {
    try {
      this.args.model.save()
    } catch (error) {
      console.log(error);
    }

    this.args.onComplete();
  }
}
