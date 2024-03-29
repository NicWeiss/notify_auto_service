import Model, { attr, hasMany } from '@ember-data/model';

export default class NotifyModel extends Model {
  @attr('string') name
  @attr('string') text
  @attr('string') periodic
  @attr('number') dayOfWeek
  @attr('string') date
  @attr('string') time
  @attr('boolean') isDisabled
  @attr('number') categoryId
  @attr('number') repeateInterval
  @attr('boolean') isAutodisable
  @attr('string') autodisableAt

  @hasMany('acceptor') acceptors
}
