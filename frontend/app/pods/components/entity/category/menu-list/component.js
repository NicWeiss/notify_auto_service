import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class CategoriesListComponent extends Component {

  @tracked maxlistHeight = 0;
  @tracked selectedCategory = "0";
  @tracked isShowAddModal = false;

  constructor(owner, args) {
    super(owner, args);
  }

  get style() {
    return Ember.String.htmlSafe(`height: ${this.args.maxlistHeight}px;`);
  }

  @action
  onSelect(record = null) {
    document.querySelector(`.positionId_${this.selectedCategory}`)?.classList.remove('selected');
    this.selectedCategory = record?.id || "0";
    document.querySelector(`.positionId_${this.selectedCategory}`)?.classList.add('selected');

    this.args.onSelect(this.selectedCategory);
  }

  @action
  onAdd() {
    this.isShowAddModal = true;
  }

  @action
  onCancel() {
    this.isShowAddModal = false;
  }

  @action
  onComplete() {
    this.isShowAddModal = false;
    console.log('complete');
  }
}
