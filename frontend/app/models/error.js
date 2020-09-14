import Model, { attr } from '@ember-data/model'; 

export default class ErrorModel extends Model {
  @attr message
}
