import Model, { attr, hasMany } from '@ember-data/model';

export default class NotifyModel extends Model {
  @attr('string') name
  @attr('string') text
  @attr('string') periodic
  @attr('string') dayOfWeek
  @attr('string') date
  @attr('string') time
  @attr('string') status

  @hasMany('acceptor') acceptorsList
}
