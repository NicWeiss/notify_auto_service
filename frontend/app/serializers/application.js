import RESTSerializer from '@ember-data/serializer/rest';

export default RESTSerializer.extend({
  keyForAttribute: function (key) {
    return Ember.String.camelize(key);
  },

  keyForRelationship: function (key) {
    return Ember.String.camelize(key);
  },
});