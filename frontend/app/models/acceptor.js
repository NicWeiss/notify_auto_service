import Model, { attr } from '@ember-data/model'; 

export default class AcceptorAddModel extends Model {
  @attr('string') name
  @attr('string') system_id
  @attr('string') account
  @attr('string') status
  @attr('string') type
}
