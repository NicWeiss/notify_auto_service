import axios from 'axios';
import Service from '@ember/service';

export default Service.extend({

  get(request) {
    request.method = 'get';
    return this.doRequest(request);
  },

  put(request) {
    request.method = 'put';
    return this.doRequest(request);
  },

  post(request) {
    request.method = 'post';
    return this.doRequest(request);
  },

  delete(request) {
    request.method = 'delete';
    return this.doRequest(request);
  },

  doRequest(request) {
    const method = request.method || 'post';
    const url = request.url || '';
    const data = this.clearData(request.data);
    const queryParams = this.buildQueryParams(request.queryParams);

    let session = "";
    let obj = JSON.parse(localStorage.getItem('ember_simple_auth-session')).authenticated;

    for (const [key, value] of Object.entries(obj)) {
      if (isNaN(key)) {
        continue;
      }
      session += value;
    }

    const config = {
      method,
      url: `/api/${url}${queryParams}`,
      headers: {
        'Content-Type': 'application/json; charset=UTF-8',
        'Session': session,
      },
      data,
      json: true,
    };

    return new Promise((resolve, reject) => {
      axios(config)
        .then(async (response) => {
          resolve(response.data);
        })
        .catch((error) => {
          reject(error.response);
        });
    });
  },

  buildQueryParams(queryParams) {
    if (!queryParams) {
      return '';
    }

    const str = [];

    for (const [key, value] of Object.entries(queryParams)) {
      if (value !== '' && value !== undefined && value !== null) {
        str.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
      }
    }

    return `?${str.join('&')}`;
  },

  clearData(data) {
    if (!data) {
      return null;
    }

    const clearedData = {};

    for (const [key, value] of Object.entries(data)) {
      if (value !== '' && value !== undefined && value !== null) {
        clearedData[key] = value;
      }
    }

    return clearedData;
  }
})
