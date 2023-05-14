
import Picker from './picker';
import Component from '@ember/component';


const ISO_DATE_PATTERN = /^\d{4}-(([0-1][0-9])|([1][0-2]))-(([0-2][0-9])|([3][0-1]))T(([0-1][0-9])|([2][0-4])):[0-5][0-9]((:[0-5][0-9]$)|($))/;


export default Component.extend({
  classNames: ['date-and-time-picker'],

  inputId: null,
  date: null,
  pickerType: 'dateAndTime',
  dateInTemplate: new Date(),
  classes: "",
  oninput: () => { },

  init() {
    this._super(...arguments);
    this.set('inputId', this.uuidv4());
    if (!this.date) {
      this.date = new Date();
    }
    this.picker = new Picker(this.date.toISOString(), this.pickerType, this.inputId, this.setInput.bind(this));
  },

  didReceiveAttrs() {
    this._super(...arguments);
    this.setInput(this.date.toISOString(), false);
  },

  setInput(newDate, isUpdate = true) {
    if (!newDate?.match(this.ISO_DATE_PATTERN)) {
      this.picker.updateDate(newDate);
      this.picker.sendCallback();

      return;
    }

    const date = new Date(newDate);
    const ruDateFormat = `${`0${date.getDate()}`.slice(-2)}.${`0${date.getMonth() + 1}`.slice(-2)}.${date.getFullYear()}`;

    if (isUpdate) {
      setTimeout(() => { this.oninput(newDate); }, 100);
    }

    if (this.pickerType === 'time') {
      this.set('dateInTemplate', `${date.toLocaleTimeString().slice(0, 5)}`);
    } else {
      this.set('dateInTemplate', `${ruDateFormat}, ${date.toLocaleTimeString().slice(0, 5)}`);
    }

    this.onChange(date);
  },

  uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      let r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  },


  actions: {
    show() {
      this.picker.show();
    },
  }
});
