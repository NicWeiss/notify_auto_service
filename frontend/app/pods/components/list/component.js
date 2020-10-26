import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC, WEEK } from 'frontend/constants';

export default class ListComponent extends Component {
  @service notify;

  @tracked selectedNotify = null;
  @tracked isShowingModal = false;
  @tracked itemForDelete = null;
  @tracked previewNotify = null;
  @tracked isDetail = null;

  PERIODIC = PERIODIC
  WEEK = WEEK

  @action
  onSelectNotify(notify) {
    this.selectedNotify = notify;
    console.log(notify);
  }

  @action
  onChangeStatus(record) {
    this.isDetail = false;
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
    this.isDetail = false;
    this.itemForDelete = record;
    this.isShowingModal = true;
  }

  @action
  onClose() {
    this.isShowingModal = false;
  }

  @action
  onShowPreview(notify) {
    this.previewNotify = notify;
    this.isDetail = true;
  }

  @action
  onClosePreview() {
    this.isDetail = false;
  }

}
