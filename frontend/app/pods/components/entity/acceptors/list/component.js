import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';



export default class AcceptorsListComponent extends Component {

  @tracked isShowingModal = false;
  @tracked itemForDelete = null;


  @action
  onChangeStatus(record){
    record.status = record.status == 0 ? 1 : 0;
    record.save();
  }

  @action
  onDelete(){
    this.itemForDelete.destroyRecord();
    this.onClose();
  }

  @action
  onDeleteWindow(record){
    this.isShowingModal = true;
    this.itemForDelete = record;
  }

  @action
  onClose(){
    this.isShowingModal = false;
  }
}
