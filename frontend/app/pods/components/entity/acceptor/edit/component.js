import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class AcceptorsEditComponent extends Component {
  @service store;
  @service notify;

  @tracked selectedSystem = null;
  @tracked systemHelp = null;
  @tracked isNew = true;

  constructor(owner, args) {
    super(owner, args);
    this.isNew = Boolean(this.args.model.id)
    // if (this.args.model.id) {
    //   this.setSelectedSystem();
    // }
  }

  async setSelectedSystem() {
    this.selectedSystem = await this.store.findRecord('system', this.args.model.systemId);
    this.onSelectSystem(this.selectedSystem);
  }

  @action
  onSelectSystem(system) {
    this.args.model.systemId = system.id;
    this.args.model.type = system.type;
    this.systemHelp = system.help;
  }

  @action
  onCancel() {
    if (!this.args.model.id) {
      this.args.model.destroyRecord();
    }
    this.args.onCancel();
  }

  @action
  async onSaveAcceptor() {
    if (!this.args.model.name || !this.args.model.account || !this.args.model.systemId) {
      this.notify.error('Остались пустые поля!');
      return;
    }

    try {
      await this.args.model.save()
    } catch (error) {
      console.log(error);
      this.notify.error('Ошибка при сохранении получателя');
    }

    this.args.onComplete();
  }
}
