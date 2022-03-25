import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class CategoryDeleteComponent extends Component {
  @service api;
  @service store;

  @tracked name = '';
  @tracked deleteWithNotify = false;
  @tracked notifyList = null;

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onCheck() {
    if (!this.deleteWithNotify) {
      try {
        this.notifyList = this.store.query('notify', {
          category_id: this.args.model.id
        });
      } catch (error) {
        console.log(error);
        this.notify.error('Ошибка при получении уведомлений');
      }
    }
  }

  @action
  onCancel() {
    this.args.onCancel();
  }

  @action
  async onDelete() {
    try {
      if (this.deleteWithNotify) {
        await this.api.delete({ 'url': 'notifies/delete_by_category_id', 'data': { 'category_id': this.args.model.id } });
      } else {
        await this.api.put({ 'url': 'notifies/reset_from_category_id', 'data': { 'category_id': this.args.model.id } })
      }

      await this.args.model.destroyRecord()
    } catch (error) {
      console.log(error);
      this.notify.error('Ошибка при удалении категории');
    }

    this.args.onComplete();
  }
}
