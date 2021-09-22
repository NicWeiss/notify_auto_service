import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class NotifySerializer extends RESTSerializer.extend(EmbeddedRecordsMixin) {
  attrs = {
    acceptorsList: { embedded: 'always' },
    dayOfWeek: 'day_of_week'
  };
};
