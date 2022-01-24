import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class SettingsViewComponent extends Component {
  @service notify;
  @service api;
  @service store;
  @service infinity;

  @tracked docunentHight = 0;
  @tracked style = `height: 100%;`
  @tracked maxlistHeight = 0;
  @tracked subComponentHeight = '';
  @tracked model = {};
  @tracked modelName = '';
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
  async onSelect(modelName = "user") {
    this.modelName = modelName;

    if (modelName === 'user') {
      try {
        await this.api.get({ 'url': 'user' })
      } catch (error) {
        console.log(error);
        this.notify.error('Ошибка на сервере');
        return;
      }
    } else {
      // this.model = this.infinity.model(modelName);
    }

    this.checkBurger();

    this.isInfinityReached = false;
    this.infinityWaiter = setInterval(this.waitReachInfinity.bind(this), 500);
  }

  @action
  reloadModel() {
    this.onSelect(this.modelName);
  }

  @action
  onPressBurger() {
    document.querySelector('.burger span').classList.toggle('active');
    document.querySelector('.menu-wrapper').classList.toggle('active');
  }

  @action
  onPressBack() {
    this.args.transitionToCategories()
  }

  checkBurger() {
    if (document.querySelector('.burger span')?.classList.contains('active')) {
      this.onPressBurger();
    }
  }

  async waitReachInfinity() {
    // let infinityModel = await this.model;
    // if (infinityModel.reachedInfinity || infinityModel.length === 0) {
    //   this.isInfinityReached = true;
    //   clearInterval(this.infinityWaiter);
    // }
  }

}
