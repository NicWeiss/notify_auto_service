import requests

from app.core.config import settings
from app.services import ServiceResponse


class LocationService:
    def get_location(self, ip: str) -> ServiceResponse:
        api_key = settings.IP_LOCATION_PROVIDER_TOKEN
        location_api_response = {'ip': ip}

        if api_key:
            url = f'https://api.ipdata.co/{ip}?api-key={api_key}&fields=ip,city,region,country_name,continent_name'

            try:
                response = requests.get(url=url)
                location_api_response = response.json()
            except Exception:
                pass
                # return ServiceResponse(is_error=True, description='Can\'t get location')

        return ServiceResponse(data=location_api_response)
