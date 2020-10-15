import RESTAdapter from '@ember-data/adapter/rest';

export default RESTAdapter.extend({
  // don't pluralize, just return type as-is
  pathForType: function (type) {
    return type;
  },
  namespace: 'api'
});

