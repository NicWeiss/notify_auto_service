import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class CategoriesListComponent extends Component {

  @tracked maxlistHeight = 0;
  @tracked selectedCategory = 0;

  constructor(owner, args) {
    super(owner, args);
  }

  get style() {
    return Ember.String.htmlSafe(`height: ${this.args.maxlistHeight}px;`);
  }

  @action
  onSelect(record = null) {
    document.querySelector(`.categoryId_${this.selectedCategory}`)?.classList.remove('selected');
    this.selectedCategory = record?.id || 0;
    document.querySelector(`.categoryId_${this.selectedCategory}`)?.classList.add('selected');

    this.args.onSelect(this.selectedCategory);
  }
}
