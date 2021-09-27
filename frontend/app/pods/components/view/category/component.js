import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class CategoryViewComponent extends Component {
  @service store;
  @service notify;
  @service infinity;

  @tracked categoryId = 0;
  @tracked selectedSystem = null;
  @tracked systemHelp = null;
  @tracked docunentHight = 0;
  @tracked style = `height: 100%;`
  @tracked maxlistHeight = 0;
  @tracked subComponentHeight = '';
  @tracked notifyModel = {};
  @tracked isInfinityReached = false;

  constructor(owner, args) {
    super(owner, args);
    this.trackWindowChange();
    this.onSelect();
  }

  trackWindowChange() {
    setInterval(() => {
      if (this.docunentHight === document.documentElement.clientHeight) {
        return;
      }

      this.docunentHight = document.documentElement.clientHeight;

      this.style = `height: ${this.docunentHight}px;`;
      this.maxlistHeight = this.docunentHight - 260;
      this.subComponentHeight = `height: ${this.docunentHight - 60}px;`
    }, 100);
  }

  @action
  onSelect(categoryId = 0) {
    this.categoryId = categoryId;
    this.notifyModel = this.infinity.model('notify', { 'category_id': this.categoryId });
    this.checkBurger();
    this.isInfinityReached = false;
    this.infinityWaiter = setInterval(this.waitReachInfinity.bind(this), 500);
  }

  @action
  reloadNotifyList() {
    this.onSelect(this.categoryId);
  }

  @action
  onPressBurger() {
    document.querySelector('.burger span').classList.toggle('active');
    document.querySelector('.menu-wrapper').classList.toggle('active');
  }

  checkBurger() {
    if (document.querySelector('.burger span')?.classList.contains('active')) {
      this.onPressBurger();
    }
  }

  async waitReachInfinity() {
    let infinityModel = await this.notifyModel;
    if (infinityModel.reachedInfinity || infinityModel.length === 0) {
      this.isInfinityReached = true;
      clearInterval(this.infinityWaiter);
    }
  }

}
