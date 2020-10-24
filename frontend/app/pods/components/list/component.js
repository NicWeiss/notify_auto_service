import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC } from 'frontend/constants';

export default class ListComponent extends Component {
  @service notify;

  @tracked selectedNotify = null;
  @tracked isShowingModal = false;
  @tracked itemForDelete = null;
  @tracked previewNotify = null;
  @tracked isDetail = null;

  PERIODIC = PERIODIC

  @action
  onSelectNotify(notify) {
    this.selectedNotify = notify;
    console.log(notify);
    console.log('set notify');
  }

  @action
  onChangeStatus(record) {
    record.status = record.status == 0 ? 1 : 0;
    record.save();
  }

  @action
  onDelete() {
    this.itemForDelete.destroyRecord();
    this.onClose();
  }

  @action
  onDeleteWindow(record) {
    this.isShowingModal = true;
    this.itemForDelete = record;
  }

  @action
  onClose() {
    this.isShowingModal = false;
  }

  @action
  onShowPreview(notify) {
    this.previewNotify = notify;
    this.isDetail = !this.isDetail;
  }

  @action
  onClosePreview() {
    this.isDetail = !this.isDetail;
  }

}
