import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class SettingsListComponent extends Component {

  @tracked maxlistHeight = 0;
  @tracked selectedPosition = "0";
  @tracked isShowAddModal = false;

  constructor(owner, args) {
    super(owner, args);
  }

  get style() {
    return Ember.String.htmlSafe(`height: ${this.args.maxlistHeight}px;`);
  }

  @action
  onSelect(position) {
    document.querySelector(`.positionId_${this.selectedPosition}`)?.classList.remove('selected');
    this.selectedPosition = position;
    document.querySelector(`.positionId_${this.selectedPosition}`)?.classList.add('selected');

    this.args.onSelect(position);
  }
}
