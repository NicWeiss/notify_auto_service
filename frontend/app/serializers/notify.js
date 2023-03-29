import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class NotifySerializer extends RESTSerializer.extend(EmbeddedRecordsMixin) {
  attrs = {
    acceptors: { embedded: 'always' },
    dayOfWeek: 'day_of_week',
    categoryId: 'category_id',
    isDisabled: 'is_disabled'
  };
};
