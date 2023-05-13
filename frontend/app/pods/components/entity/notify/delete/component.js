import Component from '@glimmer/component';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { INTERVAL_SLUG, PERIODIC } from 'frontend/constants';

export default class NotifyDeleteComponent extends Component {
  @service notify;

  INTERVAL_SLUG = INTERVAL_SLUG
  PERIODIC = PERIODIC

  constructor(owner, args) {
    super(owner, args)
  }

  @action
  async onDelete() {
    await this.args.notify.deleteRecord();
    await this.args.notify.save();
    this.args.onClose();
    this.args.onComplete()
  }
}
